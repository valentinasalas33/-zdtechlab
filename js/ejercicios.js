// ============================================
// ZD.TechLab — Ejercicios con métodos de arreglo (Día 7)
// Requiere que datos-prueba.js se cargue antes en el HTML
// ============================================

// 1. Producto más caro
// reduce compara de a dos y se queda con el de mayor precio
const productoMasCaro = productos.reduce((masCaro, actual) =>
  actual.precio > masCaro.precio ? actual : masCaro
);

// 2. Total de unidades en stock por categoría
// reduce va acumulando en un objeto, sumando el stock por cada categoría
const stockPorCategoria = productos.reduce((acumulado, producto) => {
  acumulado[producto.categoria] = (acumulado[producto.categoria] || 0) + producto.stock;
  return acumulado;
}, {});

// 3. Productos con stock menor a cinco
// filter devuelve solo los que cumplen la condición
const stockCritico = productos.filter(p => p.stock < 5);

// 4. Promedio de precio
// map saca solo los precios, reduce los suma, y se divide por la cantidad
const totalPrecios = productos.map(p => p.precio).reduce((suma, precio) => suma + precio, 0);
const promedioPrecio = totalPrecios / productos.length;

// Formato de moneda en pesos colombianos
const moneda = new Intl.NumberFormat("es-CO", { style: "currency", currency: "COP", maximumFractionDigits: 0 });

// Mostrar resultados en consola
console.log("Producto más caro:", productoMasCaro.nombre, moneda.format(productoMasCaro.precio));
console.log("Stock por categoría:", stockPorCategoria);
console.table(stockCritico);
console.log("Promedio de precio:", moneda.format(promedioPrecio));
