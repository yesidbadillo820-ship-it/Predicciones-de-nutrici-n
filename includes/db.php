<?php
// includes/db.php — Conexión a la base de datos (configurable por entorno)
require_once dirname(__DIR__) . '/config/config.php';

// Lanzar excepciones de mysqli en lugar de warnings silenciosos
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    error_log('[NutriPredict] DB connection error: ' . $e->getMessage());
    $detalle = APP_DEBUG ? $e->getMessage() : 'Servicio no disponible temporalmente.';
    // Responder en JSON si es una petición de API, en texto si no
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión a la base de datos', 'detalle' => $detalle]);
    } else {
        echo 'Error de conexión a la base de datos. ' . htmlspecialchars($detalle);
    }
    exit;
}
