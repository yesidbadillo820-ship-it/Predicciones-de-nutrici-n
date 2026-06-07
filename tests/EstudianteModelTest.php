<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/EstudianteModel.php';

/**
 * Pruebas de la lógica pura de EstudianteModel (sin base de datos):
 * cálculo de IMC y clasificación OMS, y cálculo de edad.
 */
final class EstudianteModelTest extends TestCase
{
    public function testCalcularImcNormalNino(): void
    {
        // 20 kg, 115 cm, 7 años → IMC 15.12, clasificación Normal
        $r = EstudianteModel::calcularIMC(20, 115, 7);
        $this->assertSame(15.12, $r['imc']);
        $this->assertSame('Normal', $r['clasificacion']);
    }

    public function testCalcularImcBajoPesoNino(): void
    {
        // 16 kg, 118 cm, 7 años → IMC ~11.49 → Bajo peso
        $r = EstudianteModel::calcularIMC(16, 118, 7);
        $this->assertSame('Bajo peso', $r['clasificacion']);
        $this->assertEqualsWithDelta(11.49, $r['imc'], 0.05);
    }

    public function testCalcularImcSinDatosDevuelveNull(): void
    {
        $this->assertNull(EstudianteModel::calcularIMC(0, 120, 7));
        $this->assertNull(EstudianteModel::calcularIMC(20, 0, 7));
    }

    public function testClasificacionAdultoUsaUmbralesDistintos(): void
    {
        // IMC 24 con 20 años → Normal (umbral adulto < 25)
        $r = EstudianteModel::calcularIMC(70, 170, 20);
        $this->assertSame('Normal', $r['clasificacion']);
    }

    public function testCalcularEdad(): void
    {
        $hace8 = (new DateTime('-8 years -2 months'))->format('Y-m-d');
        $this->assertSame(8, EstudianteModel::calcularEdad($hace8));
    }
}
