/**
 * public/js/producto/new.js
 * Lógica específica para los formularios de Producto (New y Edit).
 */
import { initFormValidation, initNumerosValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

/**
 * Configura la lógica de cálculo de margen de ganancia en tiempo real.
 * @param {string} compraId - ID del campo de Precio de Compra.
 * @param {string} ventaId - ID del campo de Precio de Venta.
 * @param {string} gananciaPreviewId - ID del elemento para mostrar la ganancia.
 * @param {string} margenPreviewId - ID del elemento para mostrar el porcentaje de margen.
 */
function setupMarginCalculator(compraId, ventaId, gananciaPreviewId, margenPreviewId) {
    const precioCompraInput = document.getElementById(compraId);
    const precioVentaInput = document.getElementById(ventaId);
    const gananciaPreview = document.getElementById(gananciaPreviewId);
    const margenPreview = document.getElementById(margenPreviewId);

    if (!precioCompraInput || !precioVentaInput || !gananciaPreview || !margenPreview) return;

    const calcularMargen = () => {
        const compra = parseFloat(precioCompraInput.value) || 0;
        const venta = parseFloat(precioVentaInput.value) || 0;

        if (venta > 0) {
            const ganancia = venta - compra;
            const margen = compra > 0 ? (ganancia / compra) * 100 : (ganancia > 0 ? 100 : 0);

            // Actualizar la vista
            gananciaPreview.textContent = `$${ganancia.toFixed(2)}`;
            margenPreview.textContent = `${margen.toFixed(2)}%`;

            // Estilos
            margenPreview.className = ganancia > 0 ? 'margen-ganancia text-success' : 'margen-ganancia text-danger';
            gananciaPreview.className = ganancia > 0 ? 'text-success' : 'text-danger';

        } else {
            // Valores por defecto si la venta es 0
            gananciaPreview.textContent = '$0.00';
            margenPreview.textContent = '0.00%';
            margenPreview.className = 'margen-ganancia text-secondary';
            gananciaPreview.className = 'text-secondary';
        }
    }

    precioCompraInput.addEventListener('input', calcularMargen);
    precioVentaInput.addEventListener('input', calcularMargen);

    // Calcular margen inicial
    calcularMargen();
}

export function initFormPage(ids) {
    initFormValidation();

    // 1. Inicializar la lógica de la vista previa de margen
    setupMarginCalculator(
        ids.precioCompraId,
        ids.precioVentaId,
        ids.gananciaPreviewId,
        ids.margenPreviewId
    );

    // 2. Inicializar validación numérica (REUTILIZABLE)
    initNumerosValidation();

    // 3. Animación suave para las secciones
    animateElements('.form-section, .detalle-card', 200, 'Y');

    console.log('Formulario de Producto inicializado de forma modular.');
}
