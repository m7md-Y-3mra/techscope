#!/usr/bin/env bash
#
# Dumps the database and the uploads directory into ./backups.
# Run on the server; wire it to cron for a nightly copy:
#
#   0 3 * * * cd /home/ubuntu/techscope && ./deploy/backup.sh >> /var/log/techscope-backup.log 2>&1
#
# Keeps the 14 most recent sets and deletes nothing else.

set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE="docker compose -f docker-compose.prod.yml"
STAMP="$(date -u +%Y%m%d-%H%M%S)"
KEEP=14

mkdir -p backups

set -a; . ./.env; set +a

echo "==> Dumping database"
$COMPOSE exec -T db mariadb-dump \
	--user="$MARIADB_USER" \
	--password="$MARIADB_PASSWORD" \
	--single-transaction \
	--quick \
	--default-character-set=utf8mb4 \
	"$MARIADB_DATABASE" | gzip -9 > "backups/db-${STAMP}.sql.gz"

echo "==> Archiving uploads"
$COMPOSE exec -T wordpress tar -C /var/www/html/wp-content -cf - uploads \
	| gzip -9 > "backups/uploads-${STAMP}.tar.gz"

echo "==> Pruning to the newest $KEEP of each"
for prefix in db uploads; do
	ls -1t backups/${prefix}-*.gz 2>/dev/null | tail -n +$((KEEP + 1)) | while read -r old; do
		echo "    removing $old"
		rm -f "$old"
	done
done

ls -lh backups | tail -5
echo "==> Done: backups/db-${STAMP}.sql.gz, backups/uploads-${STAMP}.tar.gz"
