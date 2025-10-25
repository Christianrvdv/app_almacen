/**
 * public/js/ajuste_inventario/new.js
 * Lógica específica para los formularios de Ajuste de Inventario (New y Edit).
 */
import { initFormValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

export function initFormPage(formVars) {
    // 1. Inicializar la validación genérica
    initFormValidation();

    const { tipoId, cantidadId, productoId, fechaId, isEdit } = formVars;

    const tipoSelect = document.getElementById(tipoId);
    const cantidadInput = document.getElementById(cantidadId);
    const productoSelect = document.getElementById(productoId);
    const tipoPreview = document.getElementById('tipoPreview');
    const impactoPreview = document.getElementById('impactoPreview');
    const productoPreview = document.getElementById('productoPreview');
    const fechaInput = document.getElementById(fechaId);


    /** Actualiza la sección de 'Vista Previa del Impacto' en el formulario. */
    function actualizarVistaPrevia() {
        const tipo = tipoSelect ? tipoSelect.value : '';
        const cantidad = cantidadInput ? cantidadInput.value : 0;
        const productoNombre = productoSelect && productoSelect.selectedIndex !== -1 ? productoSelect.options[productoSelect.selectedIndex].text : 'Ninguno';

        // 1. Tipo de Movimiento (tipoPreview)
        if (tipo) {
            tipoPreview.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
            tipoPreview.className = tipo === 'entrada' ? 'margen-ganancia text-profit' : 'margen-perdida text-danger';
        } else {
            tipoPreview.textContent = 'Selecciona un tipo';
            tipoPreview.className = 'margen-ganancia text-profit';
        }

        // 2. Impacto en Inventario (impactoPreview)
        if (cantidad && parseInt(cantidad) > 0) {
            const signo = tipo === 'entrada' ? '+' : (tipo === 'salida' ? '-' : '');
            impactoPreview.textContent = `${signo}${cantidad} unidades`;
            impactoPreview.className = tipo === 'entrada' ? 'text-success' : (tipo === 'salida' ? 'text-danger' : 'text-success');
        } else {
            impactoPreview.textContent = '0 unidades';
            impactoPreview.className = 'text-success';
        }

        // 3. Producto Seleccionado (productoPreview)
        if (productoSelect && productoSelect.value) {
            productoPreview.textContent = productoNombre;
            productoPreview.className = 'text-info';
        } else {
            productoPreview.textContent = 'Ninguno';
            productoPreview.className = 'text-info';
        }
    }

    // --- Event Listeners ---
    if (tipoSelect) tipoSelect.addEventListener('change', actualizarVistaPrevia);
    if (cantidadInput) cantidadInput.addEventListener('input', actualizarVistaPrevia);
    if (productoSelect) productoSelect.addEventListener('change', actualizarVistaPrevia);

    // Ejecutar inicialización de la vista previa
    actualizarVistaPrevia();


    // --- Fecha por Defecto (Solo en 'new') ---
    if (!isEdit && fechaInput && !fechaInput.value) {
        const now = new Date();
        const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        fechaInput.value = localDateTime;
    }

    // --- Animación de Secciones ---
    const selector = isEdit ? '.form-section, .stats-card' : '.form-section';
    animateElements(selector, 200, 'Y');
}
