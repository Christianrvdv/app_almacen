/**
 * public/js/dashboard/index.js
 * Lógica específica para la página del Dashboard.
 */
import { animateElements } from '../utils/animate.js';

// **NOTA:** Asumimos aquí que estás usando Chart.js o similar
// Si tienes gráficos reales, la lógica para inicializarlos iría aquí.

/**
 * Función que simula la inicialización de un gráfico.
 * (Reemplazar con la lógica real de tu librería de gráficos, ej: Chart.js)
 */
function initChart(chartId, data) {
    const ctx = document.getElementById(chartId);
    if (ctx) {
        // Lógica de Chart.js / D3.js / otra librería
        console.log(`Inicializando gráfico: ${chartId} con ${data.length} puntos de datos.`);
        // ctx.textContent = '¡Gráfico cargado exitosamente!'; // Simulación visual
    }
}

/**
 * Función principal de inicialización del Dashboard.
 * @param {object} chartData - Datos pasados desde Twig para la inicialización de gráficos.
 */
export function initDashboard(chartData) {
    // 1. **Animación suave de todos los elementos principales**
    // Apuntamos a tarjetas de estadísticas, módulos y acciones rápidas.
    animateElements('.stats-card, .module-card, .quick-action-card, .alert-card', 100, 'Y');

    // 2. **Inicialización de Gráficos (si los hay)**
    if (chartData && chartData.ventasMensuales) {
        initChart('ventasMensualesChart', chartData.ventasMensuales);
    }
    if (chartData && chartData.stockPorCategoria) {
        initChart('stockPorCategoriaChart', chartData.stockPorCategoria);
    }

    // 3. **Lógica para el mensaje de alertas (opcional)**
    const alertCard = document.querySelector('.alert-card');
    if (alertCard) {
        alertCard.addEventListener('click', function() {
            console.log('Alerta de inventario revisada. Redirigiendo a Productos.');
            // Aquí podrías añadir una redirección o un modal.
        });
    }

    console.log('Dashboard inicializado. Animaciones y gráficos listos.');
}
