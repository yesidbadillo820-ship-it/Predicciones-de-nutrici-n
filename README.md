# NutriPredict Escolar 🥗

Sistema de gestión y predicción nutricional para instituciones educativas colombianas.

## ▶️ Verlo funcionando sin instalar nada (GitHub Codespaces)

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/yesidbadillo820-ship-it/Predicciones-de-nutrici-n)

1. Haz clic en el botón de arriba (o en GitHub: **Code → Codespaces → Create codespace on main**).
2. Espera 1–2 minutos: el entorno se prepara y **levanta la app automáticamente**.
3. En la pestaña **PORTS / PUERTOS**, abre el puerto **8080** 🌐 → se abre la interfaz.
4. Inicia sesión: **admin@nutripredict.edu.co** / **demo123**.

> Todo corre en la nube, en el navegador. El evaluador solo necesita una cuenta de GitHub; no instala Docker, PHP ni MySQL.
> Para ver la versión de **microservicios**: en la terminal del Codespace ejecuta `cd microservices && docker compose up -d --build` y abre el puerto **8090**.

---

## 🚀 Mejoras implementadas (v2)

### 1. 🤖 NutriBot — Asistente Virtual IA
- **Archivo:** `nutribot.php`
- Chatbot flotante integrado en todas las páginas del sistema
- Powered by **Claude AI (Anthropic)** via API REST
- Contexto en tiempo real: lee datos actuales de la BD (alertas, riesgo, asistencia)
- Historial de conversación (últimos 10 mensajes)
- Chips de sugerencias rápidas para consultas frecuentes
- Indicador de escritura animado
- Respuesta de fallback si la API no está disponible

### 2. 🌙 Modo Oscuro / Claro
- Toggle en la barra superior de todas las páginas
- Persiste entre sesiones usando `localStorage`
- Diseño completo adaptado (topbar, cards, tablas, formularios)

### 3. 📚 Centro de Ayuda (`ayuda.php`)
- Documentación completa de cada módulo
- Explicación detallada del algoritmo predictivo
- Guías paso a paso para tareas frecuentes
- Tabla de referencia nutricional (OMS / ICBF Colombia)
- Información técnica de la arquitectura
- Accesible desde el menú lateral (sección Soporte)

---

## 🧩 Versiones de arquitectura

Este repositorio incluye **dos arquitecturas** del mismo sistema:
- **Monolito (MVP)** — raíz del proyecto. Simple de desplegar; ideal para una institución.
- **Microservicios** — carpeta [`microservices/`](microservices/README.md). API Gateway + 9 servicios independientes con base de datos por servicio (según el material de microservicios). Se ejecuta con `cd microservices && docker compose up --build` (gateway en `:8090`).

---

## 🧩 Instalación y puesta en marcha

> 📘 **Guía completa paso a paso** (incluye cómo ejecutarlo en otro PC, XAMPP,
> Docker y solución de problemas): [`docs/INSTALACION.md`](docs/INSTALACION.md)

### Requisitos
- PHP 8.1+ (probado en 8.4) con extensiones `mysqli` y `curl`
- MySQL 8 / MariaDB 10.4+
- Servidor web Apache (con `mod_rewrite` y `mod_headers`) o Nginx

### Pasos
```bash
# 1. Clonar el repositorio
git clone <repo> nutripredict && cd nutripredict

# 2. Crear la base de datos y cargar el esquema
mysql -u root -p < database/schema.sql

# 3. (Opcional) Cargar datos de demostración
mysql -u root -p nutripredict_db < database/seed.sql

# 4. Configurar variables de entorno
cp .env.example .env
#   edita .env con las credenciales reales de tu BD y, opcionalmente, ANTHROPIC_API_KEY

# 5. Apuntar el DocumentRoot del servidor a la carpeta del proyecto
#    e iniciar (ejemplo de desarrollo local):
php -S localhost:8000
```

Luego abre `http://localhost:8000` e inicia sesión con una credencial demo
(ver `database/seed.sql`); todas usan la contraseña **`demo123`**.

### Opción rápida con Docker
Levanta la app + MySQL (con esquema y datos demo cargados automáticamente):
```bash
docker compose up --build
# App en http://localhost:8080  ·  login: admin@nutripredict.edu.co / demo123
```

### Integración continua (CI)
El workflow `.github/workflows/ci.yml` se ejecuta en cada PR y:
1. Verifica la sintaxis de todos los `.php` (`php -l`) y corre **PHPUnit**.
2. Levanta MySQL, carga `schema.sql` + `seed.sql` y corre `tests/db_smoke.php`
   (valida conexión, login demo y consultas clave contra una BD real).

### Comandos útiles
```bash
composer install                 # dependencias (PHPUnit, PHPStan)
composer test                    # pruebas unitarias
composer analyse                 # análisis estático (PHPStan nivel 5)
php bin/run_prediction.php        # recálculo de riesgo (para cron diario)
./scripts/backup.sh               # copia de seguridad de la BD
./scripts/benchmark.sh http://localhost:8080 400 15   # prueba de rendimiento
curl http://localhost:8080/health.php                 # health-check (monitoreo)
```

**Observabilidad:** los eventos clave (login, errores de BD, predicción) se
registran en formato JSON en `logs/app.log`. El endpoint `health.php` devuelve
`{status, db, env}` para monitores de *uptime*.

