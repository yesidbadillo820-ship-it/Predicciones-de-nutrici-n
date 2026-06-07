#!/usr/bin/env bash
# start.sh — Arranque de un clic para Linux/macOS.
# Levanta NutriPredict (app + base de datos) con Docker y abre el navegador.
set -e
cd "$(dirname "$0")"

if ! command -v docker >/dev/null 2>&1; then
  echo "❌ Docker no está instalado."
  echo "   Instálalo desde: https://www.docker.com/products/docker-desktop"
  exit 1
fi

echo "🚀 Levantando NutriPredict (la primera vez puede tardar unos minutos)..."
docker compose up --build -d

echo "⏳ Esperando a que la aplicación responda..."
URL="http://localhost:8080"
for i in $(seq 1 60); do
  if curl -fs "$URL/health.php" >/dev/null 2>&1; then break; fi
  sleep 2
done

echo ""
echo "✅ Listo. Abre:  $URL"
echo "   Usuario: admin@nutripredict.edu.co   Contraseña: demo123"
echo "   Para detener:  ./stop.sh"

# Intentar abrir el navegador (no es crítico si falla)
( xdg-open "$URL" || open "$URL" ) >/dev/null 2>&1 || true
