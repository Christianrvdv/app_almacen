import { initFormValidation } from '../utils/validation.js';
import { animateElements } from '../utils/animate.js';

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráfico después de un breve delay
    setTimeout(initializeChart, 100);

    // Manejar filtro de fecha específica
    handleDateFilter();

    // Inicializar animaciones
    initAnimations();

    // Inicializar validación de formularios si existe
    initFormValidation();
});

function initializeChart() {
    const ctx = document.getElementById('statsChart');
    if (!ctx) return;

    const chartLoading = document.getElementById('chartLoading');
    const chartCanvas = document.getElementById('statsChart');

    // Los datos se pasan desde Twig como variables globales
    const ventasData = window.ventasDiarias || [];
    const comprasData = window.comprasDiarias || [];

    // Procesar datos para el gráfico
    const allDates = [...new Set([
        ...ventasData.map(item => item.dia),
        ...comprasData.map(item => item.dia)
    ])].sort();

    const ventasMap = new Map(ventasData.map(item => [item.dia, parseFloat(item.total)]));
    const comprasMap = new Map(comprasData.map(item => [item.dia, parseFloat(item.total)]));

    const ventasValues = allDates.map(date => ventasMap.get(date) || 0);
    const comprasValues = allDates.map(date => comprasMap.get(date) || 0);

    // Formatear fechas para mejor legibilidad
    const formattedDates = allDates.map(date => {
        const d = new Date(date);
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
    });

    // Configuración del gráfico
    const chartConfig = {
        type: 'bar',
        data: {
            labels: formattedDates,
            datasets: [
                {
                    label: 'Ventas Diarias',
                    data: ventasValues,
                    backgroundColor: 'rgba(67, 97, 238, 0.8)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Compras Diarias',
                    data: comprasValues,
                    backgroundColor: 'rgba(239, 71, 111, 0.8)',
                    borderColor: 'rgba(239, 71, 111, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#2b2d42',
                    bodyColor: '#2b2d42',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.raw.toLocaleString('es-ES');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-ES');
                        },
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 10,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    };

    setTimeout(() => {
        const chart = new Chart(ctx, chartConfig);

        // Ocultar loading y mostrar gráfico
        if (chartLoading) {
            chartLoading.style.display = 'none';
        }
        if (chartCanvas) {
            chartCanvas.style.display = 'block';
        }

        // Manejar toggle entre tipos de gráfico si existe
        const toggleChartType = document.getElementById('toggleChartType');
        if (toggleChartType) {
            toggleChartType.addEventListener('change', function(e) {
                chart.destroy();

                const newConfig = {
                    ...chartConfig,
                    type: e.target.checked ? 'line' : 'bar'
                };

                if (e.target.checked) {
                    newConfig.data.datasets[0].backgroundColor = 'transparent';
                    newConfig.data.datasets[0].pointBackgroundColor = 'rgba(67, 97, 238, 1)';
                    newConfig.data.datasets[0].pointBorderColor = '#fff';
                    newConfig.data.datasets[0].pointBorderWidth = 2;
                    newConfig.data.datasets[0].pointRadius = 4;
                    newConfig.data.datasets[0].pointHoverRadius = 6;
                    newConfig.data.datasets[0].tension = 0.4;

                    newConfig.data.datasets[1].backgroundColor = 'transparent';
                    newConfig.data.datasets[1].pointBackgroundColor = 'rgba(239, 71, 111, 1)';
                    newConfig.data.datasets[1].pointBorderColor = '#fff';
                    newConfig.data.datasets[1].pointBorderWidth = 2;
                    newConfig.data.datasets[1].pointRadius = 4;
                    newConfig.data.datasets[1].pointHoverRadius = 6;
                    newConfig.data.datasets[1].tension = 0.4;
                } else {
                    newConfig.data.datasets[0].backgroundColor = 'rgba(67, 97, 238, 0.8)';
                    newConfig.data.datasets[1].backgroundColor = 'rgba(239, 71, 111, 0.8)';
                }

                new Chart(ctx, newConfig);
            });
        }
    }, 300);
}

function handleDateFilter() {
    const filtro = document.getElementById('filtro');
    const fechaContainer = document.getElementById('fecha-especifica-container');

    if (!filtro || !fechaContainer) return;

    // Mostrar/ocultar campo de fecha específica
    filtro.addEventListener('change', function() {
        if (this.value === 'fecha_especifica') {
            fechaContainer.style.display = 'block';
        } else {
            fechaContainer.style.display = 'none';
        }
    });

    // Inicializar visibilidad del campo fecha específica
    if (filtro.value === 'fecha_especifica') {
        fechaContainer.style.display = 'block';
    }
}

function initAnimations() {
    // Animar elementos de estadísticas
    animateElements('.dashboard-card', 100, 'Y');
    animateElements('.stats-card', 150, 'Y');
    animateElements('.chart-container', 200, 'Y');
    animateElements('.table-modern', 250, 'Y');
}
