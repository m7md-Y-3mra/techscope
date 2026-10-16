#!/usr/bin/env bash
#
# Brings the production stack up, or rolls out changes to an existing one.
# Run on the server, from the repo root.
#
#   ./deploy/deploy.sh
#
# Idempotent. On an empty database it also runs the WordPress installation and
# seeds the demo content.

set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE="docker compose -f docker-compose.prod.yml"

log()  { printf '\n\033[1m==> %s\033[0m\n' "$*"; }
fail() { printf '\033[31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }

[[ -f .env ]] || fail "no .env — copy .env.prod.example to .env and fill it in"

set -a; . ./.env; set +a

for var in MARIADB_PASSWORD MARIADB_ROOT_PASSWORD WP_SITE_URL TECHSCOPE_DOMAIN TECHSCOPE_TLS_EMAIL; do
	[[ -n "${!var:-}" ]] || fail ".env is missing $var"
done

[[ "$WP_SITE_URL" == "https://$TECHSCOPE_DOMAIN" ]] \
	|| fail "WP_SITE_URL ($WP_SITE_URL) must be exactly https://$TECHSCOPE_DOMAIN"

# The dev and prod compose files pin images separately; a silent drift between
# them means production runs something that was never tested locally.
log "Checking image pins match docker-compose.yml"
for image in $(grep -oE 'image: [a-z0-9./:-]+' docker-compose.prod.yml | awk '{print $2}' | grep -v '^caddy'); do
	grep -q "image: $image" docker-compose.yml \
		|| fail "$image is pinned in docker-compose.prod.yml but not in docker-compose.yml"
done
echo "pins agree"

log "Checking DNS before Caddy asks Let's Encrypt for a certificate"
resolved="$(getent hosts "$TECHSCOPE_DOMAIN" | awk '{print $1}' | head -1 || true)"
public_ip="$(curl -fsS --max-time 5 https://api.ipify.org || true)"
if [[ -z "$resolved" ]]; then
	fail "$TECHSCOPE_DOMAIN does not resolve. Add the A record first — a failed
       certificate order counts against the Let's Encrypt rate limit."
elif [[ -n "$public_ip" && "$resolved" != "$public_ip" ]]; then
	echo "WARNING: $TECHSCOPE_DOMAIN resolves to $resolved, this host is $public_ip."
	echo "         Continuing, but Let's Encrypt will fail unless a proxy sits in front."
else
	echo "$TECHSCOPE_DOMAIN -> $resolved"
fi

log "Pulling pinned images"
$COMPOSE pull --quiet

log "Starting stack"
$COMPOSE up -d --remove-orphans

log "Waiting for WordPress to answer"
for _ in $(seq 1 30); do
	if $COMPOSE exec -T wordpress php -r 'exit(0);' >/dev/null 2>&1; then break; fi
	sleep 2
done

wp() { $COMPOSE run --rm -T cli wp "$@"; }

if wp core is-installed >/dev/null 2>&1; then
	log "WordPress already installed — applying configuration only"
else
	log "Fresh database: installing WordPress"
	: "${WP_ADMIN_USER:?set WP_ADMIN_USER in .env for the first install}"
	: "${WP_ADMIN_PASSWORD:?set WP_ADMIN_PASSWORD in .env for the first install}"
	: "${WP_ADMIN_EMAIL:?set WP_ADMIN_EMAIL in .env for the first install}"

	wp core install \
		--url="$WP_SITE_URL" \
		--title="TechScope" \
		--admin_user="$WP_ADMIN_USER" \
		--admin_password="$WP_ADMIN_PASSWORD" \
		--admin_email="$WP_ADMIN_EMAIL" \
		--skip-email

	log "Seeding demo content"
	wp eval-file wp-content/techscope-tools/seed-content.php || true
	wp eval-file wp-content/techscope-tools/seed-pages.php || true
fi

log "Activating theme and plugin"
wp theme activate techscope
wp plugin activate techscope-core
wp rewrite structure '/%postname%/'
wp rewrite flush --hard
wp option update blogdescription 'Independent technology journalism'

# The site URL changes between local and production; stored absolute URLs in
# post content and options do not follow automatically.
log "Normalising stored URLs to $WP_SITE_URL"
wp option update home "$WP_SITE_URL"
wp option update siteurl "$WP_SITE_URL"
wp cache flush || true

log "Verifying"
code="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 20 "$WP_SITE_URL/" || echo 000)"
echo "GET $WP_SITE_URL/ -> $code"
[[ "$code" == "200" ]] || echo "WARNING: expected 200. Check: $COMPOSE logs caddy --tail 40"

$COMPOSE ps

cat <<NEXT

Site:  $WP_SITE_URL
Admin: $WP_SITE_URL/wp-admin

NEXT
