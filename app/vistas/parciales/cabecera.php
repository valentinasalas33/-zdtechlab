<?php
declare(strict_types=1);

$usuario   = $_SESSION['usuario'];
$iniciales = mb_strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr(strrchr($usuario['nombre'], ' ') ?: '', 1, 1));
?>
<header class="panel__barra">
  <button type="button" class="boton-menu" aria-label="Abrir menú" aria-expanded="false">☰</button>
  <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Logo de ZD.TechLab" width="120">
  <?php if (!empty($conBuscador)): ?>
    <input type="search" placeholder="Buscar..." aria-label="Buscar productos" id="buscador">
  <?php endif; ?>
  <div class="panel__usuario">
    <span class="avatar"><?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?></span>
    <span><?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars(ucfirst($usuario['rol']), ENT_QUOTES, 'UTF-8') ?></small></span>
  </div>
</header>
