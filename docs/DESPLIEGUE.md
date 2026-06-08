# Despliegue permanente + generación del APK

Para tener un **APK que funcione siempre**, la app debe estar publicada en una
**URL pública 24/7**. Esta guía despliega la versión **monolito** (1 app + 1
base de datos) — es la más sencilla de alojar y tiene la interfaz completa.

> El proyecto ya está preparado: la imagen Docker **crea la base de datos y los
> datos demo automáticamente** al arrancar (`docker/init-db.php`) y respeta el
> puerto que asigne el host (`$PORT`).

## Parte A — Publicar la app (Railway)

Railway es gratuito para empezar y soporta PHP + MySQL + Docker.

1. Entra a **https://railway.app** e inicia sesión con tu cuenta de **GitHub**.
2. **New Project → Deploy from GitHub repo →** elige `Predicciones-de-nutrici-n`.
   Railway detecta el `Dockerfile` de la raíz y construye la app.
3. En el proyecto: **New → Database → Add MySQL**.
4. Abre el servicio de la **app → pestaña Variables** y agrega (usando referencias
   a la base de datos que creaste):
   | Variable | Valor |
   |----------|-------|
   | `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
   | `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
   | `DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
   | `DB_USER` | `${{MySQL.MYSQLUSER}}` |
   | `DB_PASS` | `${{MySQL.MYSQLPASSWORD}}` |
   | `APP_ENV` | `production` |
5. La app se redepliega; al arrancar crea el esquema y los datos demo solos.
6. Servicio de la app → **Settings → Networking → Generate Domain**. Obtienes una
   URL `https://....up.railway.app`.
7. Abre esa URL e inicia sesión: **admin@nutripredict.edu.co / demo123**.

> Alternativa (Render): funciona con el mismo `Dockerfile`, pero Render no ofrece
> MySQL gratis; tendrías que usar una base MySQL externa. Por eso aquí se usa Railway.

## Parte B — Generar el `.apk`

Con la URL pública de la Parte A:

1. Entra a **https://www.pwabuilder.com**
2. Pega la URL y pulsa **Start**. (El manifiesto y los íconos ya están incluidos.)
3. Ve a **Package For Stores → Android → Generate Package**.
4. Descarga el `.zip`; dentro está **`app-release-signed.apk`** listo para instalar
   en un teléfono Android (Ajustes → permitir instalar de orígenes desconocidos).

¡Listo! Ese `.apk` abre tu app desde la URL pública, así que funcionará mientras
el despliegue de Railway esté activo.

---

### Nota de costos
Railway da un crédito gratuito mensual suficiente para una demostración. Si el
proyecto debe quedar disponible a largo plazo, revisa los límites del plan
gratuito o usa un VPS propio con `docker compose`.
