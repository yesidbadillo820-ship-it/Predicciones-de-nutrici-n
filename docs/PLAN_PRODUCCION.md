# 🚀 Plan Estratégico — De Proyecto a Producción
### NutriPredict Escolar

> Documento guía para llevar el sistema desde su estado actual (prototipo
> funcional) hasta un despliegue productivo estable, seguro y mantenible.

---

## 1. Estado actual (diagnóstico)

| Área | Estado | Observación |
|------|--------|-------------|
| Arquitectura | 🟢 Buena | Patrón MVP claro (Model · View · Presenter) |
| Organización de archivos | 🟢 Corregida | Antes estaban planos; ahora en carpetas (`includes/`, `models/`, `presenters/`, `views/`, `css/`, `js/`, `config/`, `database/`) |
| Base de datos | 🟢 Definida | Antes **no existía** el esquema; ahora `database/schema.sql` + `seed.sql` |
| Configuración | 🟢 Externalizada | Credenciales por `.env` / variables de entorno |
| Seguridad | 🟡 Parcial | Bcrypt y consultas preparadas en su mayoría; faltan CSRF, regeneración de sesión, HTTPS forzado |
| Calidad / Pruebas | 🔴 Ausente | Sin pruebas automatizadas ni CI |
| Observabilidad | 🔴 Ausente | Sin logging estructurado ni monitoreo |

### Correcciones ya aplicadas en esta entrega
1. **Reorganización en carpetas** según el patrón MVP que el propio código ya
   esperaba (las rutas `require 'includes/...'`, `models/...`, etc. apuntaban a
   carpetas que no existían → la app **no podía arrancar**).
2. **Bug crítico** en `EstudianteModel::actualizar()`: la cadena de tipos de
   `bind_param('ssssiiddss i', …)` tenía un espacio y tipos incorrectos →
   error fatal de mysqli al editar un estudiante. Corregido a `'ssssidddssi'`.
3. **`actualizarRiesgo()`** ahora acepta y persiste el `score` (antes el
   `PredictivoPresenter` lo pasaba pero el modelo lo descartaba en silencio).
4. **HTML duplicado** en `login.php` (`<body>`/`login-box` repetidos y `<div>`
   sin cerrar). Corregido.
5. **Conexión a BD configurable** por entorno con manejo de errores robusto
   (`config/config.php` + `includes/db.php`), en lugar de credenciales fijas.
6. **NutriBot** usa la API key y el modelo desde configuración, y cae a
   respuestas de respaldo si no hay clave.
7. **Esquema y datos demo** de la base de datos creados desde cero.
8. **Endurecimiento Apache**: `.htaccess` raíz + `.htaccess` que bloquean el
   acceso web directo a las carpetas internas.

---

## 2. Deuda técnica conocida (a resolver antes de producción)

| # | Tema | Riesgo | Acción recomendada |
|---|------|--------|--------------------|
| D1 | Tabla `cobertura_nutricional` se **lee** (reportes/predicción) pero el código no la **escribe** — solo se llena con `seed.sql` | Reportes vacíos en uso real | Poblarla desde `MenuModel::guardar()` con nombres de nutriente canónicos |
| D2 | Varias consultas interpolan valores (aunque casteados a `int` o escapados) | Inyección SQL si cambia el flujo | Migrar todo a sentencias preparadas |
| D3 | Sin token CSRF en formularios POST | Falsificación de peticiones | Añadir token CSRF por sesión |
| D4 | No se regenera el ID de sesión al iniciar sesión | Fijación de sesión | `session_regenerate_id(true)` en login |
| D5 | Cookies de sesión sin flags `Secure`/`HttpOnly`/`SameSite` | Robo de sesión | Configurar `session.cookie_*` |
| D6 | Sin límite de intentos de login | Fuerza bruta | Rate limiting / bloqueo temporal |
| D7 | Sin pruebas automatizadas | Regresiones | PHPUnit para modelos y presenters |

---

## 3. Hoja de ruta por fases

