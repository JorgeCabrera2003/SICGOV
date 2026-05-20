/**
 * ==============================================================================
 * CONTROLADOR DE REPORTES ESTADÍSTICOS - SICGOV
 * Integra: Consumo AJAX + Chart.js Premium + Soporte Dinámico Dark Mode
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // Referencias de loaders
    const loaders = {
        tiempo: document.getElementById('loader-reservas-tiempo'),
        estado: document.getElementById('loader-reservas-estado'),
        asistencia: document.getElementById('loader-asistencia'),
        menu: document.getElementById('loader-menu'),
        bitacora: document.getElementById('loader-bitacora'),
        productosTop: document.getElementById('loader-productos-top'),
        metodosPago: document.getElementById('loader-metodos-pago'),
        mesasPopularidad: document.getElementById('loader-mesas-popularidad'),
        ingredientesAlerta: document.getElementById('loader-ingredientes-alerta')
    };

    // Almacenamiento de instancias de gráficos para poder destruirlas y recrearlas al cambiar de tema
    const chartInstances = {
        tiempo: null,
        estado: null,
        asistencia: null,
        menu: null,
        bitacora: null,
        productosTop: null,
        metodosPago: null,
        mesasPopularidad: null,
        ingredientesAlerta: null
    };

    let rawData = null; // Guardar datos para redibujar al cambiar de tema

    // Función para obtener colores y configuraciones según el tema activo
    function getThemeColors() {
        const isDark = document.body.classList.contains('dark-mode');
        return {
            isDark: isDark,
            text: isDark ? '#b2bec3' : '#2d3436',
            grid: isDark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(0, 0, 0, 0.05)',
            border: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.08)',
            tooltipBg: isDark ? '#1e272e' : '#ffffff',
            tooltipText: isDark ? '#ffffff' : '#2d3436'
        };
    }

    // Opciones base comunes para los gráficos
    function getBaseOptions(titleText, theme) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        color: theme.text,
                        font: { family: 'Outfit, Inter, sans-serif', size: 11, weight: '500' },
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipText,
                    bodyColor: theme.tooltipText,
                    borderColor: theme.border,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    boxPadding: 6,
                    titleFont: { family: 'Outfit, sans-serif', weight: 'bold' },
                    bodyFont: { family: 'Inter, sans-serif' }
                }
            }
        };
    }

    // Inicializador principal de las gráficas
    function renderAllCharts(data) {
        const theme = getThemeColors();

        // --- 1. Gráfico de Reservaciones en el Tiempo (Línea) ---
        if (chartInstances.tiempo) chartInstances.tiempo.destroy();
        const ctxTiempo = document.getElementById('chart-reservas-tiempo').getContext('2d');
        
        // Gradiente para el área bajo la curva
        const gradTiempo = ctxTiempo.createLinearGradient(0, 0, 0, 300);
        gradTiempo.addColorStop(0, 'rgba(9, 132, 227, 0.4)');
        gradTiempo.addColorStop(1, 'rgba(9, 132, 227, 0.0)');

        chartInstances.tiempo = new Chart(ctxTiempo, {
            type: 'line',
            data: {
                labels: data.reservacionesMes.labels.map(m => {
                    const [year, month] = m.split('-');
                    const date = new Date(year, month - 1);
                    return date.toLocaleDateString('es-ES', { month: 'short', year: '2-digit' }).toUpperCase();
                }),
                datasets: [{
                    label: 'Reservas Registradas',
                    data: data.reservacionesMes.values,
                    borderColor: '#0984e3',
                    borderWidth: 3,
                    backgroundColor: gradTiempo,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0984e3',
                    pointBorderColor: theme.tooltipBg,
                    pointHoverRadius: 7,
                    pointRadius: 4
                }]
            },
            options: {
                ...getBaseOptions('Reservas en el tiempo', theme),
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: theme.text, font: { family: 'Inter, sans-serif', size: 10 } }
                    },
                    y: {
                        grid: { color: theme.grid },
                        ticks: { 
                            color: theme.text, 
                            font: { family: 'Inter, sans-serif', size: 10 },
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
        if (loaders.tiempo) loaders.tiempo.classList.add('loaded');

        // --- 2. Gráfico de Reservaciones por Estado (Dona) ---
        if (chartInstances.estado) chartInstances.estado.destroy();
        const ctxEstado = document.getElementById('chart-reservas-estado').getContext('2d');
        chartInstances.estado = new Chart(ctxEstado, {
            type: 'doughnut',
            data: {
                labels: data.reservacionesEstado.labels,
                datasets: [{
                    data: data.reservacionesEstado.values,
                    backgroundColor: [
                        'rgba(253, 121, 168, 0.85)', // PENDIENTE - Rosa
                        'rgba(9, 132, 227, 0.85)',   // CONFIRMADA - Azul
                        'rgba(214, 48, 49, 0.85)',   // CANCELADA - Rojo
                        'rgba(0, 184, 148, 0.85)'    // COMPLETADA - Verde
                    ],
                    borderColor: theme.tooltipBg,
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                ...getBaseOptions('Estados', theme),
                cutout: '65%'
            }
        });
        if (loaders.estado) loaders.estado.classList.add('loaded');

        // --- 3. Gráfico de Asistencia de Personal (Torta) ---
        if (chartInstances.asistencia) chartInstances.asistencia.destroy();
        const ctxAsistencia = document.getElementById('chart-asistencia').getContext('2d');
        chartInstances.asistencia = new Chart(ctxAsistencia, {
            type: 'pie',
            data: {
                labels: data.asistenciasEstado.labels,
                datasets: [{
                    data: data.asistenciasEstado.values,
                    backgroundColor: [
                        'rgba(0, 184, 148, 0.85)',   // A_TIEMPO
                        'rgba(253, 203, 110, 0.85)',  // TARDE
                        'rgba(225, 112, 85, 0.85)'    // FALTA
                    ],
                    borderColor: theme.tooltipBg,
                    borderWidth: 2
                }]
            },
            options: getBaseOptions('Asistencia', theme)
        });
        if (loaders.asistencia) loaders.asistencia.classList.add('loaded');

        // --- 4. Gráfico de Variedad de Menú por Categorías (Barra Vertical) ---
        if (chartInstances.menu) chartInstances.menu.destroy();
        const ctxMenu = document.getElementById('chart-menu').getContext('2d');
        chartInstances.menu = new Chart(ctxMenu, {
            type: 'bar',
            data: {
                labels: data.productosCategoria.labels,
                datasets: [{
                    label: 'Cantidad de Platos',
                    data: data.productosCategoria.values,
                    backgroundColor: 'rgba(108, 92, 231, 0.8)',
                    borderColor: '#6c5ce7',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                ...getBaseOptions('Menú', theme),
                plugins: {
                    ...getBaseOptions('Menú', theme).plugins,
                    legend: { display: false } // No hace falta leyenda para 1 solo dataset
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: theme.text, font: { size: 10 } }
                    },
                    y: {
                        grid: { color: theme.grid },
                        ticks: { color: theme.text, stepSize: 1, precision: 0 }
                    }
                }
            }
        });
        if (loaders.menu) loaders.menu.classList.add('loaded');

        // --- 5. Gráfico de Actividad del Sistema por Módulo (Barra Horizontal) ---
        if (chartInstances.bitacora) chartInstances.bitacora.destroy();
        const ctxBitacora = document.getElementById('chart-bitacora').getContext('2d');
        chartInstances.bitacora = new Chart(ctxBitacora, {
            type: 'bar',
            data: {
                labels: data.bitacoraActividad.labels,
                datasets: [{
                    label: 'Registros de Auditoría',
                    data: data.bitacoraActividad.values,
                    backgroundColor: 'rgba(225, 112, 85, 0.8)',
                    borderColor: '#e17055',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                ...getBaseOptions('Auditoría', theme),
                indexAxis: 'y', // Convertir en barras horizontales
                plugins: {
                    ...getBaseOptions('Auditoría', theme).plugins,
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: theme.grid },
                        ticks: { color: theme.text }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: theme.text, font: { size: 10 } }
                    }
                }
            }
        });
        if (loaders.bitacora) loaders.bitacora.classList.add('loaded');

        // --- 6. Gráfico de Top 5 Productos Más Vendidos (Barra Horizontal) ---
        if (chartInstances.productosTop) chartInstances.productosTop.destroy();
        const ctxProductosTop = document.getElementById('chart-productos-top').getContext('2d');
        
        // Gradiente horizontal premium para productos top
        const gradProdTop = ctxProductosTop.createLinearGradient(0, 0, 400, 0);
        gradProdTop.addColorStop(0, 'rgba(255, 71, 87, 0.85)');
        gradProdTop.addColorStop(1, 'rgba(255, 71, 87, 0.15)');

        chartInstances.productosTop = new Chart(ctxProductosTop, {
            type: 'bar',
            data: {
                labels: data.topProductos.labels,
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: data.topProductos.values,
                    backgroundColor: gradProdTop,
                    borderColor: '#ff4757',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                ...getBaseOptions('Productos Top', theme),
                indexAxis: 'y', // Convertir en barras horizontales
                plugins: {
                    ...getBaseOptions('Productos Top', theme).plugins,
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: theme.grid },
                        ticks: { color: theme.text, precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: theme.text, font: { size: 10 } }
                    }
                }
            }
        });
        if (loaders.productosTop) loaders.productosTop.classList.add('loaded');

        // --- 7. Gráfico de Métodos de Pago (Dona) ---
        if (chartInstances.metodosPago) chartInstances.metodosPago.destroy();
        const ctxMetodosPago = document.getElementById('chart-metodos-pago').getContext('2d');
        chartInstances.metodosPago = new Chart(ctxMetodosPago, {
            type: 'doughnut',
            data: {
                labels: data.metodosPago.labels,
                datasets: [{
                    data: data.metodosPago.values,
                    backgroundColor: [
                        'rgba(46, 213, 115, 0.85)',   // Verde
                        'rgba(9, 132, 227, 0.85)',     // Azul
                        'rgba(108, 92, 231, 0.85)',    // Morado
                        'rgba(255, 159, 67, 0.85)',    // Naranja
                        'rgba(253, 121, 168, 0.85)'    // Rosa
                    ],
                    borderColor: theme.tooltipBg,
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                ...getBaseOptions('Métodos de Pago', theme),
                cutout: '65%'
            }
        });
        if (loaders.metodosPago) loaders.metodosPago.classList.add('loaded');

        // --- 8. Gráfico de Popularidad de Mesas (Radar) ---
        if (chartInstances.mesasPopularidad) chartInstances.mesasPopularidad.destroy();
        const ctxMesasPopularidad = document.getElementById('chart-mesas-popularidad').getContext('2d');
        
        chartInstances.mesasPopularidad = new Chart(ctxMesasPopularidad, {
            type: 'radar',
            data: {
                labels: data.mesasPopularidad.labels,
                datasets: [{
                    label: 'Reservas Totales',
                    data: data.mesasPopularidad.values,
                    backgroundColor: theme.isDark ? 'rgba(9, 132, 227, 0.25)' : 'rgba(9, 132, 227, 0.12)',
                    borderColor: '#0984e3',
                    borderWidth: 2,
                    pointBackgroundColor: '#0984e3',
                    pointBorderColor: theme.tooltipBg,
                    pointHoverBackgroundColor: theme.tooltipBg,
                    pointHoverBorderColor: '#0984e3',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...getBaseOptions('Popularidad de Mesas', theme),
                scales: {
                    r: {
                        angleLines: { color: theme.grid },
                        grid: { color: theme.grid },
                        pointLabels: {
                            color: theme.text,
                            font: { family: 'Outfit, sans-serif', size: 10, weight: '600' }
                        },
                        ticks: {
                            backdropColor: 'transparent',
                            color: theme.text,
                            font: { size: 9 },
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
        if (loaders.mesasPopularidad) loaders.mesasPopularidad.classList.add('loaded');

        // --- 9. Gráfico de Alerta de Stock de Ingredientes (Doble Barra) ---
        if (chartInstances.ingredientesAlerta) chartInstances.ingredientesAlerta.destroy();
        const ctxIngredientesAlerta = document.getElementById('chart-ingredientes-alerta').getContext('2d');
        
        chartInstances.ingredientesAlerta = new Chart(ctxIngredientesAlerta, {
            type: 'bar',
            data: {
                labels: data.ingredientesAlerta.labels,
                datasets: [
                    {
                        label: 'Stock Actual',
                        data: data.ingredientesAlerta.actual,
                        backgroundColor: 'rgba(253, 126, 20, 0.85)', // Naranja de advertencia
                        borderColor: '#fd7e14',
                        borderWidth: 1.5,
                        borderRadius: 4
                    },
                    {
                        label: 'Stock Mínimo',
                        data: data.ingredientesAlerta.minimo,
                        backgroundColor: theme.isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.1)',
                        borderColor: theme.isDark ? 'rgba(255, 255, 255, 0.35)' : 'rgba(0, 0, 0, 0.2)',
                        borderWidth: 1.5,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                ...getBaseOptions('Stock Crítico', theme),
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: theme.text, font: { size: 10 } }
                    },
                    y: {
                        grid: { color: theme.grid },
                        ticks: { color: theme.text }
                    }
                }
            }
        });
        if (loaders.ingredientesAlerta) loaders.ingredientesAlerta.classList.add('loaded');
    }

    // Efecto de contador incremental para las tarjetas de KPIs
    function animateValue(id, start, end, duration, isCurrency = false, isPercent = false) {
        const obj = document.getElementById(id);
        if (!obj) return;
        if (start === end) {
            obj.innerHTML = isCurrency ? end.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : end;
            return;
        }
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const current = progress * (end - start) + start;
            if (isCurrency) {
                obj.innerHTML = current.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else if (isPercent) {
                obj.innerHTML = current.toFixed(1);
            } else {
                obj.innerHTML = Math.floor(current);
            }
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Consultar datos de estadísticas vía AJAX
    function loadData() {
        const url = `${BASE_URL}/?page=estadistica&action=data`;
        
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Error al conectar con la base de datos');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    rawData = data;

                    // Animar los KPIs
                    animateValue('kpi-reservaciones', 0, data.kpis.totalReservaciones, 800);
                    animateValue('kpi-clientes', 0, data.kpis.totalClientes, 800);
                    animateValue('kpi-productos', 0, data.kpis.totalProductos, 800);
                    animateValue('kpi-asistencias', 0, data.kpis.asistenciasHoy, 800);
                    
                    // KPIs Financieros y Operacionales Nuevos
                    animateValue('kpi-ganancias', 0, data.kpis.gananciasTotales, 800, true);
                    animateValue('kpi-ocupacion-mesas', 0, data.kpis.porcentajeOcupacion, 800, false, true);

                    // Producto Estrella
                    const prodEstrella = data.kpis.productoEstrella;
                    const prodEstrellaNombre = document.getElementById('kpi-producto-estrella-nombre');
                    const prodEstrellaDetalles = document.getElementById('kpi-producto-estrella-detalles');
                    if (prodEstrellaNombre) prodEstrellaNombre.textContent = prodEstrella.nombre;
                    if (prodEstrellaDetalles) prodEstrellaDetalles.textContent = `${prodEstrella.cantidad} unidades vendidas`;

                    // Cliente Top
                    const clienteTop = data.kpis.clienteTop;
                    const clienteTopNombre = document.getElementById('kpi-cliente-top-nombre');
                    const clienteTopDetalles = document.getElementById('kpi-cliente-top-detalles');
                    if (clienteTopNombre) clienteTopNombre.textContent = clienteTop.nombre;
                    if (clienteTopDetalles) {
                        const txtConsumos = clienteTop.tipo === 'reservas' ? 'reservaciones' : 'consumos';
                        clienteTopDetalles.textContent = `${clienteTop.cantidad} ${txtConsumos}`;
                    }

                    // Dibujar gráficos
                    renderAllCharts(data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Datos',
                        text: data.message || 'No se pudieron recuperar las estadísticas.',
                        confirmButtonText: 'Entendido'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Fallo Crítico',
                    text: 'No se pudo conectar con el servidor para obtener los datos estadísticos.',
                    confirmButtonText: 'Entendido'
                });
            });
    }

    // Ejecutar carga inicial de datos
    loadData();

    // Redibujar gráficos de forma fluida si cambia el tema
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            // Añadir un micro-delay de 50ms para esperar que el script global de temas complete la transición de clase del body
            setTimeout(() => {
                if (rawData) {
                    renderAllCharts(rawData);
                }
            }, 100);
        });
    }
});
