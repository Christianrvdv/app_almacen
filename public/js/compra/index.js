/**
 * public/js/compra/index.js
 * Lógica específica para la página de listado de Compras.
 */
import { animateElements } from '../utils/animate.js';

export function initIndexPage() {
    // 1. Funcionalidad básica de búsqueda
    const searchInput = document.querySelector('input[type="text"]');
    const tableRows = document.querySelectorAll('tbody tr');

    if (searchInput && tableRows.length > 0) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // 2. Animación suave para las filas de la tabla (X)
    animateElements('tbody tr', 100, 'X');
    // 3. Animación para tarjetas de estadísticas rápidas (Y)
    animateElements('.stats-card', 200, 'Y');
}
