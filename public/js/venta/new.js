/**
 * public/js/veenta/new.js
 * Módulo para manejar la creación de ventas.
 */

import { animateElements } from '../utils/animate.js';
import { initFormValidation } from '../utils/validation.js';

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar validación de formulario
    initFormValidation();

    // Obtener elementos del DOM
    const form = document.getElementById('venta-form');
    const addButton = document.getElementById('add-detalle');
    const detalleVentas = document.getElementById('detalle-ventas');
    const totalPreview = document.getElementById('totalPreview');
    const totalInput = document.getElementById('venta-total');

    // Función para calcular subtotales y total
    function calcularTotales() {
        let totalVenta = 0;

        document.querySelectorAll('.detalle-card').forEach(card => {
            const cantidad = parseFloat(card.querySelector('.detalle-cantidad').value) || 0;
            const precio = parseFloat(card.querySelector('.detalle-precio').value) || 0;
            const subtotal = cantidad * precio;

            // Actualizar subtotal
            const subtotalInput = card.querySelector('.detalle-subtotal');
            if (subtotalInput) {
                subtotalInput.value = subtotal.toFixed(2);
            }

            totalVenta += subtotal;
        });

        // Actualizar total
        totalPreview.textContent = `$${totalVenta.toFixed(2)}`;
        totalInput.value = totalVenta.toFixed(2);
    }

    // Agregar nuevo detalle
    addButton.addEventListener('click', function () {
        const index = detalleVentas.dataset.index || document.querySelectorAll('.detalle-card').length;
        const prototype = detalleVentas.getAttribute('data-prototype');
        const newForm = prototype.replace(/__name__/g, index);
        const newRow = document.createElement('div');
        newRow.setAttribute('class', 'detalle-card');
        newRow.setAttribute('data-index', index);
        newRow.innerHTML = newForm;

        // Añadir campos de subtotal
        const subtotalField = `
            <div class="col-md-3 mb-3">
                <div class="form-group-modern">
                    <label class="form-label-modern">Subtotal</label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text input-group-text-modern">$</span>
                        <input type="text" class="form-control form-control-modern detalle-subtotal" readonly id="detalle_${index}_subtotal">
                    </div>
                </div>
            </div>
        `;

        const rowContent = newRow.querySelector('.row');
        if (rowContent) {
            rowContent.insertAdjacentHTML('beforeend', subtotalField);
        }

        // Añadir botón eliminar
        const deleteButton = document.createElement('button');
        deleteButton.setAttribute('type', 'button');
        deleteButton.setAttribute('class', 'btn btn-modern btn-danger-modern eliminar-detalle');
        deleteButton.innerHTML = '<i class="fas fa-trash me-2"></i>Eliminar Detalle';
        newRow.appendChild(deleteButton);

        detalleVentas.appendChild(newRow);

        // Actualizar el índice
        detalleVentas.dataset.index = parseInt(index) + 1;

        // Reasignar eventos a los nuevos campos
        asignarEventosDetalle(newRow);

        // Calcular totales
        calcularTotales();

        // Animación para el nuevo elemento
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateY(20px)';

        setTimeout(() => {
            newRow.style.transition = 'all 0.6s ease';
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateY(0)';
        }, 100);
    });

    // Eliminar detalle
    detalleVentas.addEventListener('click', function (e) {
        if (e.target.classList.contains('eliminar-detalle') ||
            e.target.closest('.eliminar-detalle')) {
            const button = e.target.classList.contains('eliminar-detalle') ?
                e.target : e.target.closest('.eliminar-detalle');
            const card = button.closest('.detalle-card');

            // Animación de salida
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(100px)';

            setTimeout(() => {
                card.remove();
                calcularTotales();
            }, 400);
        }
    });

    // Función para asignar eventos a los campos de un detalle
    function asignarEventosDetalle(detalleCard) {
        const cantidadInput = detalleCard.querySelector('.detalle-cantidad');
        const precioInput = detalleCard.querySelector('.detalle-precio');

        if (cantidadInput) {
            cantidadInput.addEventListener('input', calcularTotales);
        }

        if (precioInput) {
            precioInput.addEventListener('input', calcularTotales);
        }
    }

    // Asignar eventos a los detalles existentes
    document.querySelectorAll('.detalle-card').forEach(card => {
        asignarEventosDetalle(card);
    });

    // Calcular totales iniciales
    calcularTotales();

    // Animación suave para las secciones
    animateElements('.form-section', 200, 'Y');

    // Validación de formulario específica
    form.addEventListener('submit', function (event) {
        const detalles = document.querySelectorAll('.detalle-card');
        if (detalles.length === 0) {
            event.preventDefault();
            alert('Debe agregar al menos un detalle de venta');
            return;
        }

        // Validar que todos los detalles tengan producto, cantidad y precio
        let detallesValidos = true;
        detalles.forEach((detalle, index) => {
            const producto = detalle.querySelector('.detalle-producto');
            const cantidad = detalle.querySelector('.detalle-cantidad');
            const precio = detalle.querySelector('.detalle-precio');

            if (!producto.value || !cantidad.value || !precio.value) {
                detallesValidos = false;
                // Resaltar campos inválidos
                if (!producto.value) producto.classList.add('is-invalid');
                if (!cantidad.value) cantidad.classList.add('is-invalid');
                if (!precio.value) precio.classList.add('is-invalid');
            }
        });

        if (!detallesValidos) {
            event.preventDefault();
            alert('Todos los detalles deben tener producto, cantidad y precio unitario completos');
        }
    });
});
