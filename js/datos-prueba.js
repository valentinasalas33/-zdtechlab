// ============================================
// ZD.TechLab — Datos de prueba (Día 7)
// Misma forma que tendrán los datos reales de la base de datos
// ============================================

const productos = [
  { id: 1,  nombre: "Teclado mecánico RGB",   categoria: "Periféricos",     precio: 120000, stock: 14 },
  { id: 2,  nombre: "Mouse inalámbrico",       categoria: "Periféricos",     precio: 65000,  stock: 32 },
  { id: 3,  nombre: "Mousepad XL",             categoria: "Periféricos",     precio: 35000,  stock: 20 },
  { id: 4,  nombre: "Audífonos con micrófono", categoria: "Periféricos",     precio: 150000, stock: 8  },
  { id: 5,  nombre: "Webcam Full HD",          categoria: "Periféricos",     precio: 180000, stock: 3  },
  { id: 6,  nombre: "Monitor 24 pulgadas",     categoria: "Pantallas",       precio: 890000, stock: 0  },
  { id: 7,  nombre: "Monitor curvo 27 pulgadas", categoria: "Pantallas",     precio: 1250000, stock: 6 },
  { id: 8,  nombre: "SSD 1TB",                 categoria: "Almacenamiento",  precio: 320000, stock: 7  },
  { id: 9,  nombre: "SSD 500GB",               categoria: "Almacenamiento",  precio: 210000, stock: 18 },
  { id: 10, nombre: "Disco duro externo 2TB",  categoria: "Almacenamiento",  precio: 280000, stock: 10 },
  { id: 11, nombre: "Memoria USB 64GB",        categoria: "Almacenamiento",  precio: 45000,  stock: 40 },
  { id: 12, nombre: "Router WiFi 6",           categoria: "Redes",           precio: 310000, stock: 9  },
  { id: 13, nombre: "Switch de 8 puertos",     categoria: "Redes",           precio: 150000, stock: 12 },
  { id: 14, nombre: "Cable de red Cat6 (5m)",  categoria: "Redes",           precio: 25000,  stock: 50 },
  { id: 15, nombre: "Repetidor WiFi",          categoria: "Redes",           precio: 90000,  stock: 2  }
];

const pedidos = [
  { id: 1, clienteId: 1, productoId: 1,  cantidad: 1, precioUnitario: 120000 },
  { id: 2, clienteId: 2, productoId: 6,  cantidad: 1, precioUnitario: 890000 },
  { id: 3, clienteId: 3, productoId: 8,  cantidad: 1, precioUnitario: 320000 },
  { id: 4, clienteId: 4, productoId: 12, cantidad: 1, precioUnitario: 310000 },
  { id: 5, clienteId: 5, productoId: 4,  cantidad: 1, precioUnitario: 150000 },
  { id: 6, clienteId: 1, productoId: 7,  cantidad: 1, precioUnitario: 1250000 },
  { id: 7, clienteId: 6, productoId: 9,  cantidad: 2, precioUnitario: 210000 },
  { id: 8, clienteId: 7, productoId: 2,  cantidad: 1, precioUnitario: 65000 }
];
