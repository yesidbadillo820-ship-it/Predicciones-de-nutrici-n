# 📊 Rendimiento — NutriPredict Escolar

Guía para medir el rendimiento y resultados de la última corrida.

## Cómo reproducir

```bash
# 1. Tener la app y la BD corriendo (Docker o local)
#    Local: php -S 127.0.0.1:8000  con la BD cargada (schema.sql + seed.sql)
# 2. Instalar Apache Bench:  apt-get install -y apache2-utils
# 3. Ejecutar:
./scripts/benchmark.sh http://127.0.0.1:8000 400 15
#                       └ URL                 └N    └concurrencia
```

El script inicia sesión (gestionando el token CSRF), reutiliza la cookie y mide
cada página con `ab`.

## Resultados de referencia

**Entorno de prueba:** servidor embebido de PHP 8.4 (un proceso) + MariaDB 10.11
en la misma máquina · 400 peticiones · concurrencia 15 · `APP_DEBUG=false`.

| Endpoint | Peticiones/seg | Latencia media | Fallidas |
|----------|---------------:|---------------:|:--------:|
| `index.php` (landing pública) | **4019** | 3.7 ms | 0 |
| `estudiantes.php` (auth) | **682** | 22 ms | 0 |
| `predictivo.php` (auth) | **415** | 36 ms | 0 |
| `dashboard.php` (auth) | **340** | 44 ms | 0 |

> El dashboard es la página más pesada porque agrega varias consultas
> (resumen, alertas, estudiantes en riesgo, menú del día, cobertura y
> tendencia). Aun así responde en ~44 ms bajo carga concurrente.

**Cron de predicción** (`bin/run_prediction.php`): recálculo de riesgo para
todos los estudiantes activos en **~27 ms**.

## Notas e interpretación

- **0 peticiones fallidas** en todas las pruebas → estabilidad bajo carga.
- Las cifras son una **cota inferior**: el servidor embebido de PHP procesa
  de forma prácticamente secuencial. En producción con **Apache/PHP-FPM o
  Nginx** (ver `Dockerfile`) el rendimiento concurrente es notablemente mayor.
- La base de datos ya incluye **índices** en las columnas más consultadas
  (`nivel_riesgo`, `estado`, `fecha`, claves foráneas) — ver `database/schema.sql`.

## Próximas optimizaciones sugeridas

1. **OPcache** habilitado en producción (cachea el bytecode PHP).
2. **Caché de consultas de dashboard** (p. ej. 30–60 s) para el resumen.
3. **Paginación** en listados grandes de estudiantes/alertas.
4. Servir **CSS/JS estáticos** con cabeceras de caché y compresión gzip/brotli.
