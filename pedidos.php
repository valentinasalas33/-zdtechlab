<?php
declare(strict_types=1);

/**
 * ZD.TechLab — Pedidos (Día 13: registro transaccional)
 * El formulario trae 3 líneas fijas de producto+cantidad (sin necesitar
 * JavaScript para agregar filas); las líneas vacías se ignoran en el
 * controlador.
 */
require_once __DIR__ . '/app/seguridad/guardia.php';
require_once __DIR__ . '/app/seguridad/csrf.php';
require_once __DIR__ . '/app/config/rutas.php';
require_once __DIR__ . '/app/config/conexion.php';
require_once __DIR__ . '/app/modelos/PedidoModelo.php';

exigirRol('administrador', 'vendedor');

$pdo    = Conexion::obtener();
$modelo = new PedidoModelo($pdo);

$pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 5;

$pedidos      = $modelo->listar($pagina, $porPagina);
$total        = $modelo->contar();
$totalPaginas = max(1, (int) ceil($total / $porPagina));

$clientes  = $pdo->query('SELECT id, nombre FROM clientes ORDER BY nombre')->fetchAll();
$productos = $pdo->query('SELECT id, nombre, precio, stock FROM productos WHERE activo = 1 ORDER BY nombre')->fetchAll();

$aviso = $_SESSION['aviso'] ?? null;
unset($_SESSION['aviso']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pedidos — ZD.TechLab</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/tokens.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>css/estilos.css">
</head>
<body>
  <div class="panel">
    <?php require __DIR__ . '/app/vistas/parciales/cabecera.php'; ?>
    <?php require __DIR__ . '/app/vistas/parciales/menu.php'; ?>
    <main class="panel__contenido">
      <h1>Pedidos</h1>

      <?php if ($aviso): ?>
        <p class="alerta alerta-<?= $aviso['tipo'] === 'exito' ? 'exito' : 'error' ?>">
          <?= htmlspecialchars($aviso['texto'], ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>

      <section aria-labelledby="titulo-formulario">
        <h2 id="titulo-formulario">Registrar pedido</h2>
        <form method="post" action="<?= BASE_URL ?>app/controladores/pedidos_registrar.php" novalidate class="formulario">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

          <div>
            <label for="cliente_id">Cliente</label>
            <select id="cliente_id" name="cliente_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($clientes as $c): ?>
                <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <p class="texto-tenue">Agrega hasta 3 productos (deja vacías las líneas que no uses).</p>
          <?php for ($fila = 1; $fila <= 3; $fila++): ?>
            <div class="formulario--linea">
              <div>
                <label for="producto_<?= $fila ?>">Producto <?= $fila ?></label>
                <select id="producto_<?= $fila ?>" name="producto_id[]">
                  <option value="">— Ninguno —</option>
                  <?php foreach ($productos as $p): ?>
                    <option value="<?= (int) $p['id'] ?>">
                      <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>
                      ($<?= number_format((float) $p['precio'], 0, ',', '.') ?> — stock: <?= (int) $p['stock'] ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label for="cantidad_<?= $fila ?>">Cantidad</label>
                <input type="number" id="cantidad_<?= $fila ?>" name="cantidad[]" min="1" step="1">
              </div>
            </div>
          <?php endfor; ?>

          <button type="submit" class="boton">Registrar pedido</button>
        </form>
      </section>

      <section aria-labelledby="titulo-tabla">
        <h2 id="titulo-tabla">Listado de pedidos</h2>
        <table id="tabla-pedidos">
          <caption>Pedidos registrados (<?= $total ?> en total)</caption>
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Cliente</th>
              <th scope="col">Fecha</th>
              <th scope="col">Total</th>
              <th scope="col">Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$pedidos): ?>
              <tr><td colspan="5">No hay pedidos registrados todavía.</td></tr>
            <?php endif; ?>
            <?php foreach ($pedidos as $p): ?>
              <tr>
                <td><?= (int) $p['id'] ?></td>
                <td><?= htmlspecialchars($p['cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($p['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>$<?= number_format((float) $p['total'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars(ucfirst($p['estado']), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
          <nav aria-label="Paginación de pedidos">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
              <a href="?pagina=<?= $i ?>" <?= $i === $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      </section>
    </main>
    <?php require __DIR__ . '/app/vistas/parciales/pie.php'; ?>
</body>
</html>
