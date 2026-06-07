<?php
// Repository + lógica de dominio del microservicio Estudiantes.
declare(strict_types=1);

class EstudianteRepository {
    public function __construct(private mysqli $db) {}

    public static function calcularIMC(?float $peso, ?float $talla, int $edad): ?array {
        if (!$peso || !$talla) return null;
        $m = $talla / 100;
        $imc = round($peso / ($m * $m), 2);
        if ($edad <= 12) {
            $clas = $imc < 14.5 ? 'Bajo peso' : ($imc < 19 ? 'Normal' : ($imc < 23 ? 'Sobrepeso' : 'Obesidad'));
        } else {
            $clas = $imc < 18.5 ? 'Bajo peso' : ($imc < 25 ? 'Normal' : ($imc < 30 ? 'Sobrepeso' : 'Obesidad'));
        }
        return ['imc' => $imc, 'clasificacion' => $clas];
    }

    public static function calcularEdad(string $fechaNac): int {
        return (int) date_diff(date_create($fechaNac), date_create('today'))->y;
    }

    public function listar(string $buscar = '', string $riesgo = ''): array {
        $where = "WHERE e.activo = 1";
        $types = ''; $params = [];
        if ($buscar !== '') { $where .= " AND (e.nombre LIKE ? OR e.apellido LIKE ?)"; $b = "%$buscar%"; $types .= 'ss'; $params[] = $b; $params[] = $b; }
        if ($riesgo !== '') { $where .= " AND e.nivel_riesgo = ?"; $types .= 's'; $params[] = $riesgo; }
        $sql = "SELECT e.*, g.nombre AS grado, TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad
                FROM estudiantes e JOIN grados g ON e.id_grado = g.id $where
                ORDER BY FIELD(e.nivel_riesgo,'alto','medio','bajo','sin_riesgo'), e.nombre";
        $st = $this->db->prepare($sql);
        if ($types !== '') $st->bind_param($types, ...$params);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function porId(int $id): ?array {
        $st = $this->db->prepare("SELECT e.*, g.nombre AS grado, TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad
            FROM estudiantes e JOIN grados g ON e.id_grado = g.id WHERE e.id = ? AND e.activo = 1");
        $st->bind_param('i', $id);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    public function crear(array $d): int {
        $edad = self::calcularEdad($d['fecha_nac']);
        $imc = self::calcularIMC($d['peso_kg'], $d['talla_cm'], $edad);
        $iv = $imc['imc'] ?? null; $ic = $imc['clasificacion'] ?? null;
        $st = $this->db->prepare("INSERT INTO estudiantes(nombre,apellido,fecha_nac,genero,id_grado,peso_kg,talla_cm,imc,imc_clasificacion)
            VALUES(?,?,?,?,?,?,?,?,?)");
        $st->bind_param('ssssiddds', $d['nombre'], $d['apellido'], $d['fecha_nac'], $d['genero'], $d['id_grado'], $d['peso_kg'], $d['talla_cm'], $iv, $ic);
        $st->execute();
        return (int) $this->db->insert_id;
    }

    public function actualizar(int $id, array $d): bool {
        $edad = self::calcularEdad($d['fecha_nac']);
        $imc = self::calcularIMC($d['peso_kg'], $d['talla_cm'], $edad);
        $iv = $imc['imc'] ?? null; $ic = $imc['clasificacion'] ?? null;
        $st = $this->db->prepare("UPDATE estudiantes SET nombre=?,apellido=?,fecha_nac=?,genero=?,id_grado=?,peso_kg=?,talla_cm=?,imc=?,imc_clasificacion=?,nivel_riesgo=? WHERE id=?");
        $st->bind_param('ssssidddssi', $d['nombre'], $d['apellido'], $d['fecha_nac'], $d['genero'], $d['id_grado'], $d['peso_kg'], $d['talla_cm'], $iv, $ic, $d['nivel_riesgo'], $id);
        return $st->execute();
    }

    public function eliminar(int $id): bool {
        return $this->db->query("UPDATE estudiantes SET activo=0 WHERE id=" . $id);
    }

    public function grados(): array {
        return $this->db->query("SELECT * FROM grados ORDER BY nivel, nombre")->fetch_all(MYSQLI_ASSOC);
    }
}
