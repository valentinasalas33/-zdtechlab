<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Guardar cliente (crear o actualizar), Día 13.
 */
require_once __DIR__ . '/../seguridad/guardia.php';
require_once __DIR__ . '/../seguridad/csrf.php';
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/ClienteModelo.php';

exigirRol('administrador', 'vendedor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'clientes.php', true, 303);
    exit;
}

if (!validarCsrf($_POST['csrf'] ?? null)) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Token de seguridad inválido. Vuelve a intentarlo.'];
    header('Location: ' . BASE_URL . 'clientes.php', true, 303);
    exit;
}

$id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
$nombre    = trim((string) ($_POST['nombre'] ?? ''));
$documento = trim((string) ($_POST['documento'] ?? ''));
$correo    = trim((string) ($_POST['correo'] ?? ''));

$modelo  = new ClienteModelo(Conexion::obtener());
$errores = [];

if (mb_strlen($nombre) < 3) {
    $errores[] = 'El nombre debe tener al menos 3 caracteres.';
}
if ($documento === '' || !preg_match('/^[0-9A-Za-z-]{4,30}$/', $documento)) {
    $errores[] = 'El documento debe tener entre 4 y 30 caracteres (letras, números o guiones).';
} elseif ($modelo->existeDocumento($documento, $id)) {
    $errores[] = 'Ya existe un cliente registrado con ese documento.';
}
if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo no tiene un formato válido.';
}

if ($errores) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => implode(' ', $errores)];
    header('Location: ' . BASE_URL . 'clientes.php' . ($id ? '?editar=' . $id : ''), true, 303);
    exit;
}

$datos = ['nombre' => $nombre, 'documento' => $documento, 'correo' => $correo !== '' ? $correo : null];

if ($id) {
    $modelo->actualizar($id, $datos);
    $_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Cliente actualizado correctamente.'];
} else {
    $modelo->crear($datos);
    $_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Cliente creado correctamente.'];
}

header('Location: ' . BASE_URL . 'clientes.php', true, 303);
exit;
