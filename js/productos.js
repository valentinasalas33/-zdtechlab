// ============================================
// ZD.TechLab — Interfaz de productos (Día 9: datos reales desde MySQL)
// ============================================

const tbody = document.querySelector("#tabla-productos tbody");
const moneda = new Intl.NumberFormat("es-CO", { style: "currency", currency: "COP", maximumFractionDigits: 0 });
let productos = [];

// 1. Pintar la tabla con lo que llega del servidor
const plantilla = p => {
  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td>${p.nombre}</td>
    <td>${p.categoria}</td>
    <td>${moneda.format(Number(p.precio))}</td>
    <td>${p.stock}</td>
    <td>
      <button type="button" class="boton-mini" data-accion="editar" data-id="${p.id}">Editar</button>
      <button type="button" class="boton-mini boton-peligro" data-accion="eliminar" data-id="${p.id}">Eliminar</button>
    </td>`;
  return tr;
};

function pintar(lista) {
  tbody.replaceChildren(...lista.map(plantilla));
}

// Pide la lista de productos a PHP, que consulta MySQL con sentencia preparada
async function cargarProductos(texto = "") {
  const respuesta = await fetch(`app/api/productos.php?buscar=${encodeURIComponent(texto)}`);
  productos = await respuesta.json();
  pintar(productos);
}

cargarProductos();

// 2. Buscador en vivo: ahora la búsqueda la resuelve MySQL, no JavaScript
const buscador = document.querySelector("#buscador");
buscador.addEventListener("input", () => {
  cargarProductos(buscador.value.trim());
});

// Un solo escucha para toda la tabla: delegación de eventos
let editandoId = null;
tbody.addEventListener("click", (e) => {
  const boton = e.target.closest("button[data-accion]");
  if (!boton) return;
  const { accion, id } = boton.dataset;
  const producto = productos.find(p => p.id === Number(id));
  if (!producto) return;

  if (accion === "editar") {
    form.elements.nombre.value = producto.nombre;
    const opcion = [...form.elements.categoria_id.options].find(o => o.textContent === producto.categoria);
    form.elements.categoria_id.value = opcion ? opcion.value : "";
    form.elements.precio.value = producto.precio;
    form.elements.stock.value = producto.stock;
    editandoId = producto.id;
    form.querySelector("button[type=submit]").textContent = "Actualizar";
  }

  if (accion === "eliminar") {
    const confirmar = confirm(`¿Eliminar "${producto.nombre}"?`);
    if (confirmar) {
      productos = productos.filter(p => p.id !== producto.id);
      pintar(productos);
    }
  }
});

// 3. Validación del formulario, sin usar alert()
// (el menú lateral se movió a js/menu.js el día 12, para no repetirlo
// en cada página)

// 4. Validación del formulario, sin usar alert()
const form = document.querySelector("#form-producto");
form.addEventListener("submit", (e) => {
  e.preventDefault();

  const precio = form.elements.precio;
  precio.setCustomValidity(Number(precio.value) <= 0 ? "El precio debe ser mayor que cero." : "");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // El guardado real (INSERT/UPDATE en MySQL) se conecta cuando se construya el CRUD completo
  console.log("Formulario válido, listo para guardar:", { nombre: form.elements.nombre.value });
  form.reset();
});
