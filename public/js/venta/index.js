// public/js/venta/index.js
import { animateElements } from '../utils/animate.js';

function initSearchAndFilter() {
    const searchInput = document.querySelector('input[type="text"]');
    const tableRows = document.querySelectorAll('tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

function initRowAnimations() {
    // Animación suave para las filas de la tabla
    animateElements('tbody tr', 100, 'X');
}

export function initVentaListPage() {
    initSearchAndFilter();
    initRowAnimations();
}
