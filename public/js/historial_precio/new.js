import { initFormValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('historial_precios_tipo');
    const precioAnteriorInput = document.getElementById('historial_precios_precio_anterior');
    const precioNuevoInput = document.getElementById('historial_precios_precio_nuevo');
    const fechaCambioInput = document.getElementById('historial_precios_fecha_cambio');
    const diferenciaPreview = document.getElementById('diferenciaPreview');
    const variacionPreview = document.getElementById('variacionPreview');

    // Elementos del resumen
    const resumenTipo = document.getElementById('resumenTipo');
    const resumenAnterior = document.getElementById('resumenAnterior');
    const resumenNuevo = document.getElementById('resumenNuevo');
    const resumenDiferencia = document.getElementById('resumenDiferencia');

    // Precios del producto desde el objeto producto pasado por el controlador
    const preciosProducto = window.preciosProducto || { compra: 0, venta: 0 };

    // Establecer fecha actual por defecto si está vacía
    if (fechaCambioInput && !fechaCambioInput.value) {
        const now = new Date();
        const torontoTime = now.toLocaleString("en-CA", {
            timeZone: "America/Toronto",
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).replace(', ', 'T').replace(/\//g, '-');

        fechaCambioInput.value = torontoTime;
    }

    // Función para actualizar el precio según el tipo seleccionado
    function actualizarPrecio() {
        const valorTipo = tipoSelect.value;
        const helpText = precioAnteriorInput.closest('.form-group-modern').querySelector('.form-help-text');

        if (valorTipo === 'compra') {
            precioAnteriorInput.value = preciosProducto.compra;
            precioAnteriorInput.placeholder = preciosProducto.compra.toFixed(2);
            if (helpText) {
                helpText.textContent = 'Precio de compra actual del producto';
            }
        } else if (valorTipo === 'venta') {
            precioAnteriorInput.value = preciosProducto.venta;
            precioAnteriorInput.placeholder = preciosProducto.venta.toFixed(2);
            if (helpText) {
                helpText.textContent = 'Precio de venta actual del producto';
            }
        } else {
            precioAnteriorInput.value = '0.00';
            precioAnteriorInput.placeholder = '0.00';
            if (helpText) {
                helpText.textContent = 'Precio anterior del producto';
            }
        }

        // Recalcular el cambio después de actualizar el precio
        calcularCambio();
    }

    // Asignar el evento change
    if (tipoSelect) {
        tipoSelect.addEventListener('change', actualizarPrecio);
    }

    // Llamar a la función al cargar la página
    actualizarPrecio();

    function calcularCambio() {
        const precioAnterior = parseFloat(precioAnteriorInput.value) || 0;
        const precioNuevo = parseFloat(precioNuevoInput.value) || 0;
        const tipo = tipoSelect.options[tipoSelect.selectedIndex]?.text || '-';

        if (precioAnterior > 0 && precioNuevo > 0) {
            const diferencia = precioNuevo - precioAnterior;
            const variacionPorcentaje = (diferencia / precioAnterior) * 100;

            // Actualizar preview
            if (diferenciaPreview) {
                diferenciaPreview.textContent = `$${diferencia.toFixed(2)}`;
                diferenciaPreview.className = diferencia > 0 ? 'text-success' : 'text-danger';
            }

            if (variacionPreview) {
                variacionPreview.textContent = `${variacionPorcentaje.toFixed(2)}%`;
                variacionPreview.className = diferencia > 0 ? 'text-profit' : 'text-loss';
            }

            // Actualizar resumen
            if (resumenTipo) resumenTipo.textContent = tipo;
            if (resumenAnterior) resumenAnterior.textContent = `$${precioAnterior.toFixed(2)}`;
            if (resumenNuevo) resumenNuevo.textContent = `$${precioNuevo.toFixed(2)}`;
            if (resumenDiferencia) {
                resumenDiferencia.textContent = `$${diferencia.toFixed(2)}`;
                resumenDiferencia.className = diferencia > 0 ? 'info-value text-success' : 'info-value text-danger';
            }
        } else {
            // Valores por defecto
            if (diferenciaPreview) {
                diferenciaPreview.textContent = '$0.00';
                diferenciaPreview.className = 'text-success';
            }
            if (variacionPreview) {
                variacionPreview.textContent = '0.00%';
                variacionPreview.className = 'text-profit';
            }

            // Resumen por defecto
            if (resumenTipo) resumenTipo.textContent = tipo || '-';
            if (resumenAnterior) resumenAnterior.textContent = `$${(precioAnterior || 0).toFixed(2)}`;
            if (resumenNuevo) resumenNuevo.textContent = `$${(precioNuevo || 0).toFixed(2)}`;
            if (resumenDiferencia) {
                resumenDiferencia.textContent = '$0.00';
                resumenDiferencia.className = 'info-value text-primary';
            }
        }
    }

    // Calcular cuando cambien los precios
    if (precioAnteriorInput) precioAnteriorInput.addEventListener('input', calcularCambio);
    if (precioNuevoInput) precioNuevoInput.addEventListener('input', calcularCambio);

    // Calcular inicialmente
    calcularCambio();

    // Validación de formulario
    initFormValidation();

    // Animación suave para las secciones
    animateElements('.form-section', 200, 'Y');

    // Efecto hover para la tarjeta de resumen
    const resumenCard = document.querySelector('.detalle-card');
    if (resumenCard) {
        resumenCard.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
        });
        resumenCard.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    }
});
