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
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            // Validar campo al perder foco si es requerido
            if (this.hasAttribute('required')) {
                validateRequiredField(this);
            }
        });
    });
}

/**
 * Valida campos requeridos
 */
function validateRequiredField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');

    if (isRequired && !value) {
        field.classList.add('is-invalid');
        showFieldError(field, 'Este campo es obligatorio');
        return false;
    } else {
        field.classList.remove('is-invalid');
        removeFieldError(field);
        return true;
    }
}

/**
 * Muestra mensaje de error para un campo
 */
function showFieldError(field, message) {
    let errorElement = field.parentElement.querySelector('.field-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback field-error';
        field.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = message;
}

/**
 * Elimina mensaje de error de un campo
 */
function removeFieldError(field) {
    const errorElement = field.parentElement.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Implementa la validación de email personalizada en el evento 'blur'.
 */
function initCustomEmailValidation() {
    const emailInput = document.getElementById('proveedor_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const value = this.value.trim();

            // Si está vacío y es requerido, mostrar error de requerido
            if (this.hasAttribute('required') && !value) {
                this.classList.add('is-invalid');
                showFieldError(this, 'El email es obligatorio');
                return;
            }

            // Si tiene valor pero no es válido, mostrar error de formato
            if (value && !isValidEmail(value)) {
                this.classList.add('is-invalid');
                showFieldError(this, 'Por favor ingresa un email válido');
            } else {
                this.classList.remove('is-invalid');
                removeFieldError(this);
            }
        });
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
}

/**
 * Validación del formulario antes del envío
 */
function initFormSubmissionValidation() {
    const form = document.querySelector('form.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;

            // Validar todos los campos requeridos
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!validateRequiredField(field)) {
                    isValid = false;
                }
            });

            // Validar email si existe y tiene valor
            const emailField = document.getElementById('proveedor_email');
            if (emailField && emailField.value.trim() && !isValidEmail(emailField.value.trim())) {
                emailField.classList.add('is-invalid');
                showFieldError(emailField, 'Por favor ingresa un email válido');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();

                // Mostrar alerta de error
                const firstErrorField = form.querySelector('.is-invalid');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            form.classList.add('was-validated');
        });
    }
}

export function initProveedorFormPage() {
    // Inicialización de la validación de Bootstrap/Custom
    initFormValidation();

    // Validación personalizada del envío del formulario
    initFormSubmissionValidation();

    // Animación suave para las secciones del formulario
    animateElements('.form-section', 200, 'Y');

    // Efectos de realce en inputs
    initFocusEffects();

    // Validación de email específica (solo para campos con ese ID, útil en Edit)
    initCustomEmailValidation();
}
