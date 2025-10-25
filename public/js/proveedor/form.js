// public/js/proveedor/new.js
import { initFormValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

/**
 * Añade efectos de realce (clase 'focused') a los grupos de formulario
 * cuando los inputs/selects ganan o pierden foco.
 */
function initFocusEffects() {
    const inputs = document.querySelectorAll('.form-control-modern, .form-select-modern');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            // Busca el contenedor padre que típicamente es .form-group-modern o .input-group
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

/**
 * Implementa la validación de email personalizada en el evento 'blur'.
 */
function initCustomEmailValidation() {
    const emailInput = document.getElementById('proveedor_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.classList.add('is-invalid');
                let errorElement = this.parentElement.querySelector('.email-error');
                if (!errorElement) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'invalid-feedback email-error';
                    this.parentElement.appendChild(errorElement);
                }
                errorElement.textContent = 'Por favor ingresa un email válido';
            } else {
                this.classList.remove('is-invalid');
                const errorElement = this.parentElement.querySelector('.email-error');
                if (errorElement) {
                    errorElement.remove(); // Ocultar el error si es válido
                }
            }
        });
    }

    function isValidEmail(email) {
        const re = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        return re.test(email);
    }
}


export function initProveedorFormPage() {
    // Inicialización de la validación de Bootstrap/Custom
    initFormValidation();

    // Animación suave para las secciones del formulario
    animateElements('.form-section', 200, 'Y');

    // Efectos de realce en inputs
    initFocusEffects();

    // Validación de email específica (solo para campos con ese ID, útil en Edit)
    initCustomEmailValidation();
}
