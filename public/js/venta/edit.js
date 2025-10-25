import { animateElements } from '../utils/animate.js';
import { initFormValidation } from '../utils/validation.js';

document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-detalle');
    const detalleVentas = document.getElementById('detalle-ventas');
    const prototype = detalleVentas.getAttribute('data-prototype');
    const totalPreview = document.getElementById('totalPreview');
    const totalInput = document.getElementById('venta_total');
    const form = document.getElementById('venta-edit-form');

    // Función para calcular subtotales y total
    function calcularTotales() {
        let totalVenta = 0;

        document.querySelectorAll('.detalle-card').forEach(card => {
            const index = card.getAttribute('data-index');
            const cantidad = parseFloat(card.querySelector(`.cantidad-input[data-index="${index}"]`).value) || 0;
            const precio = parseFloat(card.querySelector(`.precio-input[data-index="${index}"]`).value) || 0;
            const subtotal = cantidad * precio;

            // Actualizar subtotal
            const subtotalInput = card.querySelector(`.subtotal-input[data-index="${index}"]`);
            if (subtotalInput) {
                subtotalInput.value = subtotal.toFixed(2);
            }

            totalVenta += subtotal;
        });

        // Actualizar total
        totalPreview.textContent = `$${totalVenta.toFixed(2)}`;
        totalInput.value = totalVenta.toFixed(2);
    }

    // Función para asignar eventos a los campos de un detalle
    function asignarEventosDetalle(detalleCard) {
        const index = detalleCard.getAttribute('data-index');
        const cantidadInput = detalleCard.querySelector(`.cantidad-input[data-index="${index}"]`);
        const precioInput = detalleCard.querySelector(`.precio-input[data-index="${index}"]`);

        if (cantidadInput) {
            cantidadInput.addEventListener('input', calcularTotales);
        }

        if (precioInput) {
            precioInput.addEventListener('input', calcularTotales);
        }
    }

    // Agregar nuevo detalle
    addButton.addEventListener('click', function () {
        const index = detalleVentas.getAttribute('data-index') || document.querySelectorAll('.detalle-card').length;
        const newForm = prototype.replace(/__name__/g, index);
        const newRow = document.createElement('div');
        newRow.setAttribute('class', 'detalle-card');
        newRow.setAttribute('data-index', index);
        newRow.innerHTML = newForm;

        // Actualizar los data-index en los campos del nuevo detalle
        newRow.querySelectorAll('input, select, button').forEach(element => {
            if (element.name) {
                element.name = element.name.replace(/__name__/g, index);
                element.id = element.id.replace(/__name__/g, index);
            }
            if (!element.classList.contains('eliminar-detalle')) {
                element.setAttribute('data-index', index);
            }
        });

        // Añadir campos de subtotal
        const subtotalField = `
            <div class="col-md-3 mb-3">
                <div class="form-group-modern">
                    <label class="form-label-modern">Subtotal</label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text input-group-text-modern">$</span>
                        <input type="text" class="form-control form-control-modern subtotal-input" readonly data-index="${index}">
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
        deleteButton.setAttribute('data-index', index);
        deleteButton.innerHTML = '<i class="fas fa-trash me-2"></i>Eliminar Detalle';
        newRow.appendChild(deleteButton);

        detalleVentas.appendChild(newRow);

        // Actualizar el índice para el próximo elemento
        detalleVentas.setAttribute('data-index', parseInt(index) + 1);

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
                // Reindexar los elementos restantes
                document.querySelectorAll('.detalle-card').forEach((card, newIndex) => {
                    card.setAttribute('data-index', newIndex);
                    card.querySelectorAll('input, select, button').forEach(element => {
                        if (element.name && !element.classList.contains('eliminar-detalle')) {
                            element.setAttribute('data-index', newIndex);
                        }
                    });
                });
                calcularTotales();
            }, 400);
        }
    });

    // Asignar eventos a los detalles existentes
    document.querySelectorAll('.detalle-card').forEach(card => {
        asignarEventosDetalle(card);
    });

    // Calcular totales iniciales
    calcularTotales();

    // Animación suave para las secciones
    animateElements('.form-section', 200, 'Y');

    // Validación de formulario
    initFormValidation();
});
