/**
 * SICGOV - Good Vibes Tapas & Bar
 * Módulo JavaScript del Dashboard Ejecutivo
 * Renderizado dinámico de Chart.js con soporte responsivo y cambio de tema
 */

document.addEventListener('DOMContentLoaded', () => {
    const dataContainer = document.getElementById('dashboardData');
    if (!dataContainer) return;

    // Extracción segura de datos serializados desde PHP
    let categoriasData = { labels: [], values: [] };
    let reservacionesData = { labels: [], values: [] };

    try {
        if (dataContainer.dataset.categorias) {
            categoriasData = JSON.parse(dataContainer.dataset.categorias);
        }
        if (dataContainer.dataset.reservaciones) {
            reservacionesData = JSON.parse(dataContainer.dataset.reservaciones);
        }
    } catch (e) {
        console.error('Error parseando datos del Dashboard:', e);
    }

    // Detección de tema (Oscuro / Claro)
    const isDarkMode = () => {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
               document.documentElement.classList.contains('dark');
    };

    const getChartColors = () => {
        const dark = isDarkMode();
        return {
            text: dark ? '#94a3b8' : '#64748b',
            grid: dark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)',
            border: dark ? '#1e293b' : '#ffffff'
        };
    };

    let colors = getChartColors();

    // 1. Gráfico de Tendencia / Evolución Operativa
    const ventasCanvas = document.getElementById('ventasChart');
    let ventasChartInstance = null;

    if (ventasCanvas) {
        const ctx = ventasCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(255, 214, 0, 0.25)');
        gradient.addColorStop(1, 'rgba(255, 214, 0, 0.0)');

        const labels = (reservacionesData.labels && reservacionesData.labels.length > 0)
            ? reservacionesData.labels
            : ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];

        const values = (reservacionesData.values && reservacionesData.values.length > 0)
            ? reservacionesData.values
            : [12, 18, 14, 22];

        ventasChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Operaciones',
                    data: values,
                    borderColor: '#FFD600',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#7C1D21',
                    pointBorderColor: '#FFD600',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#FFD600',
                        bodyColor: '#FFFFFF',
                        borderColor: 'rgba(255, 214, 0, 0.25)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        ticks: { color: colors.text, font: { size: 11 } },
                        grid: { color: colors.grid, drawBorder: false }
                    },
                    y: {
                        ticks: { color: colors.text, font: { size: 11 }, precision: 0 },
                        grid: { color: colors.grid, drawBorder: false },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // 2. Gráfico de Variedad del Menú por Categorías
    const productosCanvas = document.getElementById('productosChart');
    let productosChartInstance = null;

    if (productosCanvas) {
        const ctx = productosCanvas.getContext('2d');
        const palette = ['#FFD600', '#7C1D21', '#10B981', '#06B6D4', '#8B5CF6', '#F97316', '#EC4899', '#3B82F6'];

        const catLabels = (categoriasData.labels && categoriasData.labels.length > 0)
            ? categoriasData.labels
            : ['Menú Principal'];

        const catValues = (categoriasData.values && categoriasData.values.length > 0)
            ? categoriasData.values
            : [1];

        productosChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: palette.slice(0, Math.max(catLabels.length, 1)),
                    borderColor: colors.border,
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: colors.text,
                            boxWidth: 10,
                            padding: 10,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#FFD600',
                        bodyColor: '#FFFFFF',
                        borderColor: 'rgba(255, 214, 0, 0.25)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // Observador para cambios de tema dinámicos
    const themeObserver = new MutationObserver(() => {
        colors = getChartColors();
        if (ventasChartInstance) {
            ventasChartInstance.options.scales.x.ticks.color = colors.text;
            ventasChartInstance.options.scales.x.grid.color = colors.grid;
            ventasChartInstance.options.scales.y.ticks.color = colors.text;
            ventasChartInstance.options.scales.y.grid.color = colors.grid;
            ventasChartInstance.update();
        }
        if (productosChartInstance) {
            productosChartInstance.options.plugins.legend.labels.color = colors.text;
            productosChartInstance.data.datasets[0].borderColor = colors.border;
            productosChartInstance.update();
        }
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-bs-theme', 'class']
    });
});
