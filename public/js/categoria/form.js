/**
 * public/js/categoria/new.js
 * Lógica específica para los formularios de Categoría (New y Edit).
 */
import { initFormValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

export function initFormPage(nombreInputId, isEdit) {
    // 1. Inicializar la validación genérica
    initFormValidation();

    // 2. Animación suave para las secciones del formulario
    animateElements('.form-section, .stats-card', 200, 'Y');

    // 3. Lógica específica de la página de edición: Efecto de realce
    if (isEdit) {
        const nombreInput = document.getElementById(nombreInputId);
        if (nombreInput) {
            nombreInput.addEventListener('focus', function () {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });

            nombreInput.addEventListener('blur', function () {
                this.parentElement.style.transform = 'scale(1)';
            });
        }
    }
}
