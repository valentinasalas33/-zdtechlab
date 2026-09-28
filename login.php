<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/conexion.php';
require_once __DIR__ . '/app/seguridad/csrf.php';
session_start();

const MAX_INTENTOS  = 5;
const MINUTOS_LAPSO = 15;

function registrarIntento(PDO $pdo, string $correo, bool $exitoso): void
{
    $pdo->prepare('INSERT INTO intentos_acceso (correo, exitoso) VALUES (:correo, :exitoso)')
        ->execute(['correo' => $correo, 'exitoso' => $exitoso ? 1 : 0]);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validarCsrf($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('Solicitud no válida. Recargue el formulario.');
    }

    $correo = trim((string) ($_POST['correo'] ?? ''));
    $clave  = (string) ($_POST['clave'] ?? '');

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($clave) < 8) {
        $error = 'Correo o contraseña incorrectos.';
    } else {
        $pdo = Conexion::obtener();

        $st = $pdo->prepare(
            'SELECT id, nombre, clave_hash, rol, activo, bloqueado_hasta
             FROM usuarios WHERE correo = :correo LIMIT 1'
        );
        $st->execute(['correo' => $correo]);
        $u = $st->fetch();

        if ($u && $u['bloqueado_hasta'] !== null && strtotime($u['bloqueado_hasta']) > time()) {
            $error = 'Cuenta bloqueada temporalmente. Intente más tarde.';

        } elseif ($u && (int) $u['activo'] === 1 && password_verify($clave, $u['clave_hash'])) {

            if (password_needs_rehash($u['clave_hash'], PASSWORD_DEFAULT)) {
                $nuevo = password_hash($clave, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE usuarios SET clave_hash = :h WHERE id = :id')
                    ->execute(['h' => $nuevo, 'id' => $u['id']]);
            }

            registrarIntento($pdo, $correo, true);

            $_SESSION['usuario'] = [
                'id'     => (int) $u['id'],
                'nombre' => $u['nombre'],
                'rol'    => $u['rol'],
            ];

            header('Location: dashboard.html');
            exit;

        } else {
            if ($u) {
                registrarIntento($pdo, $correo, false);

                $fallidos = $pdo->prepare(
                    'SELECT COUNT(*) AS total FROM intentos_acceso
                     WHERE correo = :correo AND exitoso = 0
                       AND creado_en >= (NOW() - INTERVAL :minutos MINUTE)'
                );
                $fallidos->bindValue(':correo', $correo);
                $fallidos->bindValue(':minutos', MINUTOS_LAPSO, PDO::PARAM_INT);
                $fallidos->execute();

                if ((int) $fallidos->fetch()['total'] >= MAX_INTENTOS) {
                    $hasta = date('Y-m-d H:i:s', time() + MINUTOS_LAPSO * 60);
                    $pdo->prepare('UPDATE usuarios SET bloqueado_hasta = :fecha WHERE id = :id')
                        ->execute(['fecha' => $hasta, 'id' => $u['id']]);
                }
            }
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ingreso — ZD.TechLab</title>
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="cuerpo-ingreso">
  <main class="pantalla-ingreso">
    <img src="assets/img/logo.svg" alt="Logo de ZD.TechLab" width="140" class="pantalla-ingreso__logo">
    <h1>Ingreso al panel de gestión</h1>

    <?php if ($error !== ''): ?>
      <p class="alerta alerta-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="login.php" method="post" novalidate class="formulario" autocomplete="on">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
      <fieldset>
        <legend>Credenciales</legend>
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" required autocomplete="username">
        <label for="clave">Contraseña</label>
        <input type="password" id="clave" name="clave" required autocomplete="current-password" minlength="8">
      </fieldset>
      <button type="submit" class="boton">Iniciar sesión</button>
    </form>
  </main>
</body>
</html>
