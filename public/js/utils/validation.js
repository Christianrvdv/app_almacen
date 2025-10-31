/**
 * public/js/utils/validation.js
 * Módulo para validación de formularios genérica.
 */

export function initFormValidation() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
}

// Clase para validar campos numéricos - REUTILIZABLE
export class ValidadorNumeros {
    constructor() {
        this.campos = document.querySelectorAll('.solo-numeros');
        this.iniciar();
    }

    iniciar() {
        this.campos.forEach(campo => {
            campo.addEventListener('input', this.validarInput.bind(this));
            campo.addEventListener('blur', this.formatearValor.bind(this));
            campo.addEventListener('keydown', this.prevenirCaracteresInvalidos.bind(this));
        });
    }

    validarInput(e) {
        const campo = e.target;
        const esDecimal = campo.hasAttribute('step');

        if (esDecimal) {
            this.validarDecimal(campo);
        } else {
            this.validarEntero(campo);
        }
    }

    validarDecimal(campo) {
        // Permite solo números, un punto decimal, y reemplaza comas
        let valor = campo.value.replace(/[^0-9,.]/g, '');
        valor = valor.replace(/,/g, '.');

        // Elimina puntos decimales adicionales
        const partes = valor.split('.');
        if (partes.length > 2) {
            valor = partes[0] + '.' + partes.slice(1).join('');
        }

        campo.value = valor;
    }

    validarEntero(campo) {
        campo.value = campo.value.replace(/[^0-9]/g, '');
    }

    formatearValor(e) {
        const campo = e.target;
        const esDecimal = campo.hasAttribute('step');

        if (!campo.value) return;

        if (esDecimal) {
            const valor = parseFloat(campo.value);
            if (!isNaN(valor)) {
                campo.value = Math.max(0, valor).toFixed(2);
            } else {
                campo.value = '0.00';
            }
        } else {
            const valor = parseInt(campo.value);
            campo.value = isNaN(valor) ? '0' : Math.max(0, valor).toString();
        }
    }

    prevenirCaracteresInvalidos(e) {
        // Permite teclas de control, números y punto (solo para decimales)
        const teclasPermitidas = [
            'Backspace', 'Tab', 'Delete', 'ArrowLeft', 'ArrowRight',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];

        const esDecimal = e.target.hasAttribute('step');
        if (esDecimal) {
            teclasPermitidas.push('.', ',');
        }

        if (!teclasPermitidas.includes(e.key) &&
            !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
        }
    }
}

// Función para inicializar la validación numérica en cualquier formulario
export function initNumerosValidation() {
    new ValidadorNumeros();
}
