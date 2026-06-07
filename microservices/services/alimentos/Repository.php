<?php
declare(strict_types=1);

class AlimentoRepository {
    public function __construct(private mysqli $db) {}

    public function listar(string $categoria = ''): array {
        if ($categoria !== '') {
            $st = $this->db->prepare("SELECT * FROM alimentos WHERE activo=1 AND categoria=? ORDER BY categoria, nombre");
            $st->bind_param('s', $categoria);
            $st->execute();
            return $st->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $this->db->query("SELECT * FROM alimentos WHERE activo=1 ORDER BY categoria, nombre")->fetch_all(MYSQLI_ASSOC);
    }

    public function crear(array $d): int {
        $st = $this->db->prepare("INSERT INTO alimentos(nombre,categoria,calorias,proteinas_g,carbohidratos_g,grasas_g,hierro_mg,calcio_mg,vitamina_d_ug,zinc_mg)
            VALUES(?,?,?,?,?,?,?,?,?,?)");
        $st->bind_param('ssdddddddd', $d['nombre'], $d['categoria'], $d['calorias'], $d['proteinas_g'], $d['carbohidratos_g'], $d['grasas_g'], $d['hierro_mg'], $d['calcio_mg'], $d['vitamina_d_ug'], $d['zinc_mg']);
        $st->execute();
        return (int) $this->db->insert_id;
    }

    public function eliminar(int $id): bool {
        return $this->db->query("UPDATE alimentos SET activo=0 WHERE id=" . $id);
    }
}
