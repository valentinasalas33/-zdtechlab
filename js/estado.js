// ============================================
// ZD.TechLab — Estado de la aplicación (Día 8)
// ============================================

const categorias = [
  { id: 1, nombre: "Periféricos" },
  { id: 2, nombre: "Pantallas" },
  { id: 3, nombre: "Almacenamiento" },
  { id: 4, nombre: "Redes" }
];

let productos = [
  { id: 1,  nombre: "Teclado mecánico RGB",   categoriaId: 1, precio: 120000, stock: 14 },
  { id: 2,  nombre: "Mouse inalámbrico",       categoriaId: 1, precio: 65000,  stock: 32 },
  { id: 3,  nombre: "Mousepad XL",             categoriaId: 1, precio: 35000,  stock: 20 },
  { id: 4,  nombre: "Audífonos con micrófono", categoriaId: 1, precio: 150000, stock: 8  },
  { id: 5,  nombre: "Webcam Full HD",          categoriaId: 1, precio: 180000, stock: 3  },
  { id: 6,  nombre: "Monitor 24 pulgadas",     categoriaId: 2, precio: 890000, stock: 0  },
  { id: 7,  nombre: "Monitor curvo 27 pulgadas", categoriaId: 2, precio: 1250000, stock: 6 },
  { id: 8,  nombre: "SSD 1TB",                 categoriaId: 3, precio: 320000, stock: 7  },
  { id: 9,  nombre: "SSD 500GB",               categoriaId: 3, precio: 210000, stock: 18 },
  { id: 10, nombre: "Disco duro externo 2TB",  categoriaId: 3, precio: 280000, stock: 10 },
  { id: 11, nombre: "Memoria USB 64GB",        categoriaId: 3, precio: 45000,  stock: 40 },
  { id: 12, nombre: "Router WiFi 6",           categoriaId: 4, precio: 310000, stock: 9  },
  { id: 13, nombre: "Switch de 8 puertos",     categoriaId: 4, precio: 150000, stock: 12 },
  { id: 14, nombre: "Cable de red Cat6 (5m)",  categoriaId: 4, precio: 25000,  stock: 50 },
  { id: 15, nombre: "Repetidor WiFi",          categoriaId: 4, precio: 90000,  stock: 2  }
];

function nombreCategoria(categoriaId) {
  const categoria = categorias.find(c => c.id === categoriaId);
  return categoria ? categoria.nombre : "Sin categoría";
}

function siguienteId() {
  return productos.length ? Math.max(...productos.map(p => p.id)) + 1 : 1;
}
