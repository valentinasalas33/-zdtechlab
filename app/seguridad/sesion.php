<?php
declare(strict_types=1);

const INACTIVIDAD_MAX = 60;  // 60 segundos
const SESION_MAX       = 28800; // 8 horas

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,                     // JavaScript no puede leerla
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Strict',
    ]);
    session_name('ZDTL_SESS');
    session_start();
}

function abrirSesion(array $u): void
{
    iniciarSesionSegura();
    session_regenerate_id(true); // nuevo ID: evita fijación de sesión

    $_SESSION['usuario'] = [
        'id'     => (int) $u['id'],
        'nombre' => $u['nombre'],
        'rol'    => $u['rol'],
    ];
    $_SESSION['inicio']           = time();
    $_SESSION['ultima_actividad'] = time();
    $_SESSION['huella']           = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

function cerrarSesion(): void
{
    iniciarSesionSegura();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }
    session_destroy();
}
