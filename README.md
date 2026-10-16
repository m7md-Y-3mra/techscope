# TechScope

A WordPress technology magazine, built as a custom block theme.

## Local environment

Isolated Docker Compose stack. Does not touch host Apache, host MariaDB, or any
other Docker project.

| Service | Image | Notes |
|---|---|---|
| `wordpress` | `wordpress:7.1.3-php8.3-apache` | published on `127.0.0.1:8080` only |
| `db` | `mariadb:11.8.9` | **not** published to the host |
| `cli` | `wordpress:cli-2.12.0-php8.3` | on-demand, `profiles: [tools]` |

WP core and uploads live in the `wp_core` named volume. Only our own code is
bind-mounted, so theme files stay owned by the host user and remain editable
without `sudo`.

### Setup

```bash
cp .env.example .env     # then fill in real values
docker compose up -d
docker compose run --rm cli wp core install --url=http://localhost:8080 ...
```

### Everyday commands

```bash
docker compose up -d                        # start
docker compose stop                         # stop (keeps data)
docker compose logs -f wordpress            # tail logs
docker compose run --rm cli wp <command>    # WP-CLI
```

- Site: http://localhost:8080
- Admin: http://localhost:8080/wp-admin

Credentials live in `.env`, which is gitignored and never committed.

## Theme

`wp-content/themes/techscope` — block theme, `theme.json` schema version 3.
