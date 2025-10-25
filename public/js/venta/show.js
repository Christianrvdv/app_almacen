// public/js/venta/show.js
import { animateElements } from '../utils/animate.js';

export function initVentaShowPage() {
    animateElements('.stats-card, .info-item', 200, 'Y');

    animateElements('.table-show tbody tr', 100, 'X', 600);
}
