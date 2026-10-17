# Deploying TechScope

Target: an **Oracle Cloud Always Free** VM running the same Docker Compose
stack as development, with [Caddy](https://caddyserver.com/) in front for
automatic HTTPS.

Nothing here costs money. The Always Free tier has no trial clock on it — the
resources below stay free indefinitely.

## Why this and not the alternatives

| Option | Verdict |
|---|---|
| **Oracle Always Free VM** | **Chosen.** Runs the real stack. Admin, comments and native search all work. |
| WordPress.com free | Custom themes require the Business plan. Cannot host this. |
| Render / Koyeb / Railway free tiers | No persistent volume on the free plans, so uploads vanish on redeploy. No free MySQL. |
| Fly.io | The free allowance ended; it is now pay-as-you-go. |
| InfinityFree and similar | Works, but no SSH, no WP-CLI, no Docker, and hard request caps. Deploying means FTP. |
| Static export to Cloudflare Pages | Genuinely good and genuinely free, but you lose live `/wp-admin`, comments, and server-side search. A different project shape. |

## What you need first

- An Oracle Cloud account. Signup asks for a card for identity verification and
  does not charge it while you stay on Always Free resources.
- A domain name pointed at the VM. If you do not have one, a free
  [DuckDNS](https://www.duckdns.org/) subdomain works with Let's Encrypt.
- An SSH key pair.

## 1. Create the instance

In the Oracle Console: **Compute → Instances → Create instance**.

- **Image:** Ubuntu 24.04 (or Oracle Linux 9 — the bootstrap script handles both).
- **Shape:** `VM.Standard.A1.Flex`, Ampere ARM. Always Free covers **4 OCPUs and
  24 GB** across your A1 instances. Give this one all four.
- **Boot volume:** 50 GB is plenty. Always Free allows 200 GB in total.
- **SSH key:** paste your public key.
- Leave the VCN defaults — the subnet it creates is fine.

> **If you get "Out of host capacity":** this is the normal Always Free
> experience for A1 shapes, not an error on your side. Either try a different
> availability domain in the same region, or create the instance as
> `VM.Standard.E2.1.Micro` (AMD, 1 OCPU / 1 GB) which is also Always Free and
> almost always available. The micro shape runs this site fine — the bootstrap
> script adds swap for exactly that case.

## 2. Open the ports — **both** layers

This is the step that wastes everyone's afternoon. Oracle blocks web traffic in
two independent places, and opening one does nothing on its own.

**Layer 1 — the VCN security list** (in the Console):
Networking → Virtual Cloud Networks → your VCN → the public subnet → its
security list → **Add Ingress Rules**:

| Source | Protocol | Destination port |
|---|---|---|
| `0.0.0.0/0` | TCP | 80 |
| `0.0.0.0/0` | TCP | 443 |

**Layer 2 — the host firewall.** Oracle's stock images ship a `REJECT` rule in
the `INPUT` chain. `deploy/bootstrap-server.sh` handles this for you (firewalld
on Oracle Linux, iptables + `netfilter-persistent` on Ubuntu).

## 3. Point DNS at the VM

Create an `A` record for your domain pointing at the instance's public IP, and
**wait until it resolves** before starting the stack:

```bash
getent hosts techscope.example.com
```

Caddy proves domain ownership over HTTP. Starting it against a domain that does
not resolve yet produces a failed certificate order, and Let's Encrypt
rate-limits repeated failures.

## 4. Bootstrap the server

SSH in, then:

```bash
curl -fsSL https://raw.githubusercontent.com/m7md-Y-3mra/techscope/main/deploy/bootstrap-server.sh | bash
```

It installs Docker and the Compose plugin, opens the host firewall, adds 2 GB of
swap, and enables unattended security updates. **Log out and back in afterwards**
so your shell picks up the `docker` group.

## 5. Deploy

```bash
git clone https://github.com/m7md-Y-3mra/techscope.git
cd techscope
cp .env.prod.example .env
```

Fill in `.env`. Generate the two database passwords rather than inventing them:

```bash
openssl rand -hex 24
```

`WP_SITE_URL` must be exactly `https://` + `TECHSCOPE_DOMAIN` — the deploy
script refuses to continue otherwise, because a mismatch here produces a site
that half-redirects and is painful to unpick later.

For the first run only, also set `WP_ADMIN_USER`, `WP_ADMIN_PASSWORD` and
`WP_ADMIN_EMAIL`, which the installer needs.

Then:

```bash
./deploy/deploy.sh
```

The script checks that the dev and prod image pins still agree, verifies DNS,
pulls the pinned images, starts the stack, installs WordPress if the database is
empty, seeds the demo content, activates the theme and plugin, flushes rewrite
rules, and finishes with an HTTP check against the live URL.

Certificate issuance takes a few seconds on first boot. If the check reports
anything but `200`:

```bash
docker compose -f docker-compose.prod.yml logs caddy --tail 40
```

## 6. Bringing your local content across (optional)

A fresh server seeds itself from the committed scripts, so this is only needed
if you want the exact database you have been editing locally.

```bash
./deploy/migrate-from-local.sh ubuntu@203.0.113.10 https://techscope.example.com
```

Run it from your **local** machine. It exports the database and uploads, copies
both over, imports them, and runs `wp search-replace` to rewrite
`http://localhost:8080` to the live URL. `search-replace` is used rather than
`sed` over the dump because WordPress stores serialised PHP arrays whose string
lengths break under naive substitution.

## Running it

```bash
# logs
docker compose -f docker-compose.prod.yml logs -f wordpress

# WP-CLI against production
docker compose -f docker-compose.prod.yml run --rm cli wp post list

# roll out theme or plugin changes
git pull && ./deploy/deploy.sh

# nightly backup (database + uploads, keeps 14 sets)
./deploy/backup.sh
```

Add the backup to cron:

```bash
0 3 * * * cd ~/techscope && ./deploy/backup.sh >> /var/log/techscope-backup.log 2>&1
```

`backups/` is gitignored. Copy the archives off the VM periodically — a backup
that only exists on the machine it backs up is not a backup.

## What production changes versus development

| | Development | Production |
|---|---|---|
| Exposure | `127.0.0.1:8080` | Caddy on `:80` / `:443`, WordPress not published |
| TLS | none | Let's Encrypt, auto-renewed by Caddy |
| `WP_DEBUG` | on, logging to file | off |
| File modification | allowed | `DISALLOW_FILE_MODS` — no admin-side plugin or theme installs |
| Theme/plugin mounts | read-write | read-only |
| `xmlrpc.php` | reachable | returns 403 |
| Core updates | manual | minor versions automatic |

Deploying code is therefore `git pull` plus a re-run of the deploy script, not
an upload through the admin screen.

## Things that will bite you later

- **Oracle reclaims idle Always Free compute** on accounts that have never
  upgraded. Upgrading to Pay As You Go exempts you and still bills nothing while
  you stay inside the Always Free limits — the resources remain free, the
  account is just no longer flagged idle-reclaimable.
- **The A1 shape may be unavailable when you need to rebuild.** Keep the backups
  current so you can restore onto whatever shape you can actually get.
- **Caddy stores certificates in the `caddy_data` volume.** Do not delete that
  volume casually; re-issuing repeatedly runs into Let's Encrypt rate limits.
- **`docker compose down -v` destroys the database.** Use `down` without `-v`.

## alwaysdata (free tier, no credit card)

Live: https://techscope.alwaysdata.net

Chosen over Oracle Always Free because Oracle's signup declines many cards.
alwaysdata's free pack (1 GB disk, 256 MB RAM, 0.25 CPU) needs no card and,
unlike most free PHP hosts, gives SSH — so WP-CLI and the seed tooling work.

No Docker there; WordPress runs native on their PHP 8.4. The compose/Caddy
stack in this repo stays the path for a VPS.

### Layout

| Thing | Value |
|---|---|
| SSH | `techscope@ssh-techscope.alwaysdata.net` (key auth) |
| Docroot | `~/www` |
| DB | `techscope_wp` on `mysql-techscope.alwaysdata.net`, user `techscope` |
| WP-CLI | `~/bin/wp` |

### Deploying

The DB password lives in `~/.dbpass` on the server (mode 600), never in argv
or in the repo. Put it there from a local shell:

    printf '%s' 'THE_PASSWORD' | ssh techscope@ssh-techscope.alwaysdata.net 'umask 077; cat > ~/.dbpass'

Then push code and run the installer:

    tar -czf - -C wp-content/themes techscope | ssh techscope@ssh-techscope.alwaysdata.net 'tar -xzf - -C ~/www/wp-content/themes'
    scp deploy/alwaysdata-install.sh techscope@ssh-techscope.alwaysdata.net:~/install.sh
    ssh techscope@ssh-techscope.alwaysdata.net 'bash ~/install.sh'

The script is re-runnable: it only writes `wp-config.php` and imports the dump
when they are absent, so a second run just re-syncs URLs and re-activates.

### Gotchas hit during the first deploy

- `wp db import` needs a `mariadb` client binary alwaysdata does not ship.
  The installer pipes into `mysql` instead.
- `wp rewrite flush` cannot write `.htaccess` here. It is committed by hand;
  without it every permalink 404s while the homepage still returns 200.
- TLS terminates upstream, so the HTTPS redirect tests `X-Forwarded-Proto`
  rather than `%{HTTPS}`.
- `--skip-content` leaves no fallback theme. `twentytwentyfive` is installed
  but not activated, purely so a fatal in `techscope` cannot lock you out
  of `/wp-admin`.