Detalles de rendimiento y resultados de referencia en **`docs/RENDIMIENTO.md`**.

### 📱 Instalar como app (PWA)
La aplicación es una **PWA**: se puede instalar en el celular y abrir a pantalla
completa, como una app nativa.
- **Android (Chrome):** menú ⋮ → «Añadir a pantalla de inicio / Instalar app».
- **iOS (Safari):** botón compartir → «Agregar a pantalla de inicio».

Requiere servir el sitio por **HTTPS** (o `localhost` para pruebas). Los íconos
se generan con `php scripts/make_icons.php` (`css/icons/`), y el comportamiento
offline básico lo da `sw.js` + `manifest.webmanifest`.


> ⚠️ **Seguridad:** el archivo `.env` nunca debe subirse al repositorio.
> Las carpetas `includes/`, `models/`, `presenters/`, `views/`, `config/` y
> `database/` incluyen un `.htaccess` que bloquea su acceso web directo.

---

## ⚙️ Configuración de NutriBot

Para activar NutriBot con respuestas de IA real, configura la variable de entorno:

```bash
# En tu servidor (Apache/Nginx vhost o .htaccess)
SetEnv ANTHROPIC_API_KEY sk-ant-...

# O en el entorno del sistema
export ANTHROPIC_API_KEY=sk-ant-...
```

Sin la API key, NutriBot responde con mensajes de fallback basados en los datos reales de la BD.

---

## 🏗️ Arquitectura

```
nutripredict/
├── index.php              — Landing pública
├── login.php              — Autenticación
├── logout.php             — Cierre de sesión
├── dashboard.php          — Panel principal
├── estudiantes.php        — Gestión de estudiantes
├── menus.php              — Menús del día
├── alimentos.php          — Catálogo de alimentos
├── asistencia.php         — Asistencia al comedor
├── alertas.php            — Alertas nutricionales
├── predictivo.php         — Análisis predictivo
├── reportes.php           — Reportes estadísticos
├── usuarios.php           — Usuarios y roles
├── nutribot.php           — Endpoint del asistente virtual
├── ayuda.php              — Centro de ayuda
├── sin_acceso.php         — Página de acceso denegado
│
├── .htaccess              — Seguridad Apache (raíz)
├── .env.example           — Plantilla de variables de entorno
│
├── config/
│   └── config.php         — Configuración central (lee .env / entorno)
│
├── database/
│   ├── schema.sql         — Estructura de la base de datos
│   └── seed.sql           — Datos de demostración
│
├── includes/
│   ├── auth.php           — Autenticación y sesiones
│   ├── db.php             — Conexión a la base de datos (por entorno)
│   ├── header.php         — Layout: sidebar + topbar (con dark mode y ayuda)
│   ├── footer.php         — Layout: cierre + NutriBot widget
│   └── roles.php          — Control de permisos por rol
│
├── models/                — Capa de datos (patrón MVP)
│   ├── AlertaModel.php
│   ├── AlimentoModel.php
│   ├── AsistenciaModel.php
│   ├── EstudianteModel.php
│   ├── MenuModel.php
│   ├── ReporteModel.php
│   └── UsuarioModel.php
│
├── presenters/            — Lógica de presentación (patrón MVP)
│   ├── AlertaPresenter.php
│   ├── AsistenciaPresenter.php
│   ├── AuthPresenter.php
│   ├── DashboardPresenter.php
│   ├── EstudiantePresenter.php
│   ├── MenuPresenter.php
│   └── PredictivoPresenter.php
│
├── views/                 — Vistas HTML puras (patrón MVP)
│   ├── dashboard_view.php
│   └── estudiantes_view.php
│
├── css/
│   └── main.css           — Estilos globales + NutriBot + Dark Mode
│
└── js/
    └── main.js            — Scripts globales
```

---

## 🎯 Patrón MVP

Cada funcionalidad sigue el patrón **Model-View-Presenter**:

1. **Model** (`models/`) — Solo acceso a datos, sin lógica de negocio
2. **Presenter** (`presenters/`) — Lógica de negocio, prepara datos para la vista
3. **View** (`views/`) — Solo muestra HTML, sin lógica ni consultas
4. **Entry point** (`.php` raíz) — Orquesta Presenter → Vista

---

## 🤖 Motor Predictivo

Scoring de riesgo (0-100) por estudiante:

| Factor | Condición | Puntos |
|--------|-----------|--------|
| Alertas activas | ≥ 3 alertas | +40 |
| Alertas activas | 1-2 alertas | +25 |
| Inasistencias | ≥ 5 en 10 días | +20 |
| Inasistencias | 2-4 en 10 días | +10 |
| Cobertura nutricional | < 60% | +30 |
| Cobertura nutricional | 60-74% | +15 |
| Alertas resueltas | ≥ 2 resueltas | -10 |

**Niveles:** Sin Riesgo (0-14) · Bajo (15-39) · Medio (40-69) · Alto (70-100)

---

## 🛠️ Tecnologías

- **Backend:** PHP 8+ · MySQLi · cURL
- **Frontend:** CSS3 · Chart.js 4.4 · Google Fonts (Plus Jakarta Sans, DM Sans)
- **IA:** Claude claude-sonnet-4-20250514 (Anthropic API)
- **Gráficas:** Chart.js (líneas, barras, dona)
- **Diseño:** Sistema de diseño personalizado con variables CSS
