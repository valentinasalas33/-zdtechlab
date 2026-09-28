<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Gestión de usuarios (Día 11)
 * Página de prueba para la Actividad 4: solo el administrador puede
 * entrar aquí, incluso escribiendo la URL directamente. El módulo
 * completo (listado, edición) se construye en días posteriores.
 */

require_once __DIR__ . '/app/seguridad/guardia.php';
exigirRol('administrador'); // 403 para vendedor o consultor

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Usuarios — ZD.TechLab</title>
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
  <div class="panel">
    <header class="panel__barra">
      <button type="button" class="boton-menu" aria-label="Abrir menú" aria-expanded="false">☰</button>
      <img src="assets/img/logo.svg" alt="Logo de ZD.TechLab" width="120">
      <div class="panel__usuario">
        <span class="avatar"><?= htmlspecialchars(mb_substr($usuario['nombre'], 0, 2), ENT_QUOTES, 'UTF-8') ?></span>
        <span><?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars(ucfirst($usuario['rol']), ENT_QUOTES, 'UTF-8') ?></small></span>
      </div>
    </header>
    <aside class="panel__menu" id="menu-lateral">
      <nav aria-label="Menú principal">
        <ul class="menu">
          <li><a href="dashboard.php" class="menu__enlace">Tablero</a></li>
          <li><a href="productos.php" class="menu__enlace">Productos</a></li>
          <li><a href="#" class="menu__enlace">Categorías</a></li>
          <li><a href="#" class="menu__enlace">Clientes</a></li>
          <li><a href="#" class="menu__enlace">Pedidos</a></li>
          <li><a href="#" class="menu__enlace">Reportes</a></li>
          <li><a href="usuarios.php" class="menu__enlace menu__enlace--activo" aria-current="page">Usuarios<br><small>solo administrador</small></a></li>
        </ul>
        <a href="salir.php" class="menu__salir">Cerrar sesión</a>
      </nav>
    </aside>
    <main class="panel__contenido">
      <h1>Usuarios</h1>
      <p class="texto-tenue">Este módulo se completa en un día posterior del plan (listado, creación y edición). Por ahora, esta página solo demuestra que únicamente el rol administrador puede llegar hasta acá.</p>
    </main>
    <footer class="panel__pie"><p>ZD.TechLab — Panel de gestión</p></footer>
  </div>
</body>
</html>
