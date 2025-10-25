import { animateElements } from '../utils/animate.js';
import { initFormValidation } from '../utils/validation.js';

document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('add-detalle');
    const detalleCompras = document.getElementById('detalle-compras');
    const prototype = detalleCompras.getAttribute('data-prototype');
    const totalPreview = document.getElementById('totalPreview');
    const totalInput = document.getElementById('compra_total');
    const form = document.getElementById('compra-edit-form');

    // Función para calcular subtotales y total
    function calcularTotales() {
        let totalCompra = 0;

        document.querySelectorAll('.detalle-card').forEach(card => {
            const cantidad = parseFloat(card.querySelector('[id$="_cantidad"]').value) || 0;
            const precio = parseFloat(card.querySelector('[id$="_precioUnitario"]').value) || 0;
            const subtotal = cantidad * precio;

            // Actualizar subtotal
            const subtotalInput = card.querySelector('.subtotal-input');
            if (subtotalInput) {
                subtotalInput.value = subtotal.toFixed(2);
            }

            totalCompra += subtotal;
        });

        // Actualizar total
        totalPreview.textContent = `$${totalCompra.toFixed(2)}`;
        totalInput.value = totalCompra.toFixed(2);
    }

    // Función para asignar eventos a los campos de un detalle
    function asignarEventosDetalle(detalleCard) {
        const cantidadInput = detalleCard.querySelector('[id$="_cantidad"]');
        const precioInput = detalleCard.querySelector('[id$="_precioUnitario"]');

        if (cantidadInput) {
            cantidadInput.addEventListener('input', calcularTotales);
        }

        if (precioInput) {
            precioInput.addEventListener('input', calcularTotales);
        }
    }

    // Agregar nuevo detalle
    addButton.addEventListener('click', function() {
        const index = document.querySelectorAll('.detalle-card').length;
        const newForm = prototype.replace(/__name__/g, index);
        const newRow = document.createElement('div');
        newRow.setAttribute('class', 'detalle-card');
        newRow.innerHTML = newForm;

        // Añadir campos de subtotal
        const subtotalField = `
            <div class="col-md-3 mb-3">
                <div class="form-group-modern">
                    <label class="form-label-modern">Subtotal</label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text input-group-text-modern">$</span>
                        <input type="text" class="form-control form-control-modern subtotal-input" readonly>
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

        detalleCompras.appendChild(newRow);

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
    detalleCompras.addEventListener('click', function(e) {
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

    // Asignar eventos a los detalles existentes
    document.querySelectorAll('.detalle-card').forEach(card => {
        asignarEventosDetalle(card);
    });

    // Calcular totales iniciales
    calcularTotales();

    // Animación suave para las secciones
    animateElements('.form-section', 200, 'Y');

    // Inicializar validación de formulario
    initFormValidation();

    // Auto-calcular total cuando cambian los detalles
    document.addEventListener('input', function(e) {
        if (e.target.matches('[id$="_cantidad"], [id$="_precioUnitario"]')) {
            calcularTotales();
        }
    });
});
