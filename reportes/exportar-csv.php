<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Exportar reporte a CSV (Día 15)
 * Marcador BOM al inicio para que Excel respete acentos y ñ.
 */
require_once __DIR__ . '/../app/seguridad/guardia.php';
require_once __DIR__ . '/../app/config/conexion.php';

exigirRol('administrador', 'consultor');

$pdo   = Conexion::obtener();
$tipo  = $_GET['tipo'] ?? 'ventas';
$tipo  = in_array($tipo, ['ventas', 'inventario', 'pedidos'], true) ? $tipo : 'ventas';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$hayFiltro = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta);

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
    $encabezados = ['Categoría', 'Unidades', 'Total vendido'];
} elseif ($tipo === 'inventario') {
    $filas = $pdo->query('SELECT nombre, categoria, stock, stock_minimo FROM v_stock_critico ORDER BY stock ASC')->fetchAll();
    $encabezados = ['Producto', 'Categoría', 'Stock', 'Stock mínimo'];
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
    $encabezados = ['Cliente', 'Documento', 'Pedidos', 'Total comprado'];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="reporte-' . $tipo . '-' . date('Ymd') . '.csv"');

$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF"); // BOM: acentos correctos en Excel
fputcsv($salida, $encabezados, ';');
foreach ($filas as $fila) {
    fputcsv($salida, array_values($fila), ';');
}
fclose($salida);
