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
docker compose up -d                 # start
docker compose stop                  # stop (keeps data)
docker compose logs -f wordpress     # tail logs
./tools/wp <command>                 # WP-CLI, e.g. ./tools/wp post list
```

`docker compose down -v` would delete the database and WP core volumes. Use
`docker compose down` without `-v` unless you mean it.

- Site: http://localhost:8080
- Admin: http://localhost:8080/wp-admin

Credentials live in `.env`, which is gitignored and never committed.

## Theme

`wp-content/themes/techscope` — block theme, `theme.json` schema version 3.

### Theme layout

```
wp-content/themes/techscope/
├── theme.json        design system — palette, type scale, spacing, block styles
├── style.css         what theme.json cannot express (grids, rails, reading column)
├── functions.php     fonts, image sizes, reading-time block binding, search scope
├── templates/        home, single, archive, search, page, page-wide, index, 404
├── parts/            header, footer, article-meta
└── patterns/         newsletter band, no-results
```

`wp-content/plugins/techscope-core` carries the Open Graph, Twitter card and
JSON-LD output. It lives in a plugin so the markup survives a theme change.

## Demo content

```bash
./tools/wp eval-file wp-content/techscope-tools/seed-content.php   # 17 articles
./tools/wp eval-file wp-content/techscope-tools/seed-pages.php     # About, Contact
```

Both scripts are idempotent — they match on slug and update rather than insert,
so re-running after editing an article is safe.

Cover art is generated with PHP GD inside the container, because host PHP has
no `gd`:

```bash
docker compose exec wordpress php \
  /var/www/html/wp-content/techscope-tools/make-covers.php \
  /var/www/html/wp-content/uploads/_seed-covers \
  "my-article-slug:#FF4A1C:waves"
```

Motifs: `grid`, `arcs`, `bars`, `scatter`, `waves`, `blocks`. Write output into
the uploads volume rather than the bind mount — the container runs as root, and
files it writes into the bind mount land root-owned on the host.

## Desk categories

| Desk | Slug | Term ID |
|---|---|---|
| AI | `ai` | 2 |
| Hardware | `hardware` | 3 |
| Software | `software` | 4 |
| Security | `security` | 5 |
| Science | `science` | 6 |
| Policy | `policy` | 7 |

The homepage's per-desk sections filter by term ID, because that is what the
query block takes. Changing a category means updating `templates/home.html`.
