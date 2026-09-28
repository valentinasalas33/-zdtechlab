<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Productos (Día 13: CRUD completo renderizado en el servidor)
 */
require_once __DIR__ . '/app/seguridad/guardia.php';
require_once __DIR__ . '/app/seguridad/csrf.php';
require_once __DIR__ . '/app/config/rutas.php';
require_once __DIR__ . '/app/config/conexion.php';
require_once __DIR__ . '/app/modelos/ProductoModelo.php';

$pdo    = Conexion::obtener();
$modelo = new ProductoModelo($pdo);

$busqueda = trim((string) ($_GET['b'] ?? ''));
$orden    = (string) ($_GET['orden'] ?? 'nombre');
$pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 5;

$productos  = $modelo->listar($busqueda, $pagina, $porPagina, $orden);
$total      = $modelo->contar($busqueda);
$totalPaginas = max(1, (int) ceil($total / $porPagina));

$categorias = $pdo->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();

$editando = null;
if (isset($_GET['editar'])) {
    $editando = $modelo->buscarPorId((int) $_GET['editar']);
}

$aviso = $_SESSION['aviso'] ?? null;
unset($_SESSION['aviso']);

function conQuery(array $cambios): string
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
  <title>Productos — ZD.TechLab</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/estilos.css">
</head>
<body>
  <div class="panel">
    <?php require __DIR__ . '/app/vistas/parciales/cabecera.php'; ?>
    <?php require __DIR__ . '/app/vistas/parciales/menu.php'; ?>
    <main class="panel__contenido">
      <h1>Productos</h1>

      <?php if ($aviso): ?>
        <p class="alerta alerta-<?= $aviso['tipo'] === 'exito' ? 'exito' : 'error' ?>">
          <?= htmlspecialchars($aviso['texto'], ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>

      <section aria-labelledby="titulo-formulario">
        <h2 id="titulo-formulario"><?= $editando ? 'Editar producto' : 'Registrar producto' ?></h2>
        <form method="post" action="<?= BASE_URL ?>app/controladores/productos_guardar.php" novalidate class="formulario formulario--linea">
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
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (($editando['categoria_id'] ?? null) == $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="precio">Precio</label>
            <input type="number" id="precio" name="precio" min="1" step="1" required
                   value="<?= htmlspecialchars((string) ($editando['precio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div>
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" step="1" required
                   value="<?= htmlspecialchars((string) ($editando['stock'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <button type="submit" class="boton"><?= $editando ? 'Guardar cambios' : 'Guardar' ?></button>
          <?php if ($editando): ?>
            <a href="<?= BASE_URL ?>productos.php" class="boton boton--secundario">Cancelar</a>
          <?php endif; ?>
        </form>
      </section>

      <section aria-labelledby="titulo-tabla">
        <h2 id="titulo-tabla">Listado de productos</h2>

        <form method="get" class="formulario--linea" style="margin-bottom:1rem;">
          <div>
            <label for="b">Buscar por nombre</label>
            <input type="search" id="b" name="b" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <button type="submit" class="boton">Buscar</button>
          <?php if ($busqueda !== ''): ?>
            <a href="<?= BASE_URL ?>productos.php" class="boton boton--secundario">Limpiar</a>
          <?php endif; ?>
        </form>

        <table id="tabla-productos">
          <caption>Productos activos en inventario (<?= $total ?> en total)</caption>
          <thead>
            <tr>
              <th scope="col"><a href="<?= conQuery(['orden' => 'nombre']) ?>">Nombre</a></th>
              <th scope="col">Categoría</th>
              <th scope="col"><a href="<?= conQuery(['orden' => 'precio']) ?>">Precio</a></th>
              <th scope="col"><a href="<?= conQuery(['orden' => 'stock']) ?>">Stock</a></th>
              <th scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$productos): ?>
              <tr><td colspan="5">No hay productos para mostrar.</td></tr>
            <?php endif; ?>
            <?php foreach ($productos as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>$<?= number_format((float) $p['precio'], 0, ',', '.') ?></td>
                <td><?= (int) $p['stock'] ?></td>
                <td>
                  <a href="<?= conQuery(['editar' => $p['id']]) ?>" class="boton boton--pequeno">Editar</a>
                  <form method="post" action="<?= BASE_URL ?>app/controladores/productos_eliminar.php"
                        style="display:inline" onsubmit="return confirm('¿Desactivar este producto?');">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="boton boton--pequeno boton--peligro">Desactivar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
          <nav aria-label="Paginación de productos">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
              <a href="<?= conQuery(['pagina' => $i]) ?>" <?= $i === $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      </section>
    </main>
    <?php require __DIR__ . '/app/vistas/parciales/pie.php'; ?>
</body>
</html>
