<?php
/**
 * scripts/make_icons.php — Genera los íconos PWA de NutriPredict con GD.
 * Uso:  php scripts/make_icons.php
 * Crea css/icons/icon-192.png, icon-512.png y icon-maskable-512.png
 */

$font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
$outDir = dirname(__DIR__) . '/css/icons';
@mkdir($outDir, 0755, true);

/** Dibuja un rectángulo redondeado relleno. */
function roundedRect($img, $x1, $y1, $x2, $y2, $r, $color) {
    imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
    imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
    imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
    imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
}

function generar(int $size, string $path, bool $maskable, string $font): void {
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transp = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transp);

    $verde   = imagecolorallocate($img, 0x16, 0xa3, 0x4a); // #16a34a
    $verde2  = imagecolorallocate($img, 0x22, 0xc5, 0x5e); // #22c55e
    $blanco  = imagecolorallocate($img, 255, 255, 255);

    // Maskable: el fondo cubre todo el lienzo (zona segura). Normal: con margen redondeado.
    if ($maskable) {
        imagefilledrectangle($img, 0, 0, $size, $size, $verde);
    } else {
        $m = (int) round($size * 0.06);
        roundedRect($img, $m, $m, $size - $m, $size - $m, (int) round($size * 0.18), $verde);
    }

    // Acento: círculo "plato" blanco translúcido
    $platoR = (int) round($size * 0.30);
    $cx = (int) ($size / 2);
    $cy = (int) ($size / 2);
    $platoColor = imagecolorallocatealpha($img, 255, 255, 255, 110);
    imagefilledellipse($img, $cx, $cy, $platoR * 2, $platoR * 2, $platoColor);

    // Marca "N" centrada
    $fs = (int) round($size * 0.42);
    $bbox = imagettfbbox($fs, 0, $font, 'N');
    $tw = $bbox[2] - $bbox[0];
    $th = $bbox[1] - $bbox[7];
    $tx = (int) ($cx - $tw / 2 - $bbox[0]);
    $ty = (int) ($cy + $th / 2 - ($bbox[1]));
    imagettftext($img, $fs, 0, $tx, $ty, $blanco, $font, 'N');

    imagepng($img, $path);
    imagedestroy($img);
    echo "  ✓ $path (" . filesize($path) . " bytes)\n";
}

generar(192, "$outDir/icon-192.png", false, $font);
generar(512, "$outDir/icon-512.png", false, $font);
generar(512, "$outDir/icon-maskable-512.png", true, $font);
echo "Íconos generados en $outDir\n";
