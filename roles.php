<?php
// includes/roles.php
// Control de acceso por rol según diagrama UML

// Permisos por rol
$PERMISOS = [
    'admin' => [
        'dashboard', 'estudiantes', 'menus', 'alimentos', 'asistencia',
        'alertas', 'reportes', 'predictivo', 'usuarios', 'configuracion'
    ],
    'encargado_restaurante' => [
        'dashboard', 'menus', 'alimentos', 'asistencia', 'alertas'
    ],
    'docente' => [
        'dashboard', 'estudiantes_ver', 'alertas_ver', 'reportes_ver'
    ],
    'directora' => [
        'dashboard', 'reportes', 'predictivo_ver', 'alertas_ver',
        'estudiantes_ver', 'menus_ver'
    ]
];

function tienePermiso($modulo) {
    global $PERMISOS;
    $rol = $_SESSION['usuario']['rol'] ?? '';
    return in_array($modulo, $PERMISOS[$rol] ?? []);
}

function requirePermiso($modulo) {
    if (!tienePermiso($modulo)) {
        header('Location: sin_acceso.php');
        exit;
    }
}

function getRol() {
    return $_SESSION['usuario']['rol'] ?? '';
}

function esAdmin()       { return getRol() === 'admin'; }
function esEncargado()   { return getRol() === 'encargado_restaurante'; }
function esDocente()     { return getRol() === 'docente'; }
function esDirectora()   { return getRol() === 'directora'; }
