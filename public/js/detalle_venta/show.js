/**
 * public/js/detalle_venta/show.js
 * Lógica específica para la página de detalles de un Detalle de Venta,
 * incluyendo el cálculo de margen de ganancia.
 */
import { animateElements } from '../utils/animate.js';

/**
 * Inicializa la página de detalles del Detalle de Venta.
 * @param {number} detalleId - El ID del detalle de venta.
 * @param {string} productoNombre - El nombre del producto.
 * @param {number} cantidad - La cantidad vendida.
 * @param {number} precioUnitario - El precio de venta unitario.
 * @param {number} precioCosto - El precio de costo del producto (para margen).
 */
export function initShowPage(detalleId, productoNombre, cantidad, precioUnitario, precioCosto) {
    // 1. Animación suave para las tarjetas
    animateElements('.stats-card, .detalle-card, .stat-item', 200, 'Y');

    // 2. Cálculo Financiero
    const gananciaUnidad = precioUnitario - precioCosto;
    const gananciaTotal = gananciaUnidad * cantidad;
    // Margen de ganancia: (Ganancia / Costo) * 100. Se evita división por cero.
    const margenPorcentaje = precioCosto > 0 ? ((gananciaUnidad / precioCosto) * 100) : 0;

    // 3. Actualización de elementos visuales (Asumiendo que tienes elementos con estos IDs)
    const gananciaUnidadDisplay = document.getElementById('ganancia-unidad-display');
    const gananciaTotalDisplay = document.getElementById('ganancia-total-display');
    const margenPorcentajeDisplay = document.getElementById('margen-porcentaje-display');

    if (gananciaUnidadDisplay) {
        gananciaUnidadDisplay.textContent = `$${gananciaUnidad.toFixed(2)}`;
    }
    if (gananciaTotalDisplay) {
        gananciaTotalDisplay.textContent = `$${gananciaTotal.toFixed(2)}`;
    }
    if (margenPorcentajeDisplay) {
        margenPorcentajeDisplay.textContent = `${margenPorcentaje.toFixed(2)}%`;
    }

    // 4. Log de consola
    console.log(`Detalle de Venta #${detalleId} inicializado:`);
    console.log(`- Producto: ${productoNombre} (x${cantidad})`);
    console.log(`- Ganancia Total Estimada: $${gananciaTotal.toFixed(2)}`);
    console.log(`- Margen de Venta: ${margenPorcentaje.toFixed(2)}%`);
}
