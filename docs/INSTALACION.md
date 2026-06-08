# 🧭 Guía de instalación y ejecución — NutriPredict Escolar

Cómo ejecutar el proyecto en **cualquier PC** (Windows, macOS o Linux).
Hay dos caminos: **Docker** (recomendado, todo automático) o **manual** (PHP + MySQL/XAMPP).

---

## 🚚 Llevarlo a otro PC: idea clave

Todo el proyecto vive en **Git** y se ejecuta con **Docker**. Por eso, en un PC
nuevo solo necesitas instalar **2 cosas** (Git y Docker) y correr **1 comando**.
No hay que copiar bases de datos a mano ni instalar PHP: la imagen de Docker ya
trae PHP+Apache y la base de datos se crea sola con los datos de demostración.

```
PC nuevo  →  instalar Git + Docker  →  git clone  →  docker compose up  →  listo
```

---

## ✅ Paso 0 — Obtener el código

```bash
git clone https://github.com/yesidbadillo820-ship-it/Predicciones-de-nutrici-n.git
cd Predicciones-de-nutrici-n
```
> Alternativa sin Git: en GitHub, botón **Code → Download ZIP**, y descomprime.

---

## ⚡ Arranque en 1 clic (lo más fácil)

Con **Docker instalado**, dentro de la carpeta del proyecto:

- **Windows:** doble clic en **`start.bat`** (para detener: `stop.bat`).
- **Linux / macOS:**
  ```bash
  ./start.sh        # para detener: ./stop.sh
  ```

El script levanta todo y abre `http://localhost:8080` solo.
Login: **admin@nutripredict.edu.co / demo123**.

Si prefieres hacerlo a mano, sigue la Opción A o B de abajo.

---

## 🐳 OPCIÓN A — Con Docker (recomendada)

### A.1 Instalar Docker
- **Windows / macOS:** instala **Docker Desktop** → https://www.docker.com/products/docker-desktop
  Ábrelo y espera a que diga *running*.
- **Linux:** instala `docker` y `docker compose` con el gestor de paquetes de tu distro.

### A.2 Levantar la aplicación
Dentro de la carpeta del proyecto:
```bash
docker compose up --build
```
La **primera vez** descarga las imágenes (unos minutos). Está listo cuando veas
en la consola algo como `ready for connections` (base de datos) y Apache activo.

### A.3 Abrir en el navegador
```
http://localhost:8080
```

### A.4 Iniciar sesión (contraseña: demo123)
| Rol | Correo |
|-----|--------|
| Administrador | `admin@nutripredict.edu.co` |
| Enc. Restaurante | `restaurante@nutripredict.edu.co` |
| Docente | `docente@nutripredict.edu.co` |
| Directora | `directora@nutripredict.edu.co` |

### A.5 Detener / reiniciar
```bash
# Detener (Ctrl+C en la consola) y luego:
docker compose down        # conserva los datos (volumen db_data)
docker compose up          # volver a arrancar (ya no recompila)
docker compose down -v     # ⚠️ borra también la base de datos (reinicio total)
```

> **Si aparece "toomanyrequests / rate limit"** al descargar imágenes:
> crea una cuenta gratis en hub.docker.com y ejecuta `docker login`, luego repite.

---

## 🛠️ OPCIÓN B — Manual (sin Docker)

Requisitos: **PHP 8.1+** con extensiones `mysqli` y `curl`, y **MySQL 8 / MariaDB**.

### B.1 Linux / macOS / WSL
```bash
# 1) Verificar PHP
php -v
php -m | grep -E "mysqli|curl"

# 2) Crear BD + datos demo
mysql -u root -p < database/schema.sql
mysql -u root -p nutripredict_db < database/seed.sql

# 3) (Opcional) usuario de BD con permisos mínimos
mysql -u root -p -e "CREATE USER 'nutripredict'@'localhost' IDENTIFIED BY 'clave_segura';
GRANT SELECT,INSERT,UPDATE,DELETE ON nutripredict_db.* TO 'nutripredict'@'localhost';
FLUSH PRIVILEGES;"

# 4) Configurar entorno
cp .env.example .env        # edita DB_USER/DB_PASS/DB_NAME, etc.

# 5) Arrancar
php -S localhost:8000
```
Abre `http://localhost:8000`.

### B.2 Windows con XAMPP
1. Instala **XAMPP** (https://www.apachefriends.org). Abre el panel y arranca **Apache** y **MySQL**.
2. Copia el proyecto a `C:\xampp\htdocs\nutripredict`.
3. Abre `http://localhost/phpmyadmin` → **Importar** → `database/schema.sql`; repite con `database/seed.sql`.
4. Crea `.env` (copia de `.env.example`):
   ```ini
   DB_HOST=127.0.0.1
   DB_USER=root
   DB_PASS=
   DB_NAME=nutripredict_db
   ```
5. Abre `http://localhost/nutripredict/`.

---

## 📱 Acceder desde el celular (misma red Wi-Fi)
1. Servidor escuchando en toda la red: Docker ya lo hace; en manual usa `php -S 0.0.0.0:8000`.
2. IP del PC: `ipconfig` (Windows) o `ip a` (Linux/Mac) → ej. `192.168.1.50`.
3. En el celular (misma Wi-Fi): `http://192.168.1.50:8080`.
4. Instalar como app: Android (Chrome) → ⋮ → *Instalar app*; iOS (Safari) → *Compartir* → *Agregar a pantalla de inicio*.

---

## 🔍 Comprobar que funciona
```bash
curl http://localhost:8080/health.php     # Docker  → {"status":"ok","db":"up"}
curl http://localhost:8000/health.php     # manual
```

## ⚙️ Comandos útiles (desarrollo / operación)
```bash
composer install            # PHPUnit + PHPStan (solo desarrollo)
composer test               # pruebas unitarias
composer analyse            # análisis estático
php bin/run_prediction.php   # recalcular riesgo (cron diario)
./scripts/backup.sh          # backup de la base de datos
```

---

## 🚑 Problemas comunes
| Síntoma | Solución |
|---|---|
| "Error de conexión a la base de datos" | Revisa `.env` (DB_*) y que MySQL esté corriendo |
| Página en blanco / 500 | Pon `APP_DEBUG=true` en `.env` y revisa `logs/app.log` |
| "token CSRF inválido" | Recarga la página (sesión expirada) |
| El login no entra | ¿Importaste `seed.sql`? De ahí salen las cuentas y `demo123` |
| Puerto ocupado | Cambia el puerto (`php -S localhost:8001` o el mapeo en `docker-compose.yml`) |
| NutriBot genérico | Normal sin `ANTHROPIC_API_KEY` (usa respuestas de respaldo) |

---

## 🔐 Para producción (resumen)
- Sirve por **HTTPS** (descomenta el bloque de redirección en `.htaccess`).
- Usa contraseñas reales y **borra las cuentas demo**.
- Define las variables de entorno en el host (no subas `.env` al repositorio).
