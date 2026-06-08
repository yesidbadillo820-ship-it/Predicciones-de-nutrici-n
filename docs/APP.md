# NutriPredict como aplicación (PWA y APK)

La aplicación es una **PWA**: se puede instalar en el celular o el computador y
se abre a pantalla completa, como una app nativa.

## Opción 1 — Instalar como app (inmediato, sin archivos)
Con la app abierta en el navegador (puerto **8090**):
- **Android (Chrome):** menú ⋮ → **Instalar app / Añadir a pantalla de inicio**.
- **iPhone (Safari):** botón compartir → **Agregar a pantalla de inicio**.
- **Windows/Mac (Chrome/Edge):** ícono de instalar en la barra de direcciones.

Queda con ícono propio y a pantalla completa. Para la mayoría de entregas, esto
es suficiente y es lo más rápido.

## Opción 2 — Generar un archivo .apk (Android)
Un `.apk` envuelve la app web, así que **primero la app debe estar en una URL
pública HTTPS**. Hay dos formas de tener esa URL:

- **Temporal (para una demo):** en GitHub Codespaces, pestaña **Ports** → clic
  derecho en el puerto **8090** → **Port Visibility → Public**, y copia la URL
  `https://....app.github.dev`. Funciona mientras el Codespace esté encendido.
- **Permanente:** desplegar el proyecto en un host (Render, Railway, un VPS…).

Con esa URL pública, genera el APK sin instalar nada:

1. Entra a **https://www.pwabuilder.com**
2. Pega la URL pública de la app y pulsa **Start**.
3. PWABuilder analiza el manifiesto (ya incluido en este proyecto).
4. En **Package For Stores → Android**, pulsa **Generate Package**.
5. Descarga el `.zip`; dentro está el **`app-release-signed.apk`** para instalar.

> Alternativa por consola (si tienes Android Studio/JDK):
> `npm i -g @bubblewrap/cli` y luego
> `bubblewrap init --manifest https://TU-URL/manifest.webmanifest` y `bubblewrap build`.

## Importante
El `.apk` solo mostrará datos mientras el **backend** (microservicios + base de
datos) esté accesible en esa URL pública. Si la URL se apaga, la app no carga
información. Por eso, para un APK que funcione siempre, hace falta un despliegue
permanente.
