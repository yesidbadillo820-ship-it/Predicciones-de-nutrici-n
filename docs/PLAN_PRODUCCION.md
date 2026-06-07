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
- [x] Pipeline CI (GitHub Actions): lint + integración con MySQL real en cada PR

### Fase 3 — Infraestructura y despliegue (1 semana)
- [x] Dockerizar (PHP+Apache + MySQL) con `docker-compose` e init automático de BD
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

---

## 7. Evolución hacia microservicios (visión a futuro)

> Esta sección relaciona los conceptos de arquitectura de microservicios con
> NutriPredict. **Recomendación honesta:** hoy la aplicación es pequeña y un
> **monolito modular bien organizado** (lo que acabamos de dejar) es la opción
> correcta. Migrar a microservicios solo se justifica si aparecen necesidades
> reales de *alta demanda*, *alta disponibilidad* o equipos independientes.
> No conviene microservicios "porque sí": añaden complejidad operativa.

### 7.1 Dónde estamos: monolito de 3 capas
NutriPredict es un **monolito** con capa de datos (MySQL), capa de lógica
(PHP/MVP) y capa de presentación (vistas PHP + JS). Un solo servidor, una sola
base de datos, un solo despliegue.

| Problema del monolito | ¿Aplica hoy? |
|-----------------------|--------------|
| Escalar es caro (se escala todo junto) | Bajo (volumen escolar pequeño) |
| Un cambio obliga a redesplegar todo | Medio |
| Más difícil de escalar en la nube | Bajo |

### 7.2 Paso intermedio recomendado (alto valor, bajo riesgo)
Antes de pensar en microservicios, capturar el 80 % del beneficio con prácticas
que **no** rompen el monolito:
- **Contenerización** (Docker): empaquetar app + dependencias → despliegues reproducibles.
- **DevOps + CI/CD**: integración y despliegue continuos (build → test → release → deploy) para derribar el "muro de la confusión" entre desarrollo y operaciones.
- **Mantener fronteras de dominio claras** dentro del código (ya las hay: estudiantes, menús, alimentos, asistencia, alertas, predictivo, reportes, usuarios), para que una futura separación sea natural.

### 7.3 Descomposición futura por dominio (si se necesita)
Si el sistema creciera (p. ej. una red de varias instituciones), los módulos
actuales mapean casi 1:1 a microservicios candidatos:

| Microservicio | Responsabilidad única | Notas |
|---------------|----------------------|-------|
| `auth` | Login, sesiones, roles | Punto de autenticación central |
| `estudiantes` | Gestión de estudiantes e IMC | |
| `menus` + `alimentos` | Menús y catálogo nutricional | Podrían ir juntos al inicio |
| `asistencia` | Registro de asistencia | Alta frecuencia de escritura |
| `alertas` | Detección y gestión de alertas | |
| `predictivo` | Cálculo de scores de riesgo | Candidato a **serverless** (corre por lote/diario) |
| `nutribot` | Asistente IA (Claude) | Ya es un *endpoint* aislado; candidato natural |
| `reportes` | Estadísticas y agregados | Lecturas intensivas |

Cada microservicio debería **cumplir una sola función, ser autónomo y estar
aislado** (con su propia lógica y, idealmente, su propio almacenamiento), y
comunicarse por mecanismos ligeros (HTTP/REST).

### 7.4 Componentes de plataforma
- **API Gateway** (AWS API Gateway, Google Cloud Endpoints o Azure API Management): expone **un único endpoint** al exterior, enruta a cada servicio y centraliza autenticación, *rate limiting* y seguridad.
- **Serverless** (AWS Lambda / Cloud Functions) para cargas intermitentes como `predictivo` y `nutribot`: el proveedor asigna recursos solo durante la ejecución → se paga por uso.
- **Contenedores + orquestación** (Docker + Kubernetes) cuando haya varios servicios que escalar y desplegar de forma independiente.
- **Patrón interno por servicio** (Controller/Endpoint → Validator → Lógica de negocio → Acceso a datos), coherente con el MVP que ya usa el proyecto.

### 7.5 Hoja de ruta de arquitectura
1. **Ahora:** monolito modular + Docker + CI/CD (Fases 1–3 de este plan).
2. **Si crece la demanda:** extraer primero `nutribot` y `predictivo` (ya casi aislados) como servicios/funciones serverless detrás de un API Gateway.
3. **A escala (multi-institución):** descomponer por dominio según 7.3, con orquestación Kubernetes y bases de datos por servicio.

> ⚖️ **Criterio de decisión:** migrar un módulo a microservicio solo cuando su
> escalado, su ritmo de cambio o su equipo lo justifiquen. Hasta entonces, el
> monolito modular es más barato de operar y más fácil de mantener.
