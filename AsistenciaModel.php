<?php
// models/AsistenciaModel.php
class AsistenciaModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerPorFecha($fecha) {
        $fecha = mysqli_real_escape_string($this->conn, $fecha);
        return $this->conn->query("
            SELECT e.id, e.nombre, e.apellido, g.nombre AS grado,
                   a.asistio, a.observacion
            FROM estudiantes e
            JOIN grados g ON e.id_grado = g.id
            LEFT JOIN asistencia a ON a.id_estudiante = e.id AND a.fecha = '$fecha'
            WHERE e.activo = 1
            ORDER BY g.nivel, e.nombre
        ");
    }

    public function contarHoy() {
        $hoy = date('Y-m-d');
        return $this->conn->query("
            SELECT COUNT(*) AS t FROM asistencia WHERE fecha='$hoy' AND asistio=1
        ")->fetch_assoc()['t'];
    }

    public function resumenFecha($fecha) {
        $fecha = mysqli_real_escape_string($this->conn, $fecha);
        return $this->conn->query("
            SELECT SUM(asistio=1) AS presentes, SUM(asistio=0) AS ausentes, COUNT(*) AS total
            FROM asistencia WHERE fecha='$fecha'
        ")->fetch_assoc();
    }

    public function guardar($fecha, $ids_todos, $asistencias, $observaciones) {
        $fecha = mysqli_real_escape_string($this->conn, $fecha);
        $this->conn->query("DELETE FROM asistencia WHERE fecha='$fecha'");
        foreach ($ids_todos as $id) {
            $asistio = isset($asistencias[$id]) ? 1 : 0;
            $obs = mysqli_real_escape_string($this->conn, $observaciones[$id] ?? '');
            $this->conn->query("
                INSERT INTO asistencia(id_estudiante, fecha, asistio, observacion)
                VALUES($id, '$fecha', $asistio, '$obs')
            ");
        }
        return true;
    }

    public function historial($limite = 14) {
        return $this->conn->query("
            SELECT fecha, SUM(asistio=1) AS presentes, SUM(asistio=0) AS ausentes
            FROM asistencia
            GROUP BY fecha
            ORDER BY fecha DESC
            LIMIT $limite
        ");
    }
}
