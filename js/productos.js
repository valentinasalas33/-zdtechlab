// ============================================
// ZD.TechLab — Interfaz de productos (Día 8)
// ============================================

const tbody = document.querySelector("#tabla-productos tbody");
const moneda = new Intl.NumberFormat("es-CO", { style: "currency", currency: "COP", maximumFractionDigits: 0 });

// 1. Pintar la tabla desde el estado, sin escribir filas a mano en el HTML
const plantilla = p => {
  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td>${p.nombre}</td>
    <td>${nombreCategoria(p.categoriaId)}</td>
    <td>${moneda.format(p.precio)}</td>
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

pintar(productos);

// 2. Buscador en vivo con filter
const buscador = document.querySelector("#buscador");
buscador.addEventListener("input", () => {
  const texto = buscador.value.trim().toLowerCase();
  const filtrados = productos.filter(p => p.nombre.toLowerCase().includes(texto));
  pintar(filtrados);
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
    form.elements.categoria_id.value = producto.categoriaId;
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

// 3. Menú lateral: responde a clic y a la tecla Enter
const botonMenu = document.querySelector(".boton-menu");
const menu = document.querySelector(".panel__menu");

function alternarMenu() {
  const abierto = menu.classList.toggle("abierto");
  botonMenu.setAttribute("aria-expanded", abierto);
}

botonMenu.addEventListener("click", alternarMenu);

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

  const datos = {
    nombre: form.elements.nombre.value.trim(),
    categoriaId: Number(form.elements.categoria_id.value),
    precio: Number(precio.value),
    stock: Number(form.elements.stock.value)
  };

  if (editandoId) {
    const producto = productos.find(p => p.id === editandoId);
    Object.assign(producto, datos);
    editandoId = null;
    form.querySelector("button[type=submit]").textContent = "Guardar";
  } else {
    productos.push({ id: siguienteId(), ...datos });
  }

  pintar(productos);
  form.reset();
});
