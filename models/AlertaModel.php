<?php
// models/AlertaModel.php
// RF06 — Agente inteligente: detecta deficiencias y sugiere alimentos correctivos

class AlertaModel {
    private $conn;

    // RF06: Alimentos sugeridos por nutriente deficiente (según catálogo ICBF/USDA)
    const SUGERENCIAS = [
        'Hierro'     => ['Lentejas cocidas', 'Espinaca cocida', 'Hígado de res', 'Frijoles rojos'],
        'Calcio'     => ['Leche entera', 'Yogur natural', 'Queso', 'Sardinas en lata'],
        'Proteinas'  => ['Huevo cocido', 'Pollo pechuga', 'Atún en agua', 'Frijoles negros'],
        'Vitamina D' => ['Leche fortificada', 'Huevo (yema)', 'Salmón', 'Sardinas'],
        'Zinc'       => ['Carne de res', 'Pollo', 'Frijoles', 'Maní'],
        'calorias'   => ['Arroz', 'Plátano maduro', 'Arepa de maíz', 'Papa cocida'],
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // RF06: Generar alertas automáticas al registrar un menú
    public function generarAlertasMenu($id_menu, $cobertura, $id_usuario) {
        foreach ($cobertura as $item) {
            if ($item['pct'] < 70) {  // RFC: < 70% del recomendado ICBF
                $nut  = $item['nombre'];
                $pct  = $item['pct'];
                $sugs = self::SUGERENCIAS[$nut] ?? [];
                $sugerencia = count($sugs) > 0
                    ? "Sugerencia: agregar " . implode(', ', array_slice($sugs, 0, 2)) . " al menú."
                    : "Revisar la composición nutricional del menú.";
                $desc = "El menú cubre solo el {$pct}% del {$nut} recomendado por el ICBF. {$sugerencia}";
                $nivel = $pct < 40 ? 'alta' : 'media';

                // Solo crear si no existe alerta activa igual para este menú
                $existe = $this->conn->query("
                    SELECT id FROM alertas
                    WHERE tipo_deficiencia = '$nut'
                    AND estado = 'activa'
                    AND DATE(fecha_creacion) = CURDATE()
                ")->num_rows;

                if (!$existe) {
                    $stmt = $this->conn->prepare("
                        INSERT INTO alertas(id_estudiante, tipo_deficiencia, descripcion, nivel)
                        VALUES(0, ?, ?, ?)
                    ");
                    $stmt->bind_param('sss', $nut, $desc, $nivel);
                    $stmt->execute();
                }
            }
        }
    }

    public function obtenerActivas($limite = 5) {
        return $this->conn->query("
            SELECT a.*,
                   COALESCE(e.nombre, 'Sistema') AS nombre,
                   COALESCE(e.apellido, '') AS apellido,
                   COALESCE(g.nombre, 'General') AS grado
            FROM alertas a
            LEFT JOIN estudiantes e ON a.id_estudiante = e.id AND a.id_estudiante > 0
            LEFT JOIN grados g ON e.id_grado = g.id
            WHERE a.estado = 'activa'
            ORDER BY a.fecha_creacion DESC
            LIMIT $limite
        ");
    }

    public function obtenerPorEstado($estado) {
        $estado = mysqli_real_escape_string($this->conn, $estado);
        return $this->conn->query("
            SELECT a.*,
                   COALESCE(e.nombre, 'Sistema') AS nombre,
                   COALESCE(e.apellido, '') AS apellido,
                   COALESCE(g.nombre, 'General') AS grado
            FROM alertas a
            LEFT JOIN estudiantes e ON a.id_estudiante = e.id AND a.id_estudiante > 0
            LEFT JOIN grados g ON e.id_grado = g.id
            WHERE a.estado = '$estado'
            ORDER BY a.fecha_creacion DESC
        ");
    }

    public function contarPorEstado($estado) {
        $estado = mysqli_real_escape_string($this->conn, $estado);
        return $this->conn->query("SELECT COUNT(*) AS t FROM alertas WHERE estado='$estado'")->fetch_assoc()['t'];
    }

    public function contarPorEstadoEstudiante($id_est, $estado) {
        $id_est = (int)$id_est;
        $estado = mysqli_real_escape_string($this->conn, $estado);
        return $this->conn->query("SELECT COUNT(*) AS t FROM alertas WHERE id_estudiante=$id_est AND estado='$estado'")->fetch_assoc()['t'];
    }

    public function crear($id_estudiante, $tipo, $descripcion, $nivel = 'media') {
        $stmt = $this->conn->prepare("INSERT INTO alertas(id_estudiante,tipo_deficiencia,descripcion,nivel) VALUES(?,?,?,?)");
        $stmt->bind_param('isss', $id_estudiante, $tipo, $descripcion, $nivel);
        return $stmt->execute();
    }

    public function resolver($id) {
        return $this->conn->query("UPDATE alertas SET estado='resuelta',fecha_resolucion=NOW() WHERE id=".(int)$id);
    }

    public function ignorar($id) {
        return $this->conn->query("UPDATE alertas SET estado='ignorada' WHERE id=".(int)$id);
    }

    public function existeActivaPorEstudiante($id_est, $tipo) {
        $id = (int)$id_est;
        $tipo = mysqli_real_escape_string($this->conn, $tipo);
        return $this->conn->query("SELECT id FROM alertas WHERE id_estudiante=$id AND estado='activa' AND tipo_deficiencia='$tipo'")->num_rows > 0;
    }

    public static function getSugerencias($tipo) {
        return self::SUGERENCIAS[$tipo] ?? [];
    }
}
