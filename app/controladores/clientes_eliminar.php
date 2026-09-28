<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Eliminar cliente (borrado real), Día 13.
 * A propósito NO es borrado lógico: si el cliente tiene pedidos, la llave
 * foránea (ON DELETE RESTRICT) rechaza el borrado y aquí se traduce en un
 * aviso claro en vez de una pantalla de error de MySQL.
 */
require_once __DIR__ . '/../seguridad/guardia.php';
require_once __DIR__ . '/../seguridad/csrf.php';
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/ClienteModelo.php';

exigirRol('administrador', 'vendedor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrf($_POST['csrf'] ?? null)) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Solicitud inválida.'];
    header('Location: ' . BASE_URL . 'clientes.php', true, 303);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'Cliente no válido.'];
    header('Location: ' . BASE_URL . 'clientes.php', true, 303);
    exit;
}

try {
    (new ClienteModelo(Conexion::obtener()))->eliminar($id);
    $_SESSION['aviso'] = ['tipo' => 'exito', 'texto' => 'Cliente eliminado correctamente.'];
} catch (PDOException $e) {
    // Código 23000 = violación de restricción de integridad (FK)
    $_SESSION['aviso'] = ['tipo' => 'error', 'texto' => 'No se puede eliminar: el cliente tiene pedidos registrados.'];
}

header('Location: ' . BASE_URL . 'clientes.php', true, 303);
exit;
