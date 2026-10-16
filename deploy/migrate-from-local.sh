#!/usr/bin/env bash
#
# Copies this machine's local TechScope database and uploads to the server,
# rewriting URLs on the way. Run on your LOCAL machine, from the repo root.
#
#   ./deploy/migrate-from-local.sh ubuntu@203.0.113.10 https://techscope.example.com
#
# Only needed if you want the content you authored locally. A fresh server can
# instead seed itself from the committed scripts, which deploy.sh does already.

set -euo pipefail

cd "$(dirname "$0")/.."

TARGET="${1:?usage: migrate-from-local.sh <user@host> <https://domain> [remote-path]}"
REMOTE_URL="${2:?usage: migrate-from-local.sh <user@host> <https://domain> [remote-path]}"
REMOTE_PATH="${3:-techscope}"

LOCAL_URL="http://localhost:8080"
STAMP="$(date -u +%Y%m%d-%H%M%S)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

echo "==> Exporting local database"
docker compose run --rm -T cli wp db export - | gzip -9 > "$WORK/db.sql.gz"

echo "==> Exporting local uploads"
docker compose exec -T wordpress tar -C /var/www/html/wp-content -cf - uploads \
	| gzip -9 > "$WORK/uploads.tar.gz"

echo "==> Copying to $TARGET:$REMOTE_PATH"
ssh "$TARGET" "mkdir -p '$REMOTE_PATH/transfer'"
scp "$WORK/db.sql.gz" "$WORK/uploads.tar.gz" "$TARGET:$REMOTE_PATH/transfer/"

echo "==> Importing on the server and rewriting $LOCAL_URL -> $REMOTE_URL"
ssh "$TARGET" bash -s <<REMOTE
set -euo pipefail
cd "$REMOTE_PATH"
C="docker compose -f docker-compose.prod.yml"

gunzip -c transfer/db.sql.gz | \$C run --rm -T cli wp db import -
\$C exec -T wordpress sh -c 'rm -rf /var/www/html/wp-content/uploads'
gunzip -c transfer/uploads.tar.gz | \$C exec -T wordpress tar -C /var/www/html/wp-content -xf -
\$C exec -T wordpress chown -R 33:33 /var/www/html/wp-content/uploads

# search-replace handles serialised data, which a plain sed over the dump does not.
\$C run --rm -T cli wp search-replace '$LOCAL_URL' '$REMOTE_URL' --all-tables-with-prefix --report-changed-only
\$C run --rm -T cli wp cache flush
\$C run --rm -T cli wp rewrite flush --hard

rm -rf transfer
REMOTE

echo "==> Done. Verify: curl -sI $REMOTE_URL/ | head -1"
