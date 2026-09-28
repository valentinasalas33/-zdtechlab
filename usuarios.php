<?php
declare(strict_types=1);

require_once __DIR__ . '/app/seguridad/guardia.php';
exigirRol('administrador'); // 403 para vendedor o consultor
require_once __DIR__ . '/app/config/rutas.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Usuarios — ZD.TechLab</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/estilos.css">
</head>
<body>
  <div class="panel">
    <?php require __DIR__ . '/app/vistas/parciales/cabecera.php'; ?>
    <?php require __DIR__ . '/app/vistas/parciales/menu.php'; ?>
    <main class="panel__contenido">
      <h1>Usuarios</h1>
      <p class="texto-tenue">Esta página demuestra el control de acceso por rol: solo un usuario con rol administrador puede llegar hasta acá (Día 11 — Actividad 4). El alta de nuevos usuarios se hace desde <a href="<?= BASE_URL ?>app/controladores/registro.php">el formulario de registro</a>.</p>
    </main>
    <?php require __DIR__ . '/app/vistas/parciales/pie.php'; ?>
</body>
</html>
