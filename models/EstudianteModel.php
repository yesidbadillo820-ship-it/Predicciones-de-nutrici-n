<?php
// models/EstudianteModel.php
// RF01 — Gestión de estudiantes con cálculo automático de IMC según tablas OMS

class EstudianteModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // RF01: Calcula IMC y clasifica según tablas OMS para niños
    public static function calcularIMC($peso_kg, $talla_cm, $edad_anios) {
        if (!$peso_kg || !$talla_cm) return null;
        $talla_m = $talla_cm / 100;
        $imc = round($peso_kg / ($talla_m * $talla_m), 2);

        // Clasificación simplificada OMS para niños 5-12 años
        if ($edad_anios < 5) {
            $clasificacion = $imc < 14 ? 'Bajo peso' : ($imc < 18 ? 'Normal' : ($imc < 22 ? 'Sobrepeso' : 'Obesidad'));
        } elseif ($edad_anios <= 12) {
            $clasificacion = $imc < 14.5 ? 'Bajo peso' : ($imc < 19 ? 'Normal' : ($imc < 23 ? 'Sobrepeso' : 'Obesidad'));
        } else {
            $clasificacion = $imc < 18.5 ? 'Bajo peso' : ($imc < 25 ? 'Normal' : ($imc < 30 ? 'Sobrepeso' : 'Obesidad'));
        }

        return ['imc' => $imc, 'clasificacion' => $clasificacion];
    }

    // Calcular edad en años desde fecha de nacimiento
    public static function calcularEdad($fecha_nac) {
        return (int)date_diff(date_create($fecha_nac), date_create('today'))->y;
    }

    public function obtenerTodos($buscar = '', $filtro_riesgo = '') {
        $where = "WHERE e.activo = 1";
        if ($buscar) {
            $b = mysqli_real_escape_string($this->conn, $buscar);
            $where .= " AND (e.nombre LIKE '%$b%' OR e.apellido LIKE '%$b%')";
        }
        if ($filtro_riesgo) {
            $fr = mysqli_real_escape_string($this->conn, $filtro_riesgo);
            $where .= " AND e.nivel_riesgo = '$fr'";
        }
        return $this->conn->query("
            SELECT e.*, g.nombre AS grado,
                   TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad
            FROM estudiantes e
            JOIN grados g ON e.id_grado = g.id
            $where
            ORDER BY FIELD(e.nivel_riesgo,'alto','medio','bajo','sin_riesgo'), e.nombre
        ");
    }

    public function obtenerPorId($id) {
        $id = (int)$id;
        return $this->conn->query("
            SELECT e.*, g.nombre AS grado,
                   TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad
            FROM estudiantes e
            JOIN grados g ON e.id_grado = g.id
            WHERE e.id = $id AND e.activo = 1
        ")->fetch_assoc();
    }

    public function contarTotal() {
        return $this->conn->query("SELECT COUNT(*) AS t FROM estudiantes WHERE activo=1")->fetch_assoc()['t'];
    }

    public function contarEnRiesgo() {
        return $this->conn->query("SELECT COUNT(*) AS t FROM estudiantes WHERE nivel_riesgo IN ('alto','medio') AND activo=1")->fetch_assoc()['t'];
    }

    public function crear($datos) {
        // RF01: Calcular y guardar IMC automáticamente
        $edad = self::calcularEdad($datos['fecha_nac']);
        $imc_data = self::calcularIMC($datos['peso_kg'], $datos['talla_cm'], $edad);
        $imc = $imc_data ? $imc_data['imc'] : null;
        $imc_clasificacion = $imc_data ? $imc_data['clasificacion'] : null;

        $stmt = $this->conn->prepare("
            INSERT INTO estudiantes(nombre, apellido, fecha_nac, genero, id_grado, peso_kg, talla_cm, imc, imc_clasificacion)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssssiddds',
            $datos['nombre'], $datos['apellido'], $datos['fecha_nac'],
            $datos['genero'], $datos['id_grado'], $datos['peso_kg'],
            $datos['talla_cm'], $imc, $imc_clasificacion
        );
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $edad = self::calcularEdad($datos['fecha_nac']);
        $imc_data = self::calcularIMC($datos['peso_kg'], $datos['talla_cm'], $edad);
        $imc = $imc_data ? $imc_data['imc'] : null;
        $imc_clasificacion = $imc_data ? $imc_data['clasificacion'] : null;

        $stmt = $this->conn->prepare("
            UPDATE estudiantes
            SET nombre=?, apellido=?, fecha_nac=?, genero=?, id_grado=?,
                peso_kg=?, talla_cm=?, imc=?, imc_clasificacion=?, nivel_riesgo=?
            WHERE id=?
        ");
        $stmt->bind_param('ssssidddssi',
            $datos['nombre'], $datos['apellido'], $datos['fecha_nac'],
            $datos['genero'], $datos['id_grado'], $datos['peso_kg'],
            $datos['talla_cm'], $imc, $imc_clasificacion,
            $datos['nivel_riesgo'], $id
        );
        return $stmt->execute();
    }

    public function eliminar($id) {
        return $this->conn->query("UPDATE estudiantes SET activo=0 WHERE id=".(int)$id);
    }

    public function actualizarRiesgo($id, $nivel, $score = null) {
        $id = (int)$id;
        $nivel = mysqli_real_escape_string($this->conn, $nivel);
        if ($score !== null) {
            $score = (int)$score;
            return $this->conn->query("UPDATE estudiantes SET nivel_riesgo='$nivel', score=$score WHERE id=$id");
        }
        return $this->conn->query("UPDATE estudiantes SET nivel_riesgo='$nivel' WHERE id=$id");
    }

    public function obtenerGrados() {
        return $this->conn->query("SELECT * FROM grados ORDER BY nivel, nombre");
    }

    public function obtenerTodosIds() {
        $result = $this->conn->query("SELECT id FROM estudiantes WHERE activo=1");
        $ids = [];
        while ($r = $result->fetch_assoc()) $ids[] = $r['id'];
        return $ids;
    }
}
