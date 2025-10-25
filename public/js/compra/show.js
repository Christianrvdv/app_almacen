/**
 * public/js/compra/show.js
 * Lógica específica para la página de detalles de Compra.
 */
import { animateElements } from '../utils/animate.js';

export function initShowPage() {
    // 1. Animación suave para las tarjetas (información principal)
    animateElements('.stats-card, .detalle-card', 200, 'Y');

    // 2. Animación para las filas de la tabla de detalles de la compra
    // Se usa un retraso adicional para que empiece después de las tarjetas.
    animateElements('.table-show tbody tr', 100, 'X');

    // 3. Log de consola (opcional, para depuración)
    console.log('Página de detalles de la compra inicializada.');
}
