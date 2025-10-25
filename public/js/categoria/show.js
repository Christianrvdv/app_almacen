/**
 * public/js/categoria/show.js
 * Lógica específica para la página de detalles de Categoría.
 */
import { animateElements } from '../utils/animate.js';

export async function initShowPage(totalProductos, productosActivos) {
    // 1. Animación suave para las tarjetas
    animateElements('.stats-card, .detalle-card, .stat-item', 200, 'Y');

    // 2. Simulación de carga de estadísticas
    const statProductos = document.getElementById('stat-productos');
    const statActivos = document.getElementById('stat-activos');

    // Simular un pequeño retardo de carga para el efecto visual
    await new Promise(resolve => setTimeout(resolve, 500));

    if (statProductos) {
        statProductos.querySelector('.stat-number').textContent = totalProductos;
    }
    if (statActivos) {
        statActivos.querySelector('.stat-number').textContent = productosActivos;
    }

    // Remover clase de loading
    document.querySelectorAll('.stat-item').forEach(item => {
        item.classList.remove('loading');
    });

    // 3. Log de consola
    console.log(`Detalles de la categoría: ${totalProductos} productos (${productosActivos} activos).`);
}
