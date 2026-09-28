<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Registrar pedido (transacción), Día 13.
 * El formulario trae hasta 3 líneas de producto+cantidad; las vacías se
 * ignoran. El precio y el stock reales se validan en PedidoModelo::registrar,
 * nunca se confía en lo que venga del navegador.
 */
require_once __DIR__ . '/../seguridad/guardia.php';
require_once __DIR__ . '/../seguridad/csrf.php';
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/PedidoModelo.php';

exigirRol('administrador', 'vendedor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'pedidos.php', true, 303);
    exit;
}

if (!validarCsrf($_POST['csrf'] ?? null)) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Token de seguridad inválido. Vuelve a intentarlo.'];
    header('Location: ' . BASE_URL . 'pedidos.php', true, 303);
    exit;
}

$clienteId = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$productos = $_POST['producto_id'] ?? [];
$cantidades = $_POST['cantidad'] ?? [];

$errores = [];
if (!$clienteId) {
    $errores[] = 'Selecciona un cliente válido.';
}

$items = [];
if (is_array($productos) && is_array($cantidades)) {
    foreach ($productos as $i => $pid) {
        $pid  = filter_var($pid, FILTER_VALIDATE_INT);
        $cant = filter_var($cantidades[$i] ?? null, FILTER_VALIDATE_INT);
        if (!$pid) {
            continue; // línea vacía, se ignora
        }
        if (!$cant || $cant <= 0) {
            $errores[] = 'La cantidad debe ser un número entero mayor que 0.';
            continue;
        }
        $items[] = ['id' => $pid, 'cant' => $cant];
    }
}

if (!$items && !$errores) {
    $errores[] = 'Agrega al menos un producto con cantidad válida.';
}

if ($errores) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => implode(' ', array_unique($errores))];
    header('Location: ' . BASE_URL . 'pedidos.php', true, 303);
    exit;
}

try {
    (new PedidoModelo(Conexion::obtener()))->registrar($clienteId, $items);
    $_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Pedido registrado correctamente.'];
} catch (RuntimeException $e) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => $e->getMessage()];
}

header('Location: ' . BASE_URL . 'pedidos.php', true, 303);
exit;
