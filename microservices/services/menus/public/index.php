<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if ($path === '/health') json_out(['status' => 'ok', 'service' => 'menus']);

$repo = new MenuRepository(db());
$hoy = date('Y-m-d');

if ($path === '/menus/cobertura' && $method === 'GET') {
    json_out(['data' => $repo->coberturaPromedio((int) ($_GET['dias'] ?? 7))]);
}
if ($path === '/menus') {
    if ($method === 'GET') json_out(['data' => $repo->porFecha(trim((string) ($_GET['fecha'] ?? $hoy)))]);
    if ($method === 'POST') {
        $in = body_json();
        $fecha = trim((string) ($in['fecha'] ?? $hoy));
        $tipo = in_array($in['tipo_tiempo'] ?? 'almuerzo', ['desayuno','almuerzo','merienda'], true) ? $in['tipo_tiempo'] : 'almuerzo';
        $desc = trim((string) ($in['descripcion'] ?? ''));
        $items = $in['items'] ?? [];
        if ($desc === '') json_out(['error' => 'descripcion es obligatoria'], 422);
        if (!is_array($items) || !$items) json_out(['error' => 'items (array) es obligatorio'], 422);

        // Comunicación entre microservicios: obtener el catálogo del servicio Alimentos
        $resp = svc_get('alimentos', '/alimentos');
        $alimentosById = [];
        foreach (($resp['data'] ?? []) as $a) $alimentosById[(int) $a['id']] = $a;
        if (!$alimentosById) json_out(['error' => 'No se pudo consultar el servicio de Alimentos'], 503);

        $r = $repo->guardar($fecha, $tipo, $desc, $items, $alimentosById);
        $deficiencias = array_values(array_filter($r['calculo']['cobertura'], fn($c) => !$c['ok']));
        json_out(['ok' => true, 'id_menu' => $r['id_menu'], 'cobertura' => $r['calculo']['cobertura'], 'deficiencias' => $deficiencias], 201);
    }
}
json_out(['error' => 'Ruta no encontrada', 'path' => $path], 404);
