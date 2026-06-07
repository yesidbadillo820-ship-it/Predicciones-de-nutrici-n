#!/usr/bin/env bash
# scripts/backup.sh — Copia de seguridad de la base de datos NutriPredict.
# Lee la configuración desde variables de entorno o desde el archivo .env.
# Uso:  ./scripts/backup.sh [directorio_destino]
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Cargar .env si existe (sin sobrescribir variables ya definidas)
if [ -f "$ROOT_DIR/.env" ]; then
  set -a
  # shellcheck disable=SC1091
  . "$ROOT_DIR/.env"
  set +a
fi

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-nutripredict_db}"

DEST_DIR="${1:-$ROOT_DIR/backups}"
mkdir -p "$DEST_DIR"
STAMP="$(date +%Y%m%d_%H%M%S)"
OUT="$DEST_DIR/${DB_NAME}_${STAMP}.sql.gz"

echo "Generando backup de '$DB_NAME' → $OUT"
mysqldump --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" --password="$DB_PASS" \
  --single-transaction --routines --triggers "$DB_NAME" | gzip > "$OUT"

# Retención: conservar los últimos 14 backups
ls -1t "$DEST_DIR/${DB_NAME}_"*.sql.gz 2>/dev/null | tail -n +15 | xargs -r rm -f

echo "Backup completado: $(du -h "$OUT" | cut -f1)"
