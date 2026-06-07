<?php
declare(strict_types=1);

class AsistenciaRepository {
    public function __construct(private mysqli $db) {}

    public function porFecha(string $fecha): array {
        $st = $this->db->prepare("SELECT id_estudiante, asistio, observacion FROM asistencia WHERE fecha=? ORDER BY id_estudiante");
        $st->bind_param('s', $fecha);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function guardar(string $fecha, array $registros): int {
        $del = $this->db->prepare("DELETE FROM asistencia WHERE fecha=?");
        $del->bind_param('s', $fecha);
        $del->execute();
        $ins = $this->db->prepare("INSERT INTO asistencia(id_estudiante,fecha,asistio,observacion) VALUES(?,?,?,?)");
        $n = 0;
        foreach ($registros as $r) {
            $id = (int) ($r['id_estudiante'] ?? 0);
            if ($id <= 0) continue;
            $asistio = !empty($r['asistio']) ? 1 : 0;
            $obs = (string) ($r['observacion'] ?? '');
            $ins->bind_param('isis', $id, $fecha, $asistio, $obs);
            $ins->execute();
            $n++;
        }
        return $n;
    }

    public function resumen(string $fecha): array {
        $st = $this->db->prepare("SELECT COALESCE(SUM(asistio=1),0) presentes, COALESCE(SUM(asistio=0),0) ausentes, COUNT(*) total FROM asistencia WHERE fecha=?");
        $st->bind_param('s', $fecha);
        $st->execute();
        return $st->get_result()->fetch_assoc();
    }

    public function inasistencias(int $idEst, int $dias): int {
        $st = $this->db->prepare("SELECT COUNT(*) t FROM asistencia WHERE id_estudiante=? AND asistio=0 AND fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)");
        $st->bind_param('ii', $idEst, $dias);
        $st->execute();
        return (int) $st->get_result()->fetch_assoc()['t'];
    }
}
