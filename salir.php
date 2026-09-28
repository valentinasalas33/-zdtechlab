<?php
declare(strict_types=1);

require_once __DIR__ . '/app/seguridad/sesion.php';

iniciarSesionSegura();
cerrarSesion();

header('Location: login.php');
exit;
