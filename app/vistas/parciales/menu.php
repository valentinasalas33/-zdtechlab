<?php
declare(strict_types=1);

$paginaActual = basename($_SERVER['PHP_SELF']);

$opciones = [
    ['archivo' => 'dashboard.php',  'texto' => 'Tablero',     'roles' => ['administrador', 'vendedor', 'consultor']],
    ['archivo' => 'productos.php',  'texto' => 'Productos',   'roles' => ['administrador', 'vendedor', 'consultor']],
    ['archivo' => 'categorias.php', 'texto' => 'Categorías',  'roles' => ['administrador', 'vendedor']],
    ['archivo' => 'clientes.php',   'texto' => 'Clientes',    'roles' => ['administrador', 'vendedor']],
    ['archivo' => 'pedidos.php',    'texto' => 'Pedidos',     'roles' => ['administrador', 'vendedor']],
    ['archivo' => 'reportes.php',   'texto' => 'Reportes',    'roles' => ['administrador', 'consultor']],
    ['archivo' => 'usuarios.php',   'texto' => 'Usuarios',    'roles' => ['administrador']],
];
?>
<aside class="panel__menu" id="menu-lateral">
  <nav aria-label="Menú principal">
    <ul class="menu">
      <?php foreach ($opciones as $op): ?>
        <?php if (!puede(...$op['roles'])) continue; ?>
        <?php $activo = ($paginaActual === $op['archivo']); ?>
        <li>
          <a href="<?= BASE_URL . $op['archivo'] ?>"
             class="menu__enlace<?= $activo ? ' menu__enlace--activo' : '' ?>"
             <?= $activo ? 'aria-current="page"' : '' ?>>
            <?= htmlspecialchars($op['texto'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <a href="<?= BASE_URL ?>salir.php" class="menu__salir">Cerrar sesión</a>
  </nav>
</aside>
