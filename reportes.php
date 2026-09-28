<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Reportes (Día 15)
 * Tres reportes alimentados por vistas / consultas parametrizadas por
 * rango de fechas, con logo institucional, totales, gráfico (en 2 de los
 * 3) y exportación a PDF/CSV. La misma vista sirve para pantalla e impresión.
 */
require_once __DIR__ . '/app/seguridad/guardia.php';
require_once __DIR__ . '/app/seguridad/csrf.php';
require_once __DIR__ . '/app/config/rutas.php';
require_once __DIR__ . '/app/config/conexion.php';

exigirRol('administrador', 'consultor');

$pdo = Conexion::obtener();

$tipo  = $_GET['tipo'] ?? 'ventas';
$tipo  = in_array($tipo, ['ventas', 'inventario', 'pedidos'], true) ? $tipo : 'ventas';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$hayFiltro = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta);

$titulos = [
    'ventas'     => 'Reporte de ventas por categoría',
    'inventario' => 'Reporte de inventario con stock crítico',
    'pedidos'    => 'Reporte de pedidos por cliente',
];

if ($tipo === 'ventas') {
    if ($hayFiltro) {
        $st = $pdo->prepare(
            "SELECT c.nombre AS categoria, COALESCE(SUM(d.cantidad),0) AS unidades, COALESCE(SUM(d.cantidad*d.precio_unitario),0) AS total_vendido
             FROM categorias c
             LEFT JOIN productos pr ON pr.categoria_id = c.id
             LEFT JOIN detalle_pedido d ON d.producto_id = pr.id
             LEFT JOIN pedidos p ON p.id = d.pedido_id AND p.estado='confirmado' AND p.fecha BETWEEN :desde AND DATE_ADD(:hasta, INTERVAL 1 DAY)
             GROUP BY c.id, c.nombre
             ORDER BY total_vendido DESC"
        );
        $st->execute(['desde' => $desde, 'hasta' => $hasta]);
    } else {
        $st = $pdo->query('SELECT categoria, unidades, total_vendido FROM v_ventas_categoria ORDER BY total_vendido DESC');
    }
    $filas = $st->fetchAll();
    $columnas = ['categoria' => 'Categoría', 'unidades' => 'Unidades', 'total_vendido' => 'Total vendido'];
    $columnaTotal = 'total_vendido';
} elseif ($tipo === 'inventario') {
    $st = $pdo->query('SELECT nombre, categoria, stock, stock_minimo FROM v_stock_critico ORDER BY stock ASC');
    $filas = $st->fetchAll();
    $columnas = ['nombre' => 'Producto', 'categoria' => 'Categoría', 'stock' => 'Stock', 'stock_minimo' => 'Stock mínimo'];
    $columnaTotal = null;
} else {
    if ($hayFiltro) {
        $st = $pdo->prepare(
            "SELECT cl.nombre, cl.documento, COUNT(DISTINCT p.id) AS pedidos, COALESCE(SUM(p.total),0) AS total_comprado
             FROM clientes cl
             LEFT JOIN pedidos p ON p.cliente_id = cl.id AND p.estado='confirmado' AND p.fecha BETWEEN :desde AND DATE_ADD(:hasta, INTERVAL 1 DAY)
             GROUP BY cl.id, cl.nombre, cl.documento
             ORDER BY total_comprado DESC"
        );
        $st->execute(['desde' => $desde, 'hasta' => $hasta]);
    } else {
        $st = $pdo->query('SELECT nombre, documento, pedidos, total_comprado FROM v_clientes_top ORDER BY total_comprado DESC');
    }
    $filas = $st->fetchAll();
    $columnas = ['nombre' => 'Cliente', 'documento' => 'Documento', 'pedidos' => 'Pedidos', 'total_comprado' => 'Total comprado'];
    $columnaTotal = 'total_comprado';
}

