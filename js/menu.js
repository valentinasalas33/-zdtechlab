// ============================================
// ZD.TechLab — Menú lateral colapsable (Día 8, conectado a todas las páginas el Día 12)
// ============================================

const botonMenu = document.querySelector(".boton-menu");
const menu = document.querySelector(".panel__menu");

function alternarMenu() {
  const abierto = menu.classList.toggle("abierto");
  botonMenu.setAttribute("aria-expanded", abierto);
}

botonMenu.addEventListener("click", alternarMenu);
