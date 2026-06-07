# NutriPredict Escolar 🥗

Sistema de gestión y predicción nutricional para instituciones educativas colombianas.

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
nutripredict_final/
├── index.php              — Redirección al dashboard
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
├── nutribot.php           ← NUEVO: Endpoint del asistente virtual
├── ayuda.php              ← NUEVO: Centro de ayuda
├── sin_acceso.php         — Página de acceso denegado
│
├── includes/
│   ├── auth.php           — Autenticación y sesiones
│   ├── db.php             — Conexión a la base de datos
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
