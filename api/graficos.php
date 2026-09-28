<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Datos para los gráficos del tablero (Día 14)
 * Protegido por el mismo guardián de sesión: sin sesión activa no
 * devuelve datos (guardia.php redirige a login.php).
 * Las consultas SIEMPRE leen de las vistas (v_ventas_mes, v_ventas_categoria),
 * nunca repiten el JOIN/GROUP BY aquí.
 */
require_once __DIR__ . '/../app/seguridad/guardia.php';
require_once __DIR__ . '/../app/config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = Conexion::obtener();

// Filtro opcional de rango de fechas (formato YYYY-MM-DD desde <input type="date">)
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$hayFiltro = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta);

if ($hayFiltro) {
    $st = $pdo->prepare(
        "SELECT periodo, cantidad_pedidos, total_vendido FROM v_ventas_mes
         WHERE periodo BETWEEN DATE_FORMAT(:desde, '%Y-%m') AND DATE_FORMAT(:hasta, '%Y-%m')
         ORDER BY periodo"
    );
    $st->execute(['desde' => $desde, 'hasta' => $hasta]);
} else {
    $st = $pdo->query('SELECT periodo, cantidad_pedidos, total_vendido FROM v_ventas_mes ORDER BY periodo DESC LIMIT 12');
}
$meses = $hayFiltro ? $st->fetchAll() : array_reverse($st->fetchAll());

$categorias = $pdo->query(
    'SELECT categoria, total_vendido FROM v_ventas_categoria WHERE total_vendido > 0 ORDER BY total_vendido DESC'
)->fetchAll();

echo json_encode([
    'ventasMes' => [
        'etiquetas' => array_column($meses, 'periodo'),
        'valores'   => array_map('floatval', array_column($meses, 'total_vendido')),
    ],
    'pedidosMes' => [
        'etiquetas' => array_column($meses, 'periodo'),
        'valores'   => array_map('intval', array_column($meses, 'cantidad_pedidos')),
    ],
    'categorias' => [
        'etiquetas' => array_column($categorias, 'categoria'),
        'valores'   => array_map('floatval', array_column($categorias, 'total_vendido')),
    ],
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
