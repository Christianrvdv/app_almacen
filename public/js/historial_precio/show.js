import { animateElements } from './animate.js';

document.addEventListener('DOMContentLoaded', function () {
    // Animación para las tarjetas
    const cards = document.querySelectorAll('.detalle-card, .stat-item');
    animateElements('.detalle-card, .stat-item', 150, 'Y');

    // Confirmación para eliminar
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este registro del historial? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });

    // Efectos hover adicionales para mejorar la interactividad
    const statItems = document.querySelectorAll('.stat-item');
    statItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'all 0.3s ease';
        });
        item.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });

    // Animación para el modal cuando se muestra
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function () {
            this.style.opacity = '0';
            setTimeout(() => {
                this.style.transition = 'all 0.3s ease';
                this.style.opacity = '1';
            }, 50);
        });
    }
});
