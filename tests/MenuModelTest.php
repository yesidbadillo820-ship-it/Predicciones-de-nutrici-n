<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/MenuModel.php';

/** Resultado falso que imita mysqli_result::fetch_assoc(). */
final class FakeResult
{
    public function __construct(private ?array $row) {}
    public function fetch_assoc(): ?array { return $this->row; }
}

/** Conexión falsa: devuelve el alimento según el id presente en el SQL. */
final class FakeConn
{
    public array $alimentos = [];
    public function query(string $sql)
    {
        preg_match('/id=(\d+)/', $sql, $m);
        $id = (int) ($m[1] ?? 0);
        return new FakeResult($this->alimentos[$id] ?? null);
    }
}

/**
 * Prueba del cálculo de nutrientes y cobertura vs ICBF (sin base de datos real).
 */
final class MenuModelTest extends TestCase
{
    public function testCoberturaCien(): void
    {
        $conn = new FakeConn();
        // Un alimento cuyos valores por 100 g igualan exactamente el ICBF diario
        $conn->alimentos[1] = [
            'calorias' => 1800, 'proteinas_g' => 40, 'carbohidratos_g' => 245,
            'grasas_g' => 50, 'hierro_mg' => 10, 'calcio_mg' => 1000,
            'vitamina_d_ug' => 15, 'zinc_mg' => 7,
        ];

        $model = new MenuModel($conn);
        $res = $model->calcularNutrientes([1 => 100]); // 100 g → factor 1

        foreach ($res['cobertura'] as $item) {
            $this->assertSame(100.0, (float) $item['pct'], "Cobertura de {$item['nombre']}");
            $this->assertTrue($item['ok']);
            $this->assertArrayHasKey('key', $item); // necesario para registrarCobertura (D1)
        }
    }

    public function testCoberturaParcialMarcaNoOk(): void
    {
        $conn = new FakeConn();
        $conn->alimentos[1] = [
            'calorias' => 900, 'proteinas_g' => 20, 'carbohidratos_g' => 122,
            'grasas_g' => 25, 'hierro_mg' => 5, 'calcio_mg' => 500,
            'vitamina_d_ug' => 7, 'zinc_mg' => 3,
        ];
        $model = new MenuModel($conn);
        $res = $model->calcularNutrientes([1 => 100]); // ~50% de todo

        foreach ($res['cobertura'] as $item) {
            $this->assertFalse($item['ok'], "{$item['nombre']} debería estar por debajo del 70%");
            $this->assertLessThan(70, (float) $item['pct']);
        }
    }
}
