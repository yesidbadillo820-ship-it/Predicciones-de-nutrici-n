<?php
declare(strict_types=1);

class MenuRepository {
    public const ICBF = [
        'calorias' => 1800, 'proteinas_g' => 40, 'carbohidratos_g' => 245, 'grasas_g' => 50,
        'hierro_mg' => 10, 'calcio_mg' => 1000, 'vitamina_d_ug' => 15, 'zinc_mg' => 7,
    ];
    private const NOMBRES = [
        'hierro_mg' => 'Hierro', 'calcio_mg' => 'Calcio', 'proteinas_g' => 'Proteinas',
        'vitamina_d_ug' => 'Vitamina D', 'zinc_mg' => 'Zinc',
    ];

    public function __construct(private mysqli $db) {}

    /** @param array<int,array> $alimentosById  catálogo obtenido del microservicio Alimentos */
    public function calcularNutrientes(array $items, array $alimentosById): array {
        $tot = array_fill_keys(array_keys(self::ICBF), 0.0);
        foreach ($items as $it) {
            $a = $alimentosById[(int) ($it['id_alimento'] ?? 0)] ?? null;
            if (!$a) continue;
            $f = ((float) ($it['porcion_g'] ?? 100)) / 100;
            foreach (array_keys(self::ICBF) as $k) $tot[$k] += ((float) ($a[$k] ?? 0)) * $f;
        }
        $cob = [];
        foreach (self::ICBF as $k => $rec) {
            $pct = round($tot[$k] / $rec * 100, 1);
            $cob[] = ['key' => $k, 'nombre' => ucfirst(str_replace(['_g','_mg','_ug','_'], ['',' mg',' µg',' '], $k)), 'pct' => $pct, 'ok' => $pct >= 70];
        }
        return ['totales' => $tot, 'cobertura' => $cob];
    }

    public function guardar(string $fecha, string $tipo, string $desc, array $items, array $alimentosById): array {
        $calc = $this->calcularNutrientes($items, $alimentosById);
        $json = json_encode($calc['cobertura'], JSON_UNESCAPED_UNICODE);
        $st = $this->db->prepare("INSERT INTO menus(fecha,tipo_tiempo,descripcion,nutrientes_cubre) VALUES(?,?,?,?)
            ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), nutrientes_cubre=VALUES(nutrientes_cubre)");
        $st->bind_param('ssss', $fecha, $tipo, $desc, $json);
        $st->execute();
        $idMenu = $this->db->insert_id ?: (int) ($this->db->query("SELECT id FROM menus WHERE fecha='" . $this->db->real_escape_string($fecha) . "' AND tipo_tiempo='" . $this->db->real_escape_string($tipo) . "'")->fetch_assoc()['id'] ?? 0);
        $this->db->query("DELETE FROM menu_alimentos WHERE id_menu=" . $idMenu);
        $ins = $this->db->prepare("INSERT INTO menu_alimentos(id_menu,id_alimento,porcion_g) VALUES(?,?,?)");
        foreach ($items as $it) {
            $ia = (int) ($it['id_alimento'] ?? 0); $p = (float) ($it['porcion_g'] ?? 100);
            if ($ia > 0) { $ins->bind_param('iid', $idMenu, $ia, $p); $ins->execute(); }
        }
        $this->registrarCobertura($fecha, $calc['cobertura']);
        return ['id_menu' => $idMenu, 'calculo' => $calc];
    }

    private function registrarCobertura(string $fecha, array $cobertura): void {
        $st = $this->db->prepare("INSERT INTO cobertura_nutricional(fecha,nutriente,porcentaje) VALUES(?,?,?)
            ON DUPLICATE KEY UPDATE porcentaje=VALUES(porcentaje)");
        foreach ($cobertura as $c) {
            if (!isset(self::NOMBRES[$c['key']])) continue;
            $n = self::NOMBRES[$c['key']]; $p = (float) $c['pct'];
            $st->bind_param('ssd', $fecha, $n, $p);
            $st->execute();
        }
    }

    public function porFecha(string $fecha): array {
        $st = $this->db->prepare("SELECT * FROM menus WHERE fecha=? ORDER BY FIELD(tipo_tiempo,'desayuno','almuerzo','merienda')");
        $st->bind_param('s', $fecha);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function coberturaPromedio(int $dias): array {
        $st = $this->db->prepare("SELECT nutriente, ROUND(AVG(porcentaje),1) prom FROM cobertura_nutricional
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY nutriente
            ORDER BY FIELD(nutriente,'Hierro','Calcio','Proteinas','Vitamina D','Zinc')");
        $st->bind_param('i', $dias);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
