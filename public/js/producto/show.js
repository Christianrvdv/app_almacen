/**
 * public/js/producto/show.js
 * Lógica específica para la página de detalles del Producto.
 */
import { animateElements } from '../utils/animate.js';

export function initShowPage() {
    // 1. Animación suave para las tarjetas de detalle y estadísticas
    animateElements('.stats-card, .detalle-card, .stat-item', 200, 'Y');

    // **Opcional:** Si tienes gráficos (Chart.js) o pestañas, puedes inicializarlos aquí.

    console.log('Página de Detalles de Producto inicializada.');
}
