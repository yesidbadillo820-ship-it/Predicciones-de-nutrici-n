<?php
// models/ReporteModel.php
class ReporteModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function coberturaSemanal() {
        $hoy = date('Y-m-d');
        return $this->conn->query("
            SELECT nutriente, ROUND(AVG(porcentaje),1) AS prom
            FROM cobertura_nutricional
            WHERE fecha >= DATE_SUB('$hoy', INTERVAL 7 DAY)
            GROUP BY nutriente
            ORDER BY FIELD(nutriente,'Hierro','Calcio','Proteinas','Vitamina D','Zinc')
        ");
    }

    public function coberturaPeriodo($fi, $ff) {
        $fi = mysqli_real_escape_string($this->conn, $fi);
        $ff = mysqli_real_escape_string($this->conn, $ff);
        return $this->conn->query("
            SELECT nutriente, ROUND(AVG(porcentaje),1) AS prom
            FROM cobertura_nutricional
            WHERE fecha BETWEEN '$fi' AND '$ff'
            GROUP BY nutriente
        ");
    }

    public function riesgoPorNivel() {
        return $this->conn->query("
            SELECT nivel_riesgo, COUNT(*) AS total
            FROM estudiantes WHERE activo=1
            GROUP BY nivel_riesgo
        ");
    }

    public function alertasPorDeficiencia($fi, $ff) {
        $fi = mysqli_real_escape_string($this->conn, $fi);
        $ff = mysqli_real_escape_string($this->conn, $ff);
        return $this->conn->query("
            SELECT tipo_deficiencia, COUNT(*) AS total
            FROM alertas
            WHERE fecha_creacion BETWEEN '$fi' AND '$ff 23:59:59'
            GROUP BY tipo_deficiencia
            ORDER BY total DESC
        ");
    }

    public function historialAsistencia($fi, $ff) {
        $fi = mysqli_real_escape_string($this->conn, $fi);
        $ff = mysqli_real_escape_string($this->conn, $ff);
        return $this->conn->query("
            SELECT fecha, SUM(asistio=1) AS presentes, SUM(asistio=0) AS ausentes
            FROM asistencia
            WHERE fecha BETWEEN '$fi' AND '$ff'
            GROUP BY fecha ORDER BY fecha DESC LIMIT 14
        ");
    }

    public function tendenciaSemanal() {
        $hoy = date('Y-m-d');
        return $this->conn->query("
            SELECT DATE_FORMAT(fecha,'%d/%m') AS dia,
                   SUM(nivel_riesgo='alto')  AS alto,
                   SUM(nivel_riesgo='medio') AS medio,
                   SUM(nivel_riesgo='bajo')  AS bajo
            FROM riesgo_diario
            WHERE fecha >= DATE_SUB('$hoy', INTERVAL 6 DAY)
            GROUP BY fecha ORDER BY fecha
        ");
    }
}
