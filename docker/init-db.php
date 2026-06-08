<?php
// docker/init-db.php — Inicializa la base de datos al arrancar (idempotente).
// Si la tabla 'usuarios' no existe, carga schema.sql y seed.sql contra la BD
// ya seleccionada por el host (ignora CREATE DATABASE/USE).
mysqli_report(MYSQLI_REPORT_OFF);
require __DIR__ . '/../config/config.php';

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if (!$conn || $conn->connect_errno) {
    fwrite(STDERR, "init-db: la base de datos aún no responde (" . ($conn->connect_error ?? '') . ")\n");
    exit(0); // no bloquear el arranque
}
$conn->set_charset('utf8mb4');

if (@$conn->query("SELECT 1 FROM usuarios LIMIT 1")) {
    echo "init-db: la base de datos ya estaba inicializada.\n";
    exit(0);
}

function run_sql(mysqli $c, string $file): void {
    if (!is_file($file)) return;
    $sql = (string) file_get_contents($file);
    $sql = preg_replace('/CREATE DATABASE[\s\S]*?;/i', '', $sql); // sentencia completa (multilínea)
    $sql = preg_replace('/^\s*USE\s+[^;]+;/mi', '', $sql);
    if ($c->multi_query($sql)) {
        do { /* drenar resultados */ } while ($c->more_results() && $c->next_result());
    }
    if ($c->error) fwrite(STDERR, "init-db: aviso: " . $c->error . "\n");
}

run_sql($conn, __DIR__ . '/../database/schema.sql');
run_sql($conn, __DIR__ . '/../database/seed.sql');
echo "init-db: esquema y datos demo cargados.\n";
