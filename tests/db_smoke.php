<?php
/**
 * tests/db_smoke.php — Prueba de humo de la capa de datos.
 *
 * Verifica, contra una base de datos real ya inicializada con schema.sql +
 * seed.sql, que la conexión y las consultas clave funcionan. Se usa en CI.
 * Devuelve código de salida 0 si todo pasa, 1 si algo falla.
 */

require __DIR__ . '/../includes/db.php';            // $conn (lee config por entorno)
require __DIR__ . '/../models/UsuarioModel.php';
require __DIR__ . '/../models/EstudianteModel.php';

$fallos = 0;
function check(string $nombre, bool $ok): void {
    global $fallos;
    echo ($ok ? "  ✅ " : "  ❌ ") . $nombre . "\n";
    if (!$ok) $fallos++;
}

echo "== Prueba de humo de base de datos ==\n";

// 1. Login: el usuario admin existe y la contraseña demo funciona
$usuarioModel = new UsuarioModel($conn);
$admin = $usuarioModel->obtenerPorEmail('admin@nutripredict.edu.co');
check('admin existe', is_array($admin) && isset($admin['password']));
check('password_verify("demo123") del admin', $admin && password_verify('demo123', $admin['password']));
check('rol del admin = admin', ($admin['rol'] ?? '') === 'admin');

// 2. Estudiantes con JOIN a grados (valida claves foráneas y datos demo)
$estudianteModel = new EstudianteModel($conn);
$total = (int) $estudianteModel->contarTotal();
check('hay estudiantes activos (>0)', $total > 0);
$lista = $estudianteModel->obtenerTodos();
check('obtenerTodos() ejecuta el JOIN sin error', $lista !== false);

// 3. Tablas usadas por reportes/predicción existen y se pueden consultar
foreach (['alertas', 'asistencia', 'cobertura_nutricional', 'riesgo_diario', 'menus', 'alimentos'] as $tabla) {
    $res = $conn->query("SELECT COUNT(*) AS t FROM `$tabla`");
    check("consulta tabla '$tabla'", $res !== false);
}

echo $fallos === 0 ? "\n✅ Todas las pruebas de humo pasaron.\n" : "\n❌ $fallos prueba(s) fallida(s).\n";
exit($fallos === 0 ? 0 : 1);
