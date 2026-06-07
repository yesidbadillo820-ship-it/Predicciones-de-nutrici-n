<?php
// models/MenuModel.php
// RF03 — Registro diario con cálculo automático de nutrientes vs valores ICBF

class MenuModel {
    private $conn;

    // RF03: Valores recomendados ICBF para niños 5-12 años (por día)
    const ICBF = [
        'calorias'       => 1800,
        'proteinas_g'    => 40,
        'carbohidratos_g'=> 245,
        'grasas_g'       => 50,
        'hierro_mg'      => 10,
        'calcio_mg'      => 1000,
        'vitamina_d_ug'  => 15,
        'zinc_mg'        => 7,
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // RF03: Calcular nutrientes totales de un menú sumando los alimentos
    public function calcularNutrientes($id_alimentos_porciones) {
        $totales = array_fill_keys(array_keys(self::ICBF), 0);
        foreach ($id_alimentos_porciones as $id_alimento => $porcion_g) {
            $id = (int)$id_alimento;
            $por = (float)$porcion_g;
            $a = $this->conn->query("SELECT * FROM alimentos WHERE id=$id AND activo=1")->fetch_assoc();
            if (!$a) continue;
            $factor = $por / 100;
            $totales['calorias']        += $a['calorias']        * $factor;
            $totales['proteinas_g']     += $a['proteinas_g']     * $factor;
            $totales['carbohidratos_g'] += $a['carbohidratos_g'] * $factor;
            $totales['grasas_g']        += $a['grasas_g']        * $factor;
            $totales['hierro_mg']       += $a['hierro_mg']       * $factor;
            $totales['calcio_mg']       += $a['calcio_mg']       * $factor;
            $totales['vitamina_d_ug']   += $a['vitamina_d_ug']   * $factor;
            $totales['zinc_mg']         += $a['zinc_mg']         * $factor;
        }
        // Calcular % de cobertura vs ICBF
        $cobertura = [];
        foreach (self::ICBF as $nut => $recomendado) {
            // Los valores ICBF son siempre > 0 (constantes), así que la división es segura
            $pct = round(($totales[$nut] / $recomendado) * 100, 1);
            $cobertura[] = [
                'key'    => $nut,
                'nombre' => ucfirst(str_replace(['_g','_mg','_ug','_'], ['',' mg',' μg',' '], $nut)),
                'valor'  => round($totales[$nut], 2),
                'pct'    => $pct,
                'ok'     => $pct >= 70,  // RF03: alerta si < 70% del recomendado ICBF
            ];
        }
        return ['totales' => $totales, 'cobertura' => $cobertura];
    }

    public function obtenerPorFecha($fecha) {
        $fecha = mysqli_real_escape_string($this->conn, $fecha);
        return $this->conn->query("
            SELECT m.*, u.nombre AS registrado_por
            FROM menus m
            JOIN usuarios u ON m.id_usuario = u.id
            WHERE m.fecha = '$fecha'
            ORDER BY FIELD(m.tipo_tiempo,'desayuno','almuerzo','merienda')
        ");
    }

    public function obtenerMenuHoy() {
        return $this->obtenerPorFecha(date('Y-m-d'));
    }

    public function obtenerHistorial($limite = 30) {
        return $this->conn->query("
            SELECT m.*, u.nombre AS registrado_por
            FROM menus m JOIN usuarios u ON m.id_usuario = u.id
            ORDER BY m.fecha DESC, FIELD(m.tipo_tiempo,'desayuno','almuerzo','merienda')
            LIMIT $limite
        ");
    }

    public function obtenerAlimentosDelMenu($id_menu) {
        return $this->conn->query("
            SELECT ma.porcion_g, a.*
            FROM menu_alimentos ma
            JOIN alimentos a ON ma.id_alimento = a.id
            WHERE ma.id_menu = $id_menu
        ");
    }

    // RF03: Guardar menú con cálculo automático de nutrientes
    public function guardar($fecha, $tipo, $descripcion, $alimentos_porciones, $id_usuario) {
        $fecha = mysqli_real_escape_string($this->conn, $fecha);
        $tipo  = mysqli_real_escape_string($this->conn, $tipo);

        // Calcular nutrientes automáticamente
        $calculo = $this->calcularNutrientes($alimentos_porciones);
        $nutrientes_json = json_encode($calculo['cobertura'], JSON_UNESCAPED_UNICODE);
        $totales_json    = json_encode($calculo['totales'],   JSON_UNESCAPED_UNICODE);

        $stmt = $this->conn->prepare("
            INSERT INTO menus(fecha, tipo_tiempo, descripcion, nutrientes_cubre, totales_json, id_usuario)
            VALUES(?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                descripcion=VALUES(descripcion),
                nutrientes_cubre=VALUES(nutrientes_cubre),
                totales_json=VALUES(totales_json)
        ");
        $stmt->bind_param('sssssi', $fecha, $tipo, $descripcion, $nutrientes_json, $totales_json, $id_usuario);
        if (!$stmt->execute()) return false;

        $id_menu = $this->conn->insert_id ?: $this->obtenerIdMenu($fecha, $tipo);

        // Guardar alimentos del menú
        $this->conn->query("DELETE FROM menu_alimentos WHERE id_menu=$id_menu");
        foreach ($alimentos_porciones as $id_alimento => $porcion) {
            $id_alimento = (int)$id_alimento;
            $porcion     = (float)$porcion;
            $this->conn->query("INSERT INTO menu_alimentos(id_menu,id_alimento,porcion_g) VALUES($id_menu,$id_alimento,$porcion)");
        }

        // D1: registrar la cobertura del día para reportes y predicción
        $this->registrarCobertura($fecha, $calculo['cobertura']);

        return ['id_menu' => $id_menu, 'calculo' => $calculo];
    }

    private function obtenerIdMenu($fecha, $tipo) {
        $r = $this->conn->query("SELECT id FROM menus WHERE fecha='$fecha' AND tipo_tiempo='$tipo'")->fetch_assoc();
        return $r['id'] ?? null;
    }

    // D1: persistir el % de cobertura por nutriente clave (para reportes/predicción).
    // Usa nombres canónicos que coinciden con ReporteModel y upsert por (fecha, nutriente).
    private function registrarCobertura($fecha, array $cobertura) {
        static $nombres = [
            'hierro_mg'     => 'Hierro',
            'calcio_mg'     => 'Calcio',
            'proteinas_g'   => 'Proteinas',
            'vitamina_d_ug' => 'Vitamina D',
            'zinc_mg'       => 'Zinc',
        ];
        $stmt = $this->conn->prepare("
            INSERT INTO cobertura_nutricional(fecha, nutriente, porcentaje)
            VALUES(?, ?, ?)
            ON DUPLICATE KEY UPDATE porcentaje = VALUES(porcentaje)
        ");
        foreach ($cobertura as $item) {
            $key = $item['key'] ?? '';
            if (!isset($nombres[$key])) continue;
            $nombre = $nombres[$key];
            $pct    = (float) $item['pct'];
            $stmt->bind_param('ssd', $fecha, $nombre, $pct);
            $stmt->execute();
        }
    }

    public function eliminar($id) {
        $this->conn->query("DELETE FROM menu_alimentos WHERE id_menu=".(int)$id);
        return $this->conn->query("DELETE FROM menus WHERE id=".(int)$id);
    }
}
