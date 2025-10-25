/**
 * public/js/detalle_compra/show.js
 * Lógica específica para la página de detalles de un Detalle de Compra.
 */
import { animateElements } from '../utils/animate.js';

/**
 * Inicializa la página de detalles del Detalle de Compra.
 * @param {number} detalleId - El ID del detalle de compra.
 * @param {string} productoNombre - El nombre del producto.
 * @param {number} cantidad - La cantidad del producto en el detalle.
 * @param {number} subtotal - El subtotal del detalle.
 */
export function initShowPage(detalleId, productoNombre, cantidad, subtotal) {
    // 1. Animación suave para las tarjetas (información principal)
    // Apuntamos a elementos comunes como 'stats-card' o 'detalle-card'
    animateElements('.stats-card, .detalle-card, .stat-item', 200, 'Y');

    // 2. Log de consola para depuración
    try {
        const subtotalFormatted = parseFloat(subtotal).toLocaleString('es-ES', { minimumFractionDigits: 2 });

        console.log(`Detalle de Compra #${detalleId} inicializado:`);
        console.log(`Producto: ${cantidad} unidades de ${productoNombre}.`);
        console.log(`Subtotal: $${subtotalFormatted}.`);
    } catch (e) {
        console.error('Error al generar el log de detalles:', e);
    }
}
