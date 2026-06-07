<?php
/**
 * config/config.php — Configuración central de NutriPredict Escolar
 *
 * Lee la configuración desde variables de entorno (recomendado en producción)
 * y, opcionalmente, desde un archivo `.env` en la raíz del proyecto (útil en
 * desarrollo local). NUNCA se deben subir credenciales reales al repositorio:
 * usa `.env` (ignorado por git) o variables de entorno del servidor.
 */

// ── Cargador mínimo de archivo .env (solo si existe) ─────────────────
(function () {
    $envFile = dirname(__DIR__) . '/.env';
    if (!is_readable($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Quitar comillas envolventes si las hay
        $value = trim($value, "\"'");
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
})();

/**
 * Helper para leer una variable de entorno con valor por defecto.
 */
function env(string $key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    return ($value === false || $value === null) ? $default : $value;
}

// ── Entorno de ejecución ─────────────────────────────────────────────
// 'production' | 'development'
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));

// ── Base de datos ────────────────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'nutripredict_db'));
define('DB_PORT', (int) env('DB_PORT', 3306));

// ── Integraciones externas ───────────────────────────────────────────
define('ANTHROPIC_API_KEY', env('ANTHROPIC_API_KEY', ''));
define('ANTHROPIC_MODEL', env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'));

// ── Manejo de errores según el entorno ───────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Logger estructurado de la aplicación
require_once dirname(__DIR__) . '/includes/logger.php';
