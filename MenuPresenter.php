<?php
// presenters/MenuPresenter.php
// RF03 + RF06: Registra menú, calcula nutrientes automáticamente y genera alertas
require_once 'models/MenuModel.php';
require_once 'models/AlertaModel.php';
require_once 'models/AlimentoModel.php';

class MenuPresenter {
    private $menuModel;
    private $alertaModel;
    private $alimentoModel;
    public $msg = '';
    public $err = '';
    public $alertas_generadas = [];

    public function __construct($conn) {
        $this->menuModel    = new MenuModel($conn);
        $this->alertaModel  = new AlertaModel($conn);
        $this->alimentoModel = new AlimentoModel($conn);
    }

    public function manejarRequest($id_usuario) {
        if (isset($_GET['del'])) {
            $this->menuModel->eliminar((int)$_GET['del']);
            $this->msg = 'Menú eliminado.';
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->guardarMenu($id_usuario);
    }

    private function guardarMenu($id_usuario) {
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $tipo  = $_POST['tipo_tiempo'] ?? 'almuerzo';
        $desc  = trim($_POST['descripcion'] ?? '');

        if (!$desc) { $this->err = 'La descripción es obligatoria.'; return; }

        // RF03: Recoger alimentos seleccionados con sus porciones
        $alimentos_porciones = [];
        $ids_alimentos = $_POST['id_alimento'] ?? [];
        $porciones     = $_POST['porcion_g'] ?? [];
        foreach ($ids_alimentos as $i => $id_al) {
            if ($id_al) $alimentos_porciones[(int)$id_al] = (float)($porciones[$i] ?? 100);
        }

        // RF03: Guardar y calcular nutrientes automáticamente
        $resultado = $this->menuModel->guardar($fecha, $tipo, $desc, $alimentos_porciones, $id_usuario);

        if (!$resultado) { $this->err = 'Error al guardar el menú.'; return; }

        // RF06: Agente inteligente — generar alertas si nutriente < 70% ICBF
        $cobertura = $resultado['calculo']['cobertura'];
        $this->alertaModel->generarAlertasMenu($resultado['id_menu'], $cobertura, $id_usuario);

        // Recoger alertas generadas para mostrar en la vista
        foreach ($cobertura as $item) {
            if (!$item['ok']) {
                $sugs = AlertaModel::getSugerencias($item['nombre']);
                $this->alertas_generadas[] = [
                    'nutriente'   => $item['nombre'],
                    'pct'         => $item['pct'],
                    'sugerencias' => $sugs,
                ];
            }
        }

        $this->msg = count($this->alertas_generadas) > 0
            ? 'Menú guardado. Se detectaron ' . count($this->alertas_generadas) . ' deficiencia(s) nutricional(es).'
            : 'Menú guardado. ✅ Todos los nutrientes cubren el mínimo recomendado por el ICBF.';
    }

    public function obtenerMenuFecha($fecha) {
        $menus = [];
        $q = $this->menuModel->obtenerPorFecha($fecha);
        if ($q) while ($m = $q->fetch_assoc()) $menus[$m['tipo_tiempo']] = $m;
        return $menus;
    }

    public function obtenerHistorial() {
        return $this->menuModel->obtenerHistorial(30);
    }

    public function obtenerAlimentos() {
        return $this->alimentoModel->obtenerTodos();
    }

    public function obtenerNivelesICBF() {
        return MenuModel::ICBF;
    }
}
