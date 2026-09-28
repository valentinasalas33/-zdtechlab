// ZD.TechLab — Gráfico y exportación a PDF de reportes (Día 15)
document.addEventListener('DOMContentLoaded', () => {
  const lienzo = document.getElementById('g-reporte');
  if (lienzo && typeof DATOS_GRAFICO !== 'undefined' && DATOS_GRAFICO.valores.length) {
    const PALETA = ['#39A900', '#15506B', '#F08A00', '#8FD46A', '#B0209E', '#00A0C6'];
    const tipoGrafico = TIPO_REPORTE === 'pedidos' ? 'bar' : 'doughnut';
    new Chart(lienzo, {
      type: tipoGrafico,
      data: {
        labels: DATOS_GRAFICO.etiquetas,
        datasets: [{
          data: DATOS_GRAFICO.valores,
          backgroundColor: tipoGrafico === 'bar' ? PALETA[0] : PALETA,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: tipoGrafico === 'bar' ? undefined : 'right', display: tipoGrafico !== 'bar' } },
      },
    });
  }

  document.getElementById('btn-pdf')?.addEventListener('click', () => {
    document.getElementById('form-pdf-oculto').submit();
  });
});