$totalGeneral = $columnaTotal ? array_sum(array_column($filas, $columnaTotal)) : null;
$numeroReporte = 'R-' . date('Y') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
$formatoMoneda = fn (float $n) => '$ ' . number_format($n, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulos[$tipo], ENT_QUOTES, 'UTF-8') ?> — ZD.TechLab</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/estilos.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/reporte.css">
</head>
<body>
  <div class="panel">
    <?php require __DIR__ . '/app/vistas/parciales/cabecera.php'; ?>
    <?php require __DIR__ . '/app/vistas/parciales/menu.php'; ?>
    <main class="panel__contenido">
      <h1>Reportes</h1>

      <nav class="reporte__filtros" aria-label="Tipo de reporte">
        <a class="boton <?= $tipo === 'ventas' ? '' : 'boton--secundario' ?>" href="<?= BASE_URL ?>reportes.php?tipo=ventas">Ventas por categoría</a>
        <a class="boton <?= $tipo === 'inventario' ? '' : 'boton--secundario' ?>" href="<?= BASE_URL ?>reportes.php?tipo=inventario">Inventario crítico</a>
        <a class="boton <?= $tipo === 'pedidos' ? '' : 'boton--secundario' ?>" href="<?= BASE_URL ?>reportes.php?tipo=pedidos">Pedidos por cliente</a>
      </nav>

      <?php if ($tipo !== 'inventario'): ?>
        <form method="get" class="reporte__filtros formulario--linea">
          <input type="hidden" name="tipo" value="<?= $tipo ?>">
          <div><label for="desde">Desde</label><input type="date" id="desde" name="desde" value="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>"></div>
          <div><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>"></div>
          <button type="submit" class="boton">Filtrar</button>
          <?php if ($hayFiltro): ?><a href="<?= BASE_URL ?>reportes.php?tipo=<?= $tipo ?>" class="boton boton--secundario">Limpiar</a><?php endif; ?>
        </form>
      <?php endif; ?>

      <div class="reporte__acciones">
        <button type="button" class="boton" onclick="window.print()">Imprimir / Vista previa</button>
        <button type="button" class="boton" id="btn-pdf">Exportar a PDF</button>
        <a class="boton" href="<?= BASE_URL ?>reportes/exportar-csv.php?tipo=<?= $tipo ?>&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>">Exportar a CSV</a>
      </div>

      <article id="reporte">
        <?php require __DIR__ . '/app/vistas/reportes/encabezado.php'; ?>

        <h2><?= htmlspecialchars($titulos[$tipo], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="texto-tenue">
          Filtros: <?= $hayFiltro ? htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') . ' al ' . htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') : 'todo el histórico' ?>
        </p>

        <?php if ($tipo !== 'inventario'): ?>
          <div class="reporte__grafico"><canvas id="g-reporte"></canvas></div>
        <?php endif; ?>

        <table id="tabla-reporte">
          <thead>
            <tr><?php foreach ($columnas as $etiqueta): ?><th scope="col"><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr>
          </thead>
          <tbody>
            <?php if (!$filas): ?>
              <tr><td colspan="<?= count($columnas) ?>">No hay datos para este filtro.</td></tr>
            <?php endif; ?>
            <?php foreach ($filas as $fila): ?>
              <tr>
                <?php foreach ($columnas as $clave => $etiqueta): ?>
                  <td>
                    <?php if (in_array($clave, ['total_vendido', 'total_comprado'], true)): ?>
                      <?= $formatoMoneda((float) $fila[$clave]) ?>
                    <?php else: ?>
                      <?= htmlspecialchars((string) $fila[$clave], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <?php if ($columnaTotal && $filas): ?>
            <tfoot>
              <tr class="reporte__totales">
                <td colspan="<?= count($columnas) - 1 ?>">Total general</td>
                <td><?= $formatoMoneda((float) $totalGeneral) ?></td>
              </tr>
            </tfoot>
          <?php endif; ?>
        </table>

        <p class="reporte__pie">Documento generado automáticamente por ZD.TechLab. Información de uso interno.</p>
      </article>
    </main>
    <?php require __DIR__ . '/app/vistas/parciales/pie.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
      const DATOS_GRAFICO = <?= json_encode([
          'etiquetas' => array_column($filas, array_key_first($columnas)),
          'valores'   => $columnaTotal ? array_map('floatval', array_column($filas, $columnaTotal)) : [],
      ], JSON_UNESCAPED_UNICODE) ?>;
      const TIPO_REPORTE = '<?= $tipo ?>';
    </script>
    <script src="<?= BASE_URL ?>js/reportes.js" defer></script>
    <form id="form-pdf-oculto" method="post" action="<?= BASE_URL ?>reportes/exportar-pdf.php" class="no-imprimir" style="display:none">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="tipo" value="<?= $tipo ?>">
      <input type="hidden" name="desde" value="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="hasta" value="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>">
    </form>
</body>
</html>
