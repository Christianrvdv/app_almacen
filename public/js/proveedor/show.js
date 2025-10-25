// public/js/proveedor/show.js
import { animateElements } from '../utils/animate.js';

/**
 * Inicializa los efectos de hover para los elementos de estadísticas.
 */
function initStatHoverEffects() {
    const statItems = document.querySelectorAll('.stat-item');
    statItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });

        item.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.transition = 'transform 0.3s ease';
        });
    });
}

export function initProveedorShowPage() {
    // Animación de entrada para tarjetas de información y estadísticas
    animateElements('.info-card, .stat-item', 200, 'Y');

    // Animación de entrada para las filas de la tabla de productos
    animateElements('tbody tr', 100, 'X');

    // Efecto hover para las estadísticas
    initStatHoverEffects();
}
