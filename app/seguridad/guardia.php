<?php
declare(strict_types=1);

require_once __DIR__ . '/sesion.php';
iniciarSesionSegura();

/* 1. ¿Hay sesión? */
if (empty($_SESSION['usuario'])) {
    header('Location: login.php?m=requiere_ingreso');
    exit;
}

/* 2. ¿Es el mismo navegador? */
if (($_SESSION['huella'] ?? '') !== hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
    cerrarSesion();
    header('Location: login.php?m=sesion_invalida');
    exit;
}

/* 3. Inactividad y duración máxima */
$ahora = time();
if ($ahora - $_SESSION['ultima_actividad'] > INACTIVIDAD_MAX
    || $ahora - $_SESSION['inicio'] > SESION_MAX) {
    cerrarSesion();
    header('Location: login.php?m=sesion_expirada');
    exit;
}
$_SESSION['ultima_actividad'] = $ahora;

/* 4. Autorización por rol */
function exigirRol(string ...$roles): void
{
    if (!in_array($_SESSION['usuario']['rol'], $roles, true)) {
        http_response_code(403);
        exit('403 — No tiene permiso para esta operación.');
    }
}

function puede(string ...$roles): bool
{
    return in_array($_SESSION['usuario']['rol'] ?? '', $roles, true);
}
