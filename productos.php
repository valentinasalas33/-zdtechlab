<?php
declare(strict_types=1);

require_once __DIR__ . '/app/seguridad/guardia.php';

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos — ZD.TechLab</title>
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
  <div class="panel">
    <header class="panel__barra">
      <button type="button" class="boton-menu" aria-label="Abrir menú" aria-expanded="false">☰</button>
      <img src="assets/img/logo.svg" alt="Logo de ZD.TechLab" width="120">
      <input type="search" placeholder="Buscar..." aria-label="Buscar productos" id="buscador">
    </header>
    <aside class="panel__menu" id="menu-lateral">
      <nav aria-label="Menú principal">
        <ul class="menu">
          <li><a href="dashboard.php" class="menu__enlace">Tablero</a></li>
          <li><a href="productos.php" class="menu__enlace menu__enlace--activo" aria-current="page">Productos</a></li>
          <li><a href="#" class="menu__enlace">Categorías</a></li>
          <li><a href="#" class="menu__enlace">Clientes</a></li>
          <li><a href="#" class="menu__enlace">Pedidos</a></li>
          <li><a href="#" class="menu__enlace">Reportes</a></li>
        </ul>
        <a href="salir.php" class="menu__salir">Cerrar sesión</a>
      </nav>
    </aside>
    <main class="panel__contenido">
      <h1>Productos</h1>
      <section aria-labelledby="titulo-formulario">
        <h2 id="titulo-formulario">Registrar producto</h2>
        <form id="form-producto" novalidate class="formulario formulario--linea">
          <div>
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" minlength="3" required>
          </div>
          <div>
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria_id" required>
              <option value="">Seleccione…</option>
              <option value="1">Periféricos</option>
              <option value="2">Pantallas</option>
              <option value="3">Almacenamiento</option>
              <option value="4">Redes</option>
            </select>
          </div>
          <div>
            <label for="precio">Precio</label>
            <input type="number" id="precio" name="precio" min="1" step="1" required>
          </div>
          <div>
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" step="1" required>
          </div>
          <button type="submit" class="boton">Guardar</button>
        </form>
      </section>
      <section aria-labelledby="titulo-tabla">
        <h2 id="titulo-tabla">Listado de productos</h2>
        <table id="tabla-productos">
          <caption>Productos activos en inventario</caption>
          <thead>
            <tr>
              <th scope="col">Nombre</th>
              <th scope="col">Categoría</th>
              <th scope="col">Precio</th>
              <th scope="col">Stock</th>
              <th scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </section>
    </main>
    <footer class="panel__pie"><p>ZD.TechLab — Panel de gestión</p></footer>
  </div>
  <script src="js/estado.js" defer></script>
  <script src="js/productos.js" defer></script>
</body>
</html>
