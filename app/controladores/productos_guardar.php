<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Guardar producto (crear o actualizar), Día 13.
 * Patrón POST/Redirect/GET: nunca se queda "parado" en un POST.
 */
require_once __DIR__ . '/../seguridad/guardia.php';
require_once __DIR__ . '/../seguridad/csrf.php';
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/ProductoModelo.php';

exigirRol('administrador', 'vendedor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'productos.php', true, 303);
    exit;
}

if (!validarCsrf($_POST['csrf'] ?? null)) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Token de seguridad inválido. Vuelve a intentarlo.'];
    header('Location: ' . BASE_URL . 'productos.php', true, 303);
    exit;
}

$id         = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
$nombre     = trim((string) ($_POST['nombre'] ?? ''));
$categoria  = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
$precio     = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$stock      = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

$errores = [];
if (mb_strlen($nombre) < 3) {
    $errores[] = 'El nombre debe tener al menos 3 caracteres.';
}
if ($categoria === false || $categoria === null) {
    $errores[] = 'Selecciona una categoría válida.';
}
if ($precio === false || $precio === null || $precio <= 0) {
    $errores[] = 'El precio debe ser un número mayor que 0.';
}
if ($stock === false || $stock === null || $stock < 0) {
    $errores[] = 'El stock debe ser un número entero mayor o igual a 0.';
}

if ($errores) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => implode(' ', $errores)];
    header('Location: ' . BASE_URL . 'productos.php' . ($id ? '?editar=' . $id : ''), true, 303);
    exit;
}

$pdo    = Conexion::obtener();
$modelo = new ProductoModelo($pdo);
$datos  = ['nombre' => $nombre, 'categoria_id' => $categoria, 'precio' => $precio, 'stock' => $stock];

if ($id) {
    $modelo->actualizar($id, $datos);
    $_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Producto actualizado correctamente.'];
} else {
    $modelo->crear($datos);
    $_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Producto creado correctamente.'];
}

header('Location: ' . BASE_URL . 'productos.php', true, 303);
exit;
