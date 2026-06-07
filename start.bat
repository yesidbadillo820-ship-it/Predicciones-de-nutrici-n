@echo off
REM start.bat - Arranque de un clic para Windows.
REM Levanta NutriPredict (app + base de datos) con Docker y abre el navegador.
cd /d "%~dp0"

where docker >nul 2>nul
if errorlevel 1 (
    echo Docker no esta instalado.
    echo Instalalo desde: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo Levantando NutriPredict (la primera vez puede tardar unos minutos)...
docker compose up --build -d
if errorlevel 1 (
    echo Hubo un error al iniciar. Asegurate de que Docker Desktop este abierto.
    pause
    exit /b 1
)

echo Esperando a que la aplicacion este lista...
timeout /t 10 /nobreak >nul

echo.
echo Listo. Abriendo http://localhost:8080
echo Usuario: admin@nutripredict.edu.co   Contrasena: demo123
echo Para detener: ejecuta stop.bat
start "" http://localhost:8080
pause
