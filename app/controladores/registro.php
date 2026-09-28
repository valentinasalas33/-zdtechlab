<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../seguridad/csrf.php';
require_once __DIR__ . '/../seguridad/sesion.php';
iniciarSesionSegura();

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('Solicitud no válida. Recargue el formulario.');
    }

    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $clave  = (string) ($_POST['clave'] ?? '');
    $rol    = (string) ($_POST['rol'] ?? '');

    $rolesValidos = ['administrador', 'vendedor', 'consultor'];

    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($clave) < 8 || !in_array($rol, $rolesValidos, true)) {
        $error = 'Revise los datos: correo válido, contraseña de mínimo 8 caracteres y un rol permitido.';
    } else {
        $pdo = Conexion::obtener();

        $existe = $pdo->prepare('SELECT id FROM usuarios WHERE correo = :correo LIMIT 1');
        $existe->execute(['correo' => $correo]);

        if ($existe->fetch()) {
            $error = 'Ya existe un usuario con ese correo.';
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);

            $insertar = $pdo->prepare(
                'INSERT INTO usuarios (nombre, correo, clave_hash, rol) VALUES (:nombre, :correo, :hash, :rol)'
            );
            $insertar->execute([
                'nombre' => $nombre,
                'correo' => $correo,
                'hash'   => $hash,
                'rol'    => $rol,
            ]);

            $exito = 'Usuario creado correctamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro de usuarios — ZD.TechLab</title>
  <link rel="stylesheet" href="../../css/tokens.css">
  <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body class="cuerpo-ingreso">
  <main class="pantalla-ingreso">
    <img src="../../assets/img/logo.svg" alt="Logo de ZD.TechLab" width="140" class="pantalla-ingreso__logo">
    <h1>Crear usuario</h1>

    <?php if ($error !== ''): ?>
      <p class="alerta alerta-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($exito !== ''): ?>
      <p class="alerta alerta-exito" role="status"><?= htmlspecialchars($exito, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="registro.php" class="formulario" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
      <fieldset>
        <legend>Datos del usuario</legend>
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required>
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" required>
        <label for="clave">Contraseña</label>
        <input type="password" id="clave" name="clave" required minlength="8">
        <label for="rol">Rol</label>
        <select id="rol" name="rol" required>
          <option value="administrador">Administrador</option>
          <option value="vendedor">Vendedor</option>
          <option value="consultor">Consultor</option>
        </select>
      </fieldset>
      <button type="submit" class="boton">Crear usuario</button>
    </form>
  </main>
</body>
</html>
