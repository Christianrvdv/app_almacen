/**
 * public/js/cliente/new.js
 * Lógica específica para los formularios de Cliente (New y Edit).
 */
import { initFormValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

export function initFormPage(formVars) {
    // 1. Inicializar la validación genérica
    initFormValidation();

    const { telefonoId, comprasId, isEdit } = formVars;

    // 2. Animación suave para las secciones del formulario
    animateElements('.form-section', 200, 'Y');

    // 3. Efectos interactivos para los campos (focus/blur)
    const inputs = document.querySelectorAll('.form-control-modern');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.closest('.input-group') ? this.closest('.input-group').classList.add('focused') : this.parentElement.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            this.closest('.input-group') ? this.closest('.input-group').classList.remove('focused') : this.parentElement.parentElement.classList.remove('focused');
        });
    });

    // 4. Formateo automático para el campo de teléfono
    const telefonoInput = document.getElementById(telefonoId);
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                // Formato simple: 3 dígitos separados por espacio
                value = value.match(/.{1,3}/g).join(' ');
                e.target.value = value;
            }
        });
    }

    // 5. Formateo automático para el campo de compras totales (moneda)
    const comprasInput = document.getElementById(comprasId);
    if (comprasInput) {
        comprasInput.addEventListener('blur', function(e) {
            let value = parseFloat(e.target.value.replace(',', ''));
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });
    }

    // 6. Lógica específica de Edición: Detección de cambios
    if (isEdit) {
        const form = document.querySelector('form');
        let hasChanges = false;
        const formElements = form.querySelectorAll('input, textarea, select');

        formElements.forEach(element => {
            element.addEventListener('input', function() {
                if (!hasChanges) {
                    hasChanges = true;
                    console.log('Se han detectado cambios en el formulario del cliente.');
                    // Aquí podrías actualizar un indicador visual de cambios no guardados
                }
            });
        });
    }
}
