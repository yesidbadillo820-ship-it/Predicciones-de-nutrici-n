<?php
// Reportes Service — agregador puro (no tiene base de datos propia).
// Demuestra un microservicio que solo orquesta/combina otros servicios.
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'reportes']);

if ($method !== 'GET') json_out(['error' => 'Método no permitido'], 405);

if ($path === '/reportes/riesgo') {
    json_out(['data' => svc_get('predictivo', '/predictivo/conteos')['data'] ?? []]);
}
if ($path === '/reportes/cobertura') {
    json_out(['data' => svc_get('menus', '/menus/cobertura?dias=7')['data'] ?? []]);
}
if ($path === '/reportes/resumen') {
    json_out([
        'riesgo'    => svc_get('predictivo', '/predictivo/conteos')['data'] ?? [],
        'alertas'   => svc_get('alertas', '/alertas/conteos')['data'] ?? [],
        'cobertura' => svc_get('menus', '/menus/cobertura?dias=7')['data'] ?? [],
        'generado'  => date('c'),
    ]);
}
json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
