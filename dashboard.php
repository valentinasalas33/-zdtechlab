<?php
declare(strict_types=1);

require_once __DIR__ . '/app/seguridad/guardia.php';
require_once __DIR__ . '/app/config/rutas.php';
require_once __DIR__ . '/app/config/conexion.php';

// Indicadores reales calculados con una sola consulta de agregación
$pdo = Conexion::obtener();
$mesActual = (int) date('n');
$anioActual = (int) date('Y');

$st = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*) FROM productos WHERE activo = 1) AS productos_activos,
        (SELECT COUNT(*) FROM pedidos
            WHERE estado = 'confirmado' AND YEAR(fecha) = :anio1 AND MONTH(fecha) = :mes1) AS pedidos_mes,
        (SELECT COALESCE(SUM(total), 0) FROM pedidos
            WHERE estado = 'confirmado' AND YEAR(fecha) = :anio2 AND MONTH(fecha) = :mes2) AS ventas_mes,
        (SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock < stock_minimo) AS stock_critico"
);
$st->execute([
    'anio1' => $anioActual, 'mes1' => $mesActual,
    'anio2' => $anioActual, 'mes2' => $mesActual,
]);
$indicadores = $st->fetch();

$formatoMoneda = fn (float $n) => '$ ' . number_format($n, 0, ',', '.');

$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$nombreMes = $meses[$mesActual] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tablero — ZD.TechLab</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/estilos.css">
</head>
<body>
  <div class="panel">
    <?php require __DIR__ . '/app/vistas/parciales/cabecera.php'; ?>
    <?php require __DIR__ . '/app/vistas/parciales/menu.php'; ?>
    <main class="panel__contenido">
      <h1>Resumen general</h1>
      <p class="texto-tenue">Periodo: <?= htmlspecialchars($nombreMes, ENT_QUOTES, 'UTF-8') ?> de <?= $anioActual ?></p>
      <section class="indicadores" aria-label="Indicadores clave">
        <article class="tarjeta tarjeta--verde"><p class="tarjeta__rotulo">Productos activos</p><p class="tarjeta__valor"><?= (int) $indicadores['productos_activos'] ?></p></article>
        <article class="tarjeta tarjeta--azul"><p class="tarjeta__rotulo">Pedidos del mes</p><p class="tarjeta__valor"><?= (int) $indicadores['pedidos_mes'] ?></p></article>
        <article class="tarjeta tarjeta--naranja"><p class="tarjeta__rotulo">Ventas del mes</p><p class="tarjeta__valor"><?= $formatoMoneda((float) $indicadores['ventas_mes']) ?></p></article>
        <article class="tarjeta tarjeta--error"><p class="tarjeta__rotulo">Stock crítico</p><p class="tarjeta__valor"><?= (int) $indicadores['stock_critico'] ?></p></article>
      </section>

      <form id="form-filtro-fechas" class="formulario--linea" aria-label="Filtro de rango de fechas para los gráficos">
        <div>
          <label for="desde">Desde</label>
          <input type="date" id="desde" name="desde">
        </div>
        <div>
          <label for="hasta">Hasta</label>
          <input type="date" id="hasta" name="hasta">
        </div>
        <button type="submit" class="boton">Filtrar gráficos</button>
      </form>

          <section class="graficos" aria-label="Gráficos de ventas">
        <div class="tarjeta-grafico">
          <h2>Ventas por mes</h2>
          <div class="grafico-envoltorio"><canvas id="g-ventas" aria-label="Gráfico de barras de ventas mensuales" role="img"></canvas></div>
        </div>
        <div class="tarjeta-grafico">
          <h2>Pedidos por mes</h2>
          <div class="grafico-envoltorio"><canvas id="g-pedidos" aria-label="Gráfico de líneas de pedidos mensuales" role="img"></canvas></div>
        </div>
        <div class="tarjeta-grafico">
          <h2>Ventas por categoría</h2>
          <div class="grafico-envoltorio"><canvas id="g-categorias" aria-label="Gráfico de dona de ventas por categoría" role="img"></canvas></div>
        </div>
      </section>
    </main>
    <?php require __DIR__ . '/app/vistas/parciales/pie.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?= BASE_URL ?>js/graficos.js" defer></script>
</body>
</html>
