<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Exportar reporte a PDF (Día 15)
 * Genera el mismo HTML del reporte (encabezado + tabla + totales) y lo
 * convierte a PDF con Dompdf. El gráfico no se incluye en el PDF para
 * mantenerlo simple; la tabla y los totales sí, que es lo que exige la
 * evidencia del día.
 */
require_once __DIR__ . '/../app/seguridad/guardia.php';
require_once __DIR__ . '/../app/seguridad/csrf.php';
require_once __DIR__ . '/../app/config/conexion.php';

exigirRol('administrador', 'consultor');

if (!validarCsrf($_POST['csrf'] ?? null)) {
    http_response_code(419);
    exit('Token de seguridad inválido.');
}

$pdo   = Conexion::obtener();
$tipo  = $_POST['tipo'] ?? 'ventas';
$tipo  = in_array($tipo, ['ventas', 'inventario', 'pedidos'], true) ? $tipo : 'ventas';
$desde = $_POST['desde'] ?? '';
$hasta = $_POST['hasta'] ?? '';
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
             GROUP BY c.id, c.nombre ORDER BY total_vendido DESC"
        );
        $st->execute(['desde' => $desde, 'hasta' => $hasta]);
    } else {
        $st = $pdo->query('SELECT categoria, unidades, total_vendido FROM v_ventas_categoria ORDER BY total_vendido DESC');
    }
    $filas = $st->fetchAll();
    $columnas = ['categoria' => 'Categoría', 'unidades' => 'Unidades', 'total_vendido' => 'Total vendido'];
    $columnaTotal = 'total_vendido';
} elseif ($tipo === 'inventario') {
    $filas = $pdo->query('SELECT nombre, categoria, stock, stock_minimo FROM v_stock_critico ORDER BY stock ASC')->fetchAll();
    $columnas = ['nombre' => 'Producto', 'categoria' => 'Categoría', 'stock' => 'Stock', 'stock_minimo' => 'Stock mínimo'];
    $columnaTotal = null;
} else {
    if ($hayFiltro) {
        $st = $pdo->prepare(
            "SELECT cl.nombre, cl.documento, COUNT(DISTINCT p.id) AS pedidos, COALESCE(SUM(p.total),0) AS total_comprado
             FROM clientes cl
             LEFT JOIN pedidos p ON p.cliente_id = cl.id AND p.estado='confirmado' AND p.fecha BETWEEN :desde AND DATE_ADD(:hasta, INTERVAL 1 DAY)
             GROUP BY cl.id, cl.nombre, cl.documento ORDER BY total_comprado DESC"
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

ob_start();
?>
<html>
<head>
<meta charset="UTF-8">
<style>
  body{ font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color:#222; }
  .reporte__encabezado{ display:flex; align-items:center; gap:12px; border-bottom:2.5px solid #39A900; padding-bottom:8px; margin-bottom:12px; }
  .reporte__logo{ width:48px; height:48px; }
  .reporte__marca h1{ margin:0; font-size:14pt; }
  .reporte__marca p{ margin:2px 0; font-size:8.5pt; color:#666; }
  .reporte__meta{ margin-left:auto; text-align:right; font-size:8pt; color:#666; }
  table{ width:100%; border-collapse:collapse; margin-top:8px; }
  th, td{ padding:6px 8px; border-bottom:1px solid #ddd; text-align:left; }
  thead{ background:#39A900; color:#fff; }
  tfoot td{ font-weight:bold; background:#eef4ea; }
</style>
</head>
<body>
  <div class="reporte__encabezado">
    <img class="reporte__logo" src="<?= 'data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/../assets/img/logo.png')) ?>" alt="Logo">
    <div class="reporte__marca">
      <h1>ZD.TechLab</h1>
      <p>Sistema de gestión de tienda tecnológica · v1.0</p>
      <p>NIT 900.123.456-7 · Bogotá D.C. · soporte@zdtechlab.co</p>
    </div>
    <div class="reporte__meta">
      <p>Generado: <?= date('d/m/Y H:i') ?></p>
      <p>Usuario: <?= htmlspecialchars($_SESSION['usuario']['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
      <p>Reporte N.° <?= $numeroReporte ?></p>
    </div>
  </div>
  <h2><?= htmlspecialchars($titulos[$tipo], ENT_QUOTES, 'UTF-8') ?></h2>
  <p>Filtros: <?= $hayFiltro ? htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') . ' al ' . htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') : 'todo el histórico' ?></p>
  <table>
    <thead><tr><?php foreach ($columnas as $etiqueta): ?><th><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach ($filas as $fila): ?>
        <tr>
          <?php foreach ($columnas as $clave => $etiqueta): ?>
            <td><?= in_array($clave, ['total_vendido', 'total_comprado'], true) ? $formatoMoneda((float) $fila[$clave]) : htmlspecialchars((string) $fila[$clave], ENT_QUOTES, 'UTF-8') ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <?php if ($columnaTotal && $filas): ?>
      <tfoot><tr><td colspan="<?= count($columnas) - 1 ?>">Total general</td><td><?= $formatoMoneda((float) $totalGeneral) ?></td></tr></tfoot>
    <?php endif; ?>
  </table>
  <p style="margin-top:16px;font-size:7.5pt;color:#888;">Documento generado automáticamente por ZD.TechLab. Información de uso interno.</p>
</body>
</html>
<?php
$html = ob_get_clean();

require_once __DIR__ . '/../vendor/autoload.php';

$dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('reporte-' . $tipo . '-' . date('Ymd-Hi') . '.pdf', ['Attachment' => false]);
