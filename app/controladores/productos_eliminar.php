<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Desactivar producto (borrado lógico), Día 13.
 * Siempre por POST (nunca un enlace GET) para que no se borre solo con
 * precargar o compartir la URL.
 */
require_once __DIR__ . '/../seguridad/guardia.php';
require_once __DIR__ . '/../seguridad/csrf.php';
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/ProductoModelo.php';

exigirRol('administrador', 'vendedor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrf($_POST['csrf'] ?? null)) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Solicitud inválida.'];
    header('Location: ' . BASE_URL . 'productos.php', true, 303);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Producto no válido.'];
    header('Location: ' . BASE_URL . 'productos.php', true, 303);
    exit;
}

(new ProductoModelo(Conexion::obtener()))->desactivar($id);
$_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Producto desactivado. Sigue visible en el historial de pedidos.'];
header('Location: ' . BASE_URL . 'productos.php', true, 303);
exit;
