#!/usr/bin/env bash
cat <<'TXT'

  NutriPredict Escolar — entorno listo

  La plataforma de MICROSERVICIOS se está iniciando automáticamente.
  Cuando termine, se abrirá el puerto 8090 (pestaña "Ports" / "Puertos").

    Abrir:  pestaña PORTS -> puerto 8090 -> icono del globo
    Login:  admin@nutripredict.edu.co  /  demo123

  Si aún no aparece, espera 1-2 minutos (está construyendo) o ejecuta:
    cd microservices && docker compose up -d

TXT