### Fase 0 — Estabilización (½ semana) ✅ en curso
- [x] Reorganizar el proyecto en carpetas
- [x] Crear esquema y datos de demostración
- [x] Externalizar configuración (`.env`)
- [x] Corregir bugs bloqueantes
- [ ] Validar el flujo completo en un entorno con MySQL real

### Fase 1 — Seguridad mínima para producción (1 semana)
- [ ] CSRF en todos los formularios (D3)
- [ ] `session_regenerate_id` + flags de cookie seguras (D4, D5)
- [ ] Forzar HTTPS (redirección + HSTS)
- [ ] Rate limiting en login (D6)
- [ ] Revisar y migrar consultas a preparadas (D2)
- [ ] Crear usuario de BD con privilegios mínimos (no `root`)

### Fase 2 — Calidad y automatización (1–2 semanas)
- [ ] Composer para autoload PSR-4 y dependencias
- [ ] PHPUnit: pruebas de `EstudianteModel`, `MenuModel`, `PredictivoPresenter`
- [ ] PHP_CodeSniffer / PHPStan (análisis estático)
- [ ] Pipeline CI (GitHub Actions): lint + análisis + pruebas en cada PR

### Fase 3 — Infraestructura y despliegue (1 semana)
- [ ] Dockerizar (PHP-FPM + Nginx + MySQL) con `docker-compose`
- [ ] Variables de entorno gestionadas por el orquestador/host
- [ ] Migraciones versionadas de BD (p. ej. Phinx)
- [ ] Estrategia de despliegue (blue-green o rolling) y *rollback*

### Fase 4 — Operación (continuo)
- [ ] Logging estructurado (Monolog) a archivo/servicio central
- [ ] Monitoreo (uptime, errores, latencia) y alertas
- [ ] Copias de seguridad automáticas de la BD + prueba de restauración
- [ ] Cron para `PredictivoPresenter::ejecutarPrediccion()` (recálculo diario)

---

## 4. Arquitectura de despliegue recomendada

```
            ┌─────────────┐
  Internet ─┤  HTTPS / WAF │
            └──────┬──────┘
                   │
            ┌──────▼──────┐      ┌──────────────┐
            │   Nginx     │──────│  PHP-FPM 8.x │  (app NutriPredict)
            │ (reverse    │      └──────┬───────┘
            │  proxy)     │             │
            └─────────────┘      ┌──────▼───────┐
                                 │   MySQL 8    │  (datos)
                                 └──────────────┘
                                        │
                                 ┌──────▼───────┐
                                 │  Backups +   │
                                 │  Monitoreo   │
                                 └──────────────┘
```

- **Entornos:** `development` → `staging` → `production`, cada uno con su `.env`.
- **Secretos:** gestionados por el host (variables de entorno) o un *secret manager*; nunca en el repositorio.
- **API de Claude:** `ANTHROPIC_API_KEY` solo en el servidor; el cliente nunca la ve.

---

## 5. Checklist de "Definición de Listo para Producción"

- [ ] HTTPS obligatorio y certificado válido
- [ ] `.env` fuera del control de versiones y con permisos restringidos
- [ ] Usuario de BD con privilegios mínimos
- [ ] CSRF + sesión segura implementados
- [ ] Backups automáticos verificados (restauración probada)
- [ ] Logs y monitoreo activos con alertas
- [ ] Pipeline CI en verde (lint + análisis + pruebas)
- [ ] Plan de *rollback* documentado
- [ ] Datos demo eliminados; usuarios reales creados con contraseñas robustas

---

## 6. Métricas de éxito sugeridas

| Métrica | Objetivo |
|---------|----------|
| Disponibilidad (uptime) | ≥ 99.5 % |
| Tiempo de respuesta (p95) | < 500 ms |
| Cobertura de pruebas (núcleo) | ≥ 60 % |
| Tiempo de restauración de backup | < 30 min |
| Vulnerabilidades críticas abiertas | 0 |
