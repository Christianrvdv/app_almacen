/**
 * public/js/utils/animate.js
 * Módulo para animaciones reutilizables.
 */
export function animateElements(selector, delayMultiplier = 200, direction = 'Y') {
    const elements = document.querySelectorAll(selector);
    elements.forEach((element, index) => {
        element.style.opacity = '0';

        if (direction === 'Y') {
            element.style.transform = 'translateY(20px)';
        } else if (direction === 'X') {
            element.style.transform = 'translateX(-20px)';
        }

        setTimeout(() => {
            element.style.transition = 'all 0.6s ease';
            element.style.opacity = '1';
            element.style.transform = 'translate(0, 0)';
        }, index * delayMultiplier);
    });
}
