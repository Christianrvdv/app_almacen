/**
 * public/js/cliente/show.js
 * Lógica específica para la página de detalles de Cliente.
 */
import { animateElements } from '../utils/animate.js';

export function initShowPage() {
    // 1. Animación suave para las tarjetas (información principal)
    animateElements('.stats-card, .detalle-card, .stat-item', 200, 'Y');

    // 2. Animación para las filas de la tabla de ventas (o historial)
    // Se usa un retraso adicional para que empiece después de las tarjetas.
    animateElements('tbody tr', 100, 'X');
}
