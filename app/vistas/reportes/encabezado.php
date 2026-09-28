<?php
declare(strict_types=1);

/* Datos del software, centralizados en un solo lugar (Día 15) */
const APP = [
    'nombre'  => 'ZD.TechLab',
    'lema'    => 'Sistema de gestión de tienda tecnológica',
    'version' => '1.0',
    'nit'     => '900.123.456-7',
    'ciudad'  => 'Bogotá D.C.',
    'correo'  => 'soporte@zdtechlab.co',
    'logo'    => __DIR__ . '/../../../assets/img/logo.png',
];

function logoBase64(): string
{
    return 'data:image/png;base64,' . base64_encode(file_get_contents(APP['logo']));
}
?>
<header class="reporte__encabezado">
  <img src="<?= logoBase64() ?>" alt="Logo de <?= APP['nombre'] ?>" class="reporte__logo">
  <div class="reporte__marca">
    <h1><?= APP['nombre'] ?></h1>
    <p><?= APP['lema'] ?> · v<?= APP['version'] ?></p>
    <p>NIT <?= APP['nit'] ?> · <?= APP['ciudad'] ?> · <?= APP['correo'] ?></p>
  </div>
  <div class="reporte__meta">
    <p>Generado: <?= date('d/m/Y H:i') ?></p>
    <p>Usuario: <?= htmlspecialchars($_SESSION['usuario']['nombre'], ENT_QUOTES, 'UTF-8') ?>
      (<?= htmlspecialchars(ucfirst($_SESSION['usuario']['rol']), ENT_QUOTES, 'UTF-8') ?>)</p>
    <p>Reporte N.° <?= $numeroReporte ?? '—' ?></p>
  </div>
</header>
