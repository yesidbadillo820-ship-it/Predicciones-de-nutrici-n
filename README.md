# NutriPredict Escolar

Plataforma para la gestión y la **predicción de deficiencias nutricionales** en
estudiantes de básica primaria, construida con una **arquitectura de
microservicios** y un **asistente virtual** integrado.

---

## Cómo ejecutar (sin instalar nada)

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/yesidbadillo820-ship-it/Predicciones-de-nutrici-n)

1. Abrir el repositorio en GitHub → **Code → Codespaces → Create codespace on main**
   (o el botón de arriba).
2. Esperar 1–2 minutos: el entorno se prepara y levanta la plataforma.
3. En la pestaña **Ports / Puertos**, abrir el puerto **8090**.
4. Iniciar sesión: **admin@nutripredict.edu.co** · **demo123**.

Todo se ejecuta en el navegador; no se instala Docker, PHP ni MySQL en el equipo.

### Ejecución local (alternativa, requiere Docker)
```bash
cd microservices
docker compose up --build      # interfaz en http://localhost:8090
```

---

## Arquitectura de microservicios

Un **API Gateway** es el único punto de entrada: autentica con **JWT** y enruta
hacia nueve servicios independientes, cada uno con su propia base de datos.

```
Cliente ─▶ API Gateway (JWT, enrutamiento)
             ├─ auth          ├─ asistencia     ├─ reportes
             ├─ estudiantes   ├─ alertas        └─ nutribot
             ├─ alimentos     ├─ predictivo
             └─ menus
```

- **Comunicación entre servicios por REST:** el de menús consulta al de alimentos;
  el predictivo orquesta a estudiantes, alertas, asistencia y menús; reportes y
  nutribot agregan información de varios servicios.
- **Cada servicio** sigue el patrón *Controlador → Validador → Lógica → Datos*.

Documentación detallada y diagrama: [`microservices/README.md`](microservices/README.md).

---

## Asistente virtual (NutriBot)

Asistente conversacional disponible en la interfaz. Toma el contexto en tiempo
real de los microservicios (estudiantes en riesgo, alertas activas) y responde
consultas y recomendaciones nutricionales. Si se configura una clave de IA usa
ese motor; de lo contrario responde con datos reales del sistema.

---

## Motor de predicción

Calcula un puntaje de riesgo (0–100) por estudiante combinando:

| Factor | Peso |
|--------|------|
| Alertas nutricionales activas | hasta 40 |
| Inasistencias recientes | hasta 20 |
| Cobertura nutricional del menú | hasta 30 |

Niveles: Sin riesgo · Bajo · Medio · Alto.

---

## Tecnologías

- **Backend:** PHP 8.4 (REST), MySQL 8 (una base por servicio)
- **Gateway / Auth:** enrutamiento + JSON Web Tokens
- **Infraestructura:** Docker y Docker Compose
- **Frontend:** interfaz web servida por el gateway

---

## Credenciales de prueba

| Rol | Correo | Contraseña |
|-----|--------|-----------|
| Administrador | admin@nutripredict.edu.co | demo123 |
| Encargado | restaurante@nutripredict.edu.co | demo123 |
| Docente | docente@nutripredict.edu.co | demo123 |
| Directora | directora@nutripredict.edu.co | demo123 |

---

> El repositorio incluye además una versión **monolítica** (carpeta raíz) del mismo
> sistema, con interfaz web tradicional. Guía de instalación: [`docs/INSTALACION.md`](docs/INSTALACION.md).
