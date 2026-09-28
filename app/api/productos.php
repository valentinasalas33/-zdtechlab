<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/ProductoModelo.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = Conexion::obtener();
$texto = $_GET['buscar'] ?? '';

$productos = buscarProductos($pdo, $texto);

echo json_encode($productos);
