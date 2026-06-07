#!/usr/bin/env bash
# stop.sh — Detiene NutriPredict (conserva la base de datos).
cd "$(dirname "$0")"
docker compose down
echo "🛑 NutriPredict detenido. Los datos se conservan."
echo "   Para borrar también la base de datos:  docker compose down -v"
