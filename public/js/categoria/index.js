/**
 * public/js/categoria/index.js
 * Lógica específica para la página de listado de Categorías.
 */
import { animateElements } from '../utils/animate.js';

export function initIndexPage() {
    // 1. Manejar búsqueda con el servidor
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');

    if (searchInput && searchForm) {
        // Opcional: búsqueda en tiempo real con debounce
        let searchTimeout;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Enviar formulario después de 800ms sin escribir
                searchForm.submit();
            }, 800);
        });

        // También permitir búsqueda con Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchForm.submit();
            }
        });
    }

    // 2. Animación suave para las filas de la tabla (X)
    animateElements('tbody tr', 100, 'X');
    // 3. Animación para tarjetas de estadísticas rápidas (Y)
    animateElements('.stats-card', 200, 'Y');

    console.log('Listado de Categorías inicializado.');
}

// Función para limpiar búsqueda (definida en ámbito global)
window.clearSearch = function() {
    window.location.href = "{{ path('app_categoria_index') }}";
}
