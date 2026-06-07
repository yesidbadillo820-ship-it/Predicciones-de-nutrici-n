<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'asistencia']);

$repo = new AsistenciaRepository(db());
$hoy = date('Y-m-d');

if ($path === '/asistencia/resumen' && $method === 'GET') {
    json_out(['data' => $repo->resumen(trim((string) ($_GET['fecha'] ?? $hoy)))]);
}
// Endpoint usado por Predictivo (comunicación interna)
if (preg_match('#^/asistencia/estudiante/(\d+)/inasistencias$#', $path, $m) && $method === 'GET') {
    json_out(['inasistencias' => $repo->inasistencias((int) $m[1], (int) ($_GET['dias'] ?? 10))]);
}
if ($path === '/asistencia') {
    if ($method === 'GET') json_out(['data' => $repo->porFecha(trim((string) ($_GET['fecha'] ?? $hoy)))]);
    if ($method === 'POST') {
        $in = body_json();
        $fecha = trim((string) ($in['fecha'] ?? $hoy));
        $registros = $in['registros'] ?? [];
        if (!is_array($registros) || !$registros) json_out(['error' => 'registros (array) es obligatorio'], 422);
        json_out(['ok' => true, 'guardados' => $repo->guardar($fecha, $registros)]);
    }
}
json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
