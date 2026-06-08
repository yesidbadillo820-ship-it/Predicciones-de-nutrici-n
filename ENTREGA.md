# Entrega — NutriPredict Escolar

**Repositorio:** https://github.com/yesidbadillo820-ship-it/Predicciones-de-nutrici-n

NutriPredict es una plataforma de **gestión y predicción nutricional escolar**
construida con **arquitectura de microservicios** (API Gateway + 9 servicios,
autenticación con JWT, una base de datos por servicio) e incluye un
**asistente virtual (NutriBot)**.

## Cómo abrirlo y probarlo (sin instalar nada)

1. Abrir el repositorio en GitHub.
2. Botón **Code → pestaña Codespaces → Create codespace on main**
   (o el botón **“Open in GitHub Codespaces”** del README).
3. Esperar 1–2 minutos a que el entorno construya y levante la plataforma.
4. En la pestaña **Ports / Puertos**, abrir el puerto **8090**.
5. Iniciar sesión:
   - Usuario: **admin@nutripredict.edu.co**
   - Contraseña: **demo123**

Todo se ejecuta en el navegador; no se instala Docker, PHP ni MySQL.

## Recorrido sugerido
1. **Panel general:** indicadores y gráficas (riesgo y cobertura).
2. **Alimentos / Menús del día:** registrar un alimento y un menú (calcula cobertura).
3. **Estudiantes:** registrar un estudiante (calcula el IMC).
4. **Asistencia:** marcar y guardar.
5. **Predictivo → Recalcular riesgo:** el motor orquesta varios microservicios.
6. **Microservicios:** estado en vivo de cada servicio.
7. **NutriBot** (botón inferior derecho): asistente virtual.

## Documentación
- Arquitectura de microservicios: `microservices/README.md`
- Instalación local / Docker / XAMPP: `docs/INSTALACION.md`
