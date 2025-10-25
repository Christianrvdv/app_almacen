/**
 * public/js/ajuste_inventario/show.js
 * Lógica específica para la página de detalles de Ajuste de Inventario.
 */
import { animateElements } from '../utils/animate.js';

export function initShowPage(logMessage) {
    // 1. Animación suave para las tarjetas
    animateElements('.stats-card, .detalle-card, .stat-item', 200, 'Y');

    // 2. Remover clase de loading de las estadísticas con un retardo (efecto)
    setTimeout(() => {
        document.querySelectorAll('.stat-item').forEach(item => {
            item.classList.remove('loading');
        });
    }, 1000);

    // 3. Mostrar información del impacto en la consola
    console.log(logMessage);
}
