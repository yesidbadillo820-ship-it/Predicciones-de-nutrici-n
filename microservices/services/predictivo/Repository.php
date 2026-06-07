<?php
declare(strict_types=1);

class RiesgoRepository {
    public function __construct(private mysqli $db) {}

    public function guardar(int $idEst, string $fecha, string $nivel, int $score): void {
        $st = $this->db->prepare("INSERT INTO riesgo_diario(id_estudiante,fecha,nivel_riesgo,score) VALUES(?,?,?,?)
            ON DUPLICATE KEY UPDATE nivel_riesgo=VALUES(nivel_riesgo), score=VALUES(score)");
        $st->bind_param('issi', $idEst, $fecha, $nivel, $score);
        $st->execute();
    }

    public function conteos(string $fecha): array {
        $c = ['alto' => 0, 'medio' => 0, 'bajo' => 0, 'sin_riesgo' => 0];
        $st = $this->db->prepare("SELECT nivel_riesgo, COUNT(*) t FROM riesgo_diario WHERE fecha=? GROUP BY nivel_riesgo");
        $st->bind_param('s', $fecha);
        $st->execute();
        foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $c[$r['nivel_riesgo']] = (int) $r['t'];
        return $c;
    }

    public function listar(string $fecha): array {
        $st = $this->db->prepare("SELECT id_estudiante, nivel_riesgo, score FROM riesgo_diario WHERE fecha=? ORDER BY score DESC");
        $st->bind_param('s', $fecha);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Motor de scoring (0-100) — misma regla que el monolito. */
    public static function score(int $alertas, int $inasistencias, float $cobertura): array {
        $s = 0;
        if ($alertas >= 3) $s += 40; elseif ($alertas >= 1) $s += 25;
        if ($inasistencias >= 5) $s += 20; elseif ($inasistencias >= 2) $s += 10;
        if ($cobertura < 60) $s += 30; elseif ($cobertura < 75) $s += 15;
        $s = max(0, min(100, $s));
        $nivel = $s >= 70 ? 'alto' : ($s >= 40 ? 'medio' : ($s >= 15 ? 'bajo' : 'sin_riesgo'));
        return ['score' => $s, 'nivel' => $nivel];
    }
}
