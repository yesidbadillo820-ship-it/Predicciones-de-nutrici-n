#!/usr/bin/env bash
# Mensaje de bienvenida al abrir el Codespace.
cat <<'TXT'

═══════════════════════════════════════════════════════════════
  🥗  NutriPredict Escolar — listo en Codespaces
═══════════════════════════════════════════════════════════════

La INTERFAZ VISUAL (monolito) se está levantando automáticamente.
Cuando esté lista, se abrirá el puerto 8080 (pestaña "Ports" / "Puertos").

  ▶ Abrir la app:   pestaña PORTS → puerto 8080 → 🌐 (Open in Browser)
  ▶ Login:          admin@nutripredict.edu.co  /  demo123

Si el puerto 8080 no aparece aún, espera 1-2 minutos (está construyendo)
o ejecuta:  docker compose up -d

───────────────────────────────────────────────────────────────
  ¿Quieres ver la versión de MICROSERVICIOS (API Gateway)?
───────────────────────────────────────────────────────────────
  cd microservices
  docker compose up -d --build
  # Luego abre el puerto 8090 → /health

═══════════════════════════════════════════════════════════════
TXT
