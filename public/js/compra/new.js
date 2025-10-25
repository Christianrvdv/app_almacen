/**
 * public/js/compra/new.js
 * Módulo para manejar el formulario de compras
 */

import { animateElements } from '../utils/animate.js';
import { initFormValidation } from '../utils/validation.js';

export class CompraForm {
    constructor() {
        this.detalleCompras = document.getElementById('detalle-compras');
        if (!this.detalleCompras) return;

        this.form = document.querySelector('form');
        this.addButton = document.getElementById('add-detalle');
        this.prototype = this.detalleCompras.getAttribute('data-prototype');
        this.totalPreview = document.getElementById('totalPreview');
        this.totalInput = document.getElementById('compra_total');

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.calcularTotales();
        this.animateSections();
        initFormValidation();
    }

    setupEventListeners() {
        // Agregar nuevo detalle
        this.addButton.addEventListener('click', () => this.agregarDetalle());

        // Eliminar detalle
        this.detalleCompras.addEventListener('click', (e) => this.manejarEliminarDetalle(e));

        // Auto-calcular total cuando cambian los detalles
        document.addEventListener('input', (e) => {
            if (e.target.matches('[id$="_cantidad"], [id$="_precioUnitario"]')) {
                this.calcularTotales();
            }
        });
    }

    calcularTotales() {
        let totalCompra = 0;

        document.querySelectorAll('.detalle-card').forEach(card => {
            const cantidadInput = card.querySelector('[id$="_cantidad"]');
            const precioInput = card.querySelector('[id$="_precioUnitario"]');

            if (cantidadInput && precioInput) {
                const cantidad = parseFloat(cantidadInput.value) || 0;
                const precio = parseFloat(precioInput.value) || 0;
                const subtotal = cantidad * precio;

                // Actualizar subtotal
                const subtotalInput = card.querySelector('.subtotal-input');
                if (subtotalInput) {
                    subtotalInput.value = subtotal.toFixed(2);
                }

                totalCompra += subtotal;
            }
        });

        // Actualizar total
        if (this.totalPreview) {
            this.totalPreview.textContent = `$${totalCompra.toFixed(2)}`;
        }
        if (this.totalInput) {
            this.totalInput.value = totalCompra.toFixed(2);
        }
    }

    agregarDetalle() {
        const index = document.querySelectorAll('.detalle-card').length;
        const newForm = this.prototype.replace(/__name__/g, index);
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

        this.detalleCompras.appendChild(newRow);

        // Reasignar eventos a los nuevos campos
        this.asignarEventosDetalle(newRow);

        // Calcular totales
        this.calcularTotales();

        // Animación para el nuevo elemento
        this.animateNewElement(newRow);
    }

    manejarEliminarDetalle(e) {
        if (e.target.classList.contains('eliminar-detalle') ||
            e.target.closest('.eliminar-detalle')) {
            const button = e.target.classList.contains('eliminar-detalle') ?
                e.target : e.target.closest('.eliminar-detalle');
            const card = button.closest('.detalle-card');

            this.animateRemoveElement(card);
        }
    }

    asignarEventosDetalle(detalleCard) {
        const cantidadInput = detalleCard.querySelector('[id$="_cantidad"]');
        const precioInput = detalleCard.querySelector('[id$="_precioUnitario"]');

        if (cantidadInput) {
            cantidadInput.addEventListener('input', () => this.calcularTotales());
        }

        if (precioInput) {
            precioInput.addEventListener('input', () => this.calcularTotales());
        }
    }

    animateSections() {
        animateElements('.form-section', 200, 'Y');
    }

    animateNewElement(element) {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';

        setTimeout(() => {
            element.style.transition = 'all 0.6s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
    }

    animateRemoveElement(element) {
        element.style.transition = 'all 0.4s ease';
        element.style.opacity = '0';
        element.style.transform = 'translateX(100px)';

        setTimeout(() => {
            element.remove();
            this.calcularTotales();
        }, 400);
    }
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    new CompraForm();
});
