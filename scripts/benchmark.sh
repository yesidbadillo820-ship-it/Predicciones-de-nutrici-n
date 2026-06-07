#!/usr/bin/env bash
# scripts/benchmark.sh — Prueba de rendimiento de NutriPredict.
# Inicia sesión (manejando CSRF), reutiliza la cookie y mide latencia/throughput
# de las páginas autenticadas con Apache Bench (ab).
#
# Uso:   ./scripts/benchmark.sh [BASE_URL] [N_PETICIONES] [CONCURRENCIA]
# Ej.:   ./scripts/benchmark.sh http://localhost:8080 300 20
set -euo pipefail

BASE_URL="${1:-http://localhost:8080}"
N="${2:-300}"
C="${3:-20}"
EMAIL="${BENCH_EMAIL:-admin@nutripredict.edu.co}"
PASS="${BENCH_PASS:-demo123}"
JAR="$(mktemp)"

command -v ab >/dev/null || { echo "Apache Bench (ab) no está instalado. Instala 'apache2-utils'."; exit 1; }

echo "▶ Objetivo: $BASE_URL  ·  $N peticiones, concurrencia $C"

# 1) Obtener cookie de sesión + token CSRF de la página de login
csrf="$(curl -s -c "$JAR" "$BASE_URL/login.php" | grep -oP 'name="csrf_token" value="\K[^"]+' | head -1)"
[ -n "$csrf" ] && echo "  ✓ token CSRF obtenido" || { echo "  ✗ no se pudo leer el token CSRF"; exit 1; }

# 2) Iniciar sesión
curl -s -b "$JAR" -c "$JAR" \
  --data-urlencode "email=$EMAIL" \
  --data-urlencode "password=$PASS" \
  --data-urlencode "csrf_token=$csrf" \
  "$BASE_URL/login.php" -o /dev/null
sid="$(grep -i 'PHPSESSID' "$JAR" | awk '{print $7}' | tail -1)"
[ -n "$sid" ] && echo "  ✓ sesión iniciada (PHPSESSID=$sid)" || { echo "  ✗ no se pudo iniciar sesión"; exit 1; }

run_ab () {
  local label="$1" path="$2" cookie="${3:-}"
  echo ""
  echo "── $label → $path"
  if [ -n "$cookie" ]; then
    ab -n "$N" -c "$C" -C "PHPSESSID=$sid" "$BASE_URL$path" 2>/dev/null \
      | grep -E "Requests per second|Time per request|Failed requests|Complete requests|Percentage of the requests" -A0
  else
    ab -n "$N" -c "$C" "$BASE_URL$path" 2>/dev/null \
      | grep -E "Requests per second|Time per request|Failed requests|Complete requests"
  fi
}

run_ab "Landing pública"        "/index.php"
run_ab "Dashboard (auth)"       "/dashboard.php"      "auth"
run_ab "Estudiantes (auth)"     "/estudiantes.php"    "auth"
run_ab "Análisis predictivo"    "/predictivo.php"     "auth"

rm -f "$JAR"
echo ""
echo "✔ Benchmark completado."
