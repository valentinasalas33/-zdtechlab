<?php
declare(strict_types=1);

require_once __DIR__ . '/app/seguridad/guardia.php';

$usuario = $_SESSION['usuario'];
$iniciales = mb_strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr(strrchr($usuario['nombre'], ' ') ?: '', 1, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tablero — ZD.TechLab</title>
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
  <div class="panel">
    <header class="panel__barra">
      <button type="button" class="boton-menu" aria-label="Abrir menú" aria-expanded="false">☰</button>
      <img src="assets/img/logo.svg" alt="Logo de ZD.TechLab" width="120">
      <div class="panel__usuario">
        <span class="avatar"><?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?></span>
        <span><?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars(ucfirst($usuario['rol']), ENT_QUOTES, 'UTF-8') ?></small></span>
      </div>
    </header>
    <aside class="panel__menu" id="menu-lateral">
      <nav aria-label="Menú principal">
        <ul class="menu">
          <li><a href="dashboard.php" class="menu__enlace menu__enlace--activo" aria-current="page">Tablero</a></li>
          <li><a href="productos.php" class="menu__enlace">Productos</a></li>
          <li><a href="#" class="menu__enlace">Categorías</a></li>
          <li><a href="#" class="menu__enlace">Clientes</a></li>
          <li><a href="#" class="menu__enlace">Pedidos</a></li>
          <li><a href="#" class="menu__enlace">Reportes</a></li>
          <?php if (puede('administrador')): ?>
            <li><a href="usuarios.php" class="menu__enlace">Usuarios<br><small>solo administrador</small></a></li>
          <?php else: ?>
            <li><a href="usuarios.php" class="menu__enlace menu__enlace--restringido">Usuarios<br><small>solo administrador</small></a></li>
          <?php endif; ?>
        </ul>
        <a href="salir.php" class="menu__salir">Cerrar sesión</a>
      </nav>
    </aside>
    <main class="panel__contenido">
      <h1>Resumen general</h1>
      <p class="texto-tenue">Periodo: septiembre de 2026</p>
      <section class="indicadores" aria-label="Indicadores clave">
        <article class="tarjeta tarjeta--verde"><p class="tarjeta__rotulo">Productos activos</p><p class="tarjeta__valor">128</p></article>
        <article class="tarjeta tarjeta--azul"><p class="tarjeta__rotulo">Pedidos del mes</p><p class="tarjeta__valor">47</p></article>
        <article class="tarjeta tarjeta--naranja"><p class="tarjeta__rotulo">Ventas del mes</p><p class="tarjeta__valor">$ 18.4 M</p></article>
        <article class="tarjeta tarjeta--error"><p class="tarjeta__rotulo">Stock crítico</p><p class="tarjeta__valor">9</p></article>
      </section>
      <section class="graficos" aria-label="Gráficos de ventas">
        <div class="tarjeta-grafico"><h2>Ventas por mes</h2><canvas id="g-ventas" height="220" aria-label="Gráfico de barras de ventas mensuales" role="img"></canvas></div>
        <div class="tarjeta-grafico"><h2>Ventas por categoría</h2><canvas id="g-categorias" height="220" aria-label="Gráfico de dona de ventas por categoría" role="img"></canvas></div>
      </section>
    </main>
    <footer class="panel__pie"><p>ZD.TechLab — Panel de gestión</p></footer>
  </div>
</body>
</html>
