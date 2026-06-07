<?php
// models/AlimentoModel.php
class AlimentoModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerTodos($categoria = '') {
        $where = "WHERE activo=1";
        if ($categoria) {
            $cat = mysqli_real_escape_string($this->conn, $categoria);
            $where .= " AND categoria='$cat'";
        }
        return $this->conn->query("SELECT * FROM alimentos $where ORDER BY categoria, nombre");
    }

    public function crear($datos) {
        $stmt = $this->conn->prepare("
            INSERT INTO alimentos(nombre, categoria, calorias, proteinas_g, carbohidratos_g,
            grasas_g, hierro_mg, calcio_mg, vitamina_d_ug, zinc_mg)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssdddddddd',
            $datos['nombre'], $datos['categoria'], $datos['calorias'],
            $datos['proteinas_g'], $datos['carbohidratos_g'], $datos['grasas_g'],
            $datos['hierro_mg'], $datos['calcio_mg'], $datos['vitamina_d_ug'], $datos['zinc_mg']
        );
        return $stmt->execute();
    }

    public function eliminar($id) {
        $id = (int)$id;
        return $this->conn->query("UPDATE alimentos SET activo=0 WHERE id=$id");
    }
}
