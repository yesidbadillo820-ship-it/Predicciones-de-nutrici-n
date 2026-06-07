@echo off
REM stop.bat - Detiene NutriPredict (conserva la base de datos).
cd /d "%~dp0"
docker compose down
echo NutriPredict detenido. Los datos se conservan.
echo Para borrar tambien la base de datos: docker compose down -v
pause
