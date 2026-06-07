<?php
declare(strict_types=1);

class AlertaRepository {
    public function __construct(private mysqli $db) {}

    public function porEstado(string $estado): array {
        $st = $this->db->prepare("SELECT * FROM alertas WHERE estado=? ORDER BY fecha_creacion DESC");
        $st->bind_param('s', $estado);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function conteos(): array {
        $c = [];
        foreach (['activa', 'resuelta', 'ignorada'] as $e) {
            $st = $this->db->prepare("SELECT COUNT(*) t FROM alertas WHERE estado=?");
            $st->bind_param('s', $e);
            $st->execute();
            $c[$e] = (int) $st->get_result()->fetch_assoc()['t'];
        }
        return $c;
    }

    public function contarEstudiante(int $idEst, string $estado): int {
        $st = $this->db->prepare("SELECT COUNT(*) t FROM alertas WHERE id_estudiante=? AND estado=?");
        $st->bind_param('is', $idEst, $estado);
        $st->execute();
        return (int) $st->get_result()->fetch_assoc()['t'];
    }

    public function crear(int $idEst, string $tipo, string $desc, string $nivel): int {
        $st = $this->db->prepare("INSERT INTO alertas(id_estudiante,tipo_deficiencia,descripcion,nivel) VALUES(?,?,?,?)");
        $st->bind_param('isss', $idEst, $tipo, $desc, $nivel);
        $st->execute();
        return (int) $this->db->insert_id;
    }

    public function resolver(int $id): bool {
        return $this->db->query("UPDATE alertas SET estado='resuelta', fecha_resolucion=NOW() WHERE id=" . $id);
    }

    public function ignorar(int $id): bool {
        return $this->db->query("UPDATE alertas SET estado='ignorada' WHERE id=" . $id);
    }
}
