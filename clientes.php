<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Clientes (Día 13: CRUD completo, borrado real con restricción FK)
 */
require_once __DIR__ . '/app/seguridad/guardia.php';
require_once __DIR__ . '/app/seguridad/csrf.php';
require_once __DIR__ . '/app/config/rutas.php';
require_once __DIR__ . '/app/config/conexion.php';
require_once __DIR__ . '/app/modelos/ClienteModelo.php';

exigirRol('administrador', 'vendedor');

$pdo    = Conexion::obtener();
$modelo = new ClienteModelo($pdo);

$busqueda  = trim((string) ($_GET['b'] ?? ''));
$pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 5;

$clientes     = $modelo->listar($busqueda, $pagina, $porPagina);
$total        = $modelo->contar($busqueda);
$totalPaginas = max(1, (int) ceil($total / $porPagina));

$editando = null;
if (isset($_GET['editar'])) {
    $editando = $modelo->buscarPorId((int) $_GET['editar']);
}

$aviso = $_SESSION['aviso'] ?? null;
unset($_SESSION['aviso']);

function conQueryClientes(array $cambios): string
{
    $actual = $_GET;
    foreach ($cambios as $k => $v) {
        $actual[$k] = $v;
    }
    return '?' . http_build_query($actual);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clientes — ZD.TechLab</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/estilos.css">
</head>
<body>
  <div class="panel">
    <?php require __DIR__ . '/app/vistas/parciales/cabecera.php'; ?>
    <?php require __DIR__ . '/app/vistas/parciales/menu.php'; ?>
    <main class="panel__contenido">
      <h1>Clientes</h1>

      <?php if ($aviso): ?>
        <p class="alerta alerta-<?= $aviso['tipo'] === 'exito' ? 'exito' : 'error' ?>">
          <?= htmlspecialchars($aviso['texto'], ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>

      <section aria-labelledby="titulo-formulario">
        <h2 id="titulo-formulario"><?= $editando ? 'Editar cliente' : 'Registrar cliente' ?></h2>
        <form method="post" action="<?= BASE_URL ?>app/controladores/clientes_guardar.php" novalidate class="formulario formulario--linea">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
          <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) $editando['id'] ?>">
          <?php endif; ?>
          <div>
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" minlength="3" required
                   value="<?= htmlspecialchars($editando['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="documento">Documento</label>
            <input type="text" id="documento" name="documento" minlength="4" required
                   value="<?= htmlspecialchars($editando['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="correo">Correo (opcional)</label>
            <input type="email" id="correo" name="correo"
                   value="<?= htmlspecialchars($editando['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <button type="submit" class="boton"><?= $editando ? 'Guardar cambios' : 'Guardar' ?></button>
          <?php if ($editando): ?>
            <a href="<?= BASE_URL ?>clientes.php" class="boton boton--secundario">Cancelar</a>
          <?php endif; ?>
        </form>
      </section>

      <section aria-labelledby="titulo-tabla">
        <h2 id="titulo-tabla">Listado de clientes</h2>

        <form method="get" class="formulario--linea" style="margin-bottom:1rem;">
          <div>
            <label for="b">Buscar por nombre o documento</label>
            <input type="search" id="b" name="b" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <button type="submit" class="boton">Buscar</button>
          <?php if ($busqueda !== ''): ?>
            <a href="<?= BASE_URL ?>clientes.php" class="boton boton--secundario">Limpiar</a>
          <?php endif; ?>
        </form>

        <table id="tabla-clientes">
          <caption>Clientes registrados (<?= $total ?> en total)</caption>
          <thead>
            <tr>
              <th scope="col">Nombre</th>
              <th scope="col">Documento</th>
              <th scope="col">Correo</th>
              <th scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$clientes): ?>
              <tr><td colspan="4">No hay clientes para mostrar.</td></tr>
            <?php endif; ?>
            <?php foreach ($clientes as $c): ?>
              <tr>
                <td><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['documento'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <a href="<?= conQueryClientes(['editar' => $c['id']]) ?>" class="boton boton--pequeno">Editar</a>
                  <form method="post" action="<?= BASE_URL ?>app/controladores/clientes_eliminar.php"
                        style="display:inline" onsubmit="return confirm('¿Eliminar este cliente?');">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                    <button type="submit" class="boton boton--pequeno boton--peligro">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
          <nav aria-label="Paginación de clientes">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
              <a href="<?= conQueryClientes(['pagina' => $i]) ?>" <?= $i === $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      </section>
    </main>
    <?php require __DIR__ . '/app/vistas/parciales/pie.php'; ?>
</body>
</html>
