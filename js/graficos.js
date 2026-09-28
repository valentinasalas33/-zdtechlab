// ZD.TechLab — Gráficos del tablero (Día 14)
// Los datos SIEMPRE vienen de api/graficos.php (que a su vez lee las vistas);
// aquí no hay ninguna consulta ni dato escrito a mano.
const PALETA = ['#39A900', '#15506B', '#F08A00', '#8FD46A', '#B0209E', '#00A0C6'];
const moneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });

let graficoVentas, graficoPedidos, graficoCategorias;

async function dibujarGraficos(desde = '', hasta = '') {
  const parametros = desde && hasta ? `?desde=${desde}&hasta=${hasta}` : '';
  const respuesta = await fetch(`api/graficos.php${parametros}`, { credentials: 'same-origin' });
  if (!respuesta.ok) {
    return; // sin sesión válida u otro error: no se dibuja nada
  }
  const datos = await respuesta.json();

  graficoVentas?.destroy();
  graficoVentas = new Chart(document.getElementById('g-ventas'), {
    type: 'bar',
    data: {
      labels: datos.ventasMes.etiquetas,
      datasets: [{ label: 'Ventas del mes', data: datos.ventasMes.valores, backgroundColor: PALETA[0], borderRadius: 2 }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (c) => moneda.format(c.parsed.y) } },
      },
      scales: { y: { beginAtZero: true } },
    },
  });

  graficoPedidos?.destroy();
  graficoPedidos = new Chart(document.getElementById('g-pedidos'), {
    type: 'line',
    data: {
      labels: datos.pedidosMes.etiquetas,
      datasets: [{ label: 'Pedidos por mes', data: datos.pedidosMes.valores, borderColor: PALETA[1], backgroundColor: PALETA[1], tension: 0.3 }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
    },
  });

  graficoCategorias?.destroy();
  graficoCategorias = new Chart(document.getElementById('g-categorias'), {
    type: 'doughnut',
    data: {
      labels: datos.categorias.etiquetas,
      datasets: [{ data: datos.categorias.valores, backgroundColor: PALETA }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: { position: 'right' },
        tooltip: { callbacks: { label: (c) => `${c.label}: ${moneda.format(c.parsed)}` } },
      },
    },
  });
}

document.addEventListener('DOMContentLoaded', () => {
  dibujarGraficos();

  const formulario = document.getElementById('form-filtro-fechas');
  formulario?.addEventListener('submit', (e) => {
    e.preventDefault();
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;
    dibujarGraficos(desde, hasta);
  });
});
