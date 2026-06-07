<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'predictivo']);

$repo = new RiesgoRepository(db());
$hoy = date('Y-m-d');

if ($path === '/predictivo/conteos' && $method === 'GET') json_out(['data' => $repo->conteos(trim((string) ($_GET['fecha'] ?? $hoy)))]);
if ($path === '/predictivo' && $method === 'GET') json_out(['data' => $repo->listar(trim((string) ($_GET['fecha'] ?? $hoy)))]);

// POST /predictivo/ejecutar — orquesta a otros microservicios y recalcula el riesgo
if ($path === '/predictivo/ejecutar' && $method === 'POST') {
    $est = svc_get('estudiantes', '/estudiantes');
    if ($est === null) json_out(['error' => 'Servicio Estudiantes no disponible'], 503);

    // Cobertura nutricional global (promedio de los nutrientes monitoreados)
    $cobResp = svc_get('menus', '/menus/cobertura?dias=7');
    $proms = array_column($cobResp['data'] ?? [], 'prom');
    $cobertura = $proms ? array_sum($proms) / count($proms) : 75.0;

    $procesados = 0;
    foreach (($est['data'] ?? []) as $e) {
        $id = (int) $e['id'];
        $al = svc_get('alertas', "/alertas/estudiante/$id/count?estado=activa");
        $as = svc_get('asistencia', "/asistencia/estudiante/$id/inasistencias?dias=10");
        $r = RiesgoRepository::score((int) ($al['count'] ?? 0), (int) ($as['inasistencias'] ?? 0), (float) $cobertura);
        $repo->guardar($id, $hoy, $r['nivel'], $r['score']);
        $procesados++;
    }
    json_out(['ok' => true, 'procesados' => $procesados, 'cobertura_global' => round($cobertura, 1), 'conteos' => $repo->conteos($hoy)]);
}

json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
