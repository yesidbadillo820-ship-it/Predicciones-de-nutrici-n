<?php
// health.php — Endpoint de salud para monitoreo/uptime (sin autenticación).
// Devuelve 200 si la app y la BD responden; 503 si la BD no está disponible.
// No expone datos sensibles.
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$health = [
    'status' => 'ok',
    'time'   => date('c'),
    'env'    => APP_ENV,
];

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $c = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $c->query('SELECT 1');
    $c->close();
    $health['db'] = 'up';
} catch (Throwable $e) {
    http_response_code(503);
    $health['status'] = 'degraded';
    $health['db'] = 'down';
}

echo json_encode($health);
