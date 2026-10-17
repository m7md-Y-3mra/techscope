#!/usr/bin/env bash
# Runs ON the alwaysdata server. Builds wp-config, imports the local dump,
# rewrites URLs and activates the theme. Idempotent enough to re-run.
set -euo pipefail

SITE_URL="https://techscope.alwaysdata.net"
OLD_URL="http://localhost:8080"
DB_NAME="techscope_wp"
DB_USER="techscope"
DB_HOST="mysql-techscope.alwaysdata.net"
WWW="$HOME/www"
WP="$HOME/bin/wp"

[ -s "$HOME/.dbpass" ] || { echo "missing ~/.dbpass"; exit 1; }
DB_PASS="$(cat "$HOME/.dbpass")"

cd "$WWW"

# wp-config: credentials via stdin so the password never lands in argv.
if [ ! -f wp-config.php ]; then
  "$WP" config create \
    --dbname="$DB_NAME" --dbuser="$DB_USER" --dbhost="$DB_HOST" \
    --prompt=dbpass <<< "$DB_PASS"
  "$WP" config set WP_HOME        "$SITE_URL"
  "$WP" config set WP_SITEURL     "$SITE_URL"
  "$WP" config set WP_DEBUG         false --raw
  "$WP" config set WP_DEBUG_DISPLAY false --raw
  "$WP" config set DISALLOW_FILE_EDIT true --raw
  "$WP" config set WP_MEMORY_LIMIT  '256M'
  "$WP" config set FS_METHOD        'direct'
  chmod 600 wp-config.php
fi

# Import only into an empty database, so a re-run never clobbers live content.
if ! "$WP" core is-installed 2>/dev/null; then
  # wp db import needs a `mariadb` client binary that alwaysdata does not ship;
  # pipe straight into mysql instead, password via env so it stays out of argv.
  gunzip -c "$HOME/techscope-db.sql.gz" \
    | MYSQL_PWD="$DB_PASS" mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME"
fi

# Uploads. _seed-covers is seed source, not site content.
if [ ! -d wp-content/uploads/2026 ] && [ ! -d wp-content/uploads/2025 ]; then
  tar -xzf "$HOME/techscope-uploads.tar.gz" -C wp-content
fi
rm -rf wp-content/uploads/_seed-covers

# Serialised PHP in post_content and options needs --precise, not sed.
"$WP" search-replace "$OLD_URL" "$SITE_URL" --all-tables --precise --report-changed-only

"$WP" theme install twentytwentyfive >/dev/null 2>&1 || true   # lockout parachute, not activated
"$WP" theme activate techscope
"$WP" plugin activate techscope-core
"$WP" rewrite structure '/%postname%/' --hard
"$WP" rewrite flush --hard
"$WP" cache flush || true

echo "--- state ---"
"$WP" option get home
"$WP" option get siteurl
"$WP" theme list --status=active --field=name
"$WP" plugin list --status=active --field=name
"$WP" post list --post_type=post --format=count
