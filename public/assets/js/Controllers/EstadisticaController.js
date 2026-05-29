/**
 * ==============================================================================
 * CONTROLADOR DE REPORTES ESTADÍSTICOS - SICGOV
 * Integra: Consumo AJAX + Filtros Avanzados + Chart.js Tipo Dinámico + Dark Mode
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

    // Almacenamiento de instancias de gráficos
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

    // Registro de tipos de gráficos activos por widget
    const chartTypes = {
        tiempo: 'line',
        estado: 'doughnut',
        asistencia: 'pie',
        menu: 'bar',
        bitacora: 'bar',
        productosTop: 'bar',
        metodosPago: 'doughnut',
        mesasPopularidad: 'radar',
        ingredientesAlerta: 'bar'
    };

    let rawData = null; // Guardar datos para redibujar al cambiar de tema o tipo
    let catalogsPopulated = false; // Flag para no duplicar catálogos en selects
    const activeLocalFilters = {
        tiempo: {},
        estado: {},
        asistencia: {},
        menu: {},
        bitacora: {},
        productosTop: {},
        metodosPago: {},
        mesasPopularidad: {},
        ingredientesAlerta: {}
    };

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

    // Retorna la configuración de escalas correcta por tipo de gráfico para evitar fallos de Chart.js
    function getChartScales(type, theme) {
        if (type === 'bar' || type === 'line') {
            return {
                x: {
                    grid: { display: false },
                    ticks: { color: theme.text, font: { family: 'Inter, sans-serif', size: 10 } }
                },
                y: {
                    grid: { color: theme.grid },
                    ticks: { 
                        color: theme.text, 
                        font: { family: 'Inter, sans-serif', size: 10 },
                        precision: 0
                    }
                }
            };
        }
        if (type === 'radar' || type === 'polarArea') {
            return {
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
                        precision: 0
                    }
                }
            };
        }
        return undefined; // doughnut, pie, etc. no llevan escalas
    }

    // Renderizado de un gráfico individual (Destrucción e Instanciación Limpia en Caliente)
    function renderSingleChart(chartName, data, type) {
        const theme = getThemeColors();

        if (chartName === 'tiempo') {
            if (chartInstances.tiempo) chartInstances.tiempo.destroy();
            const ctxTiempo = document.getElementById('chart-reservas-tiempo').getContext('2d');
            
            let datasets = [];
            if (type === 'line') {
                const gradTiempo = ctxTiempo.createLinearGradient(0, 0, 0, 300);
                gradTiempo.addColorStop(0, 'rgba(9, 132, 227, 0.4)');
                gradTiempo.addColorStop(1, 'rgba(9, 132, 227, 0.0)');
                datasets = [{
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
                }];
            } else if (type === 'bar') {
                const gradTiempo = ctxTiempo.createLinearGradient(0, 0, 0, 300);
                gradTiempo.addColorStop(0, 'rgba(9, 132, 227, 0.85)');
                gradTiempo.addColorStop(1, 'rgba(9, 132, 227, 0.2)');
                datasets = [{
                    label: 'Reservas Registradas',
                    data: data.reservacionesMes.values,
                    borderColor: '#0984e3',
                    borderWidth: 1.5,
                    backgroundColor: gradTiempo,
                    borderRadius: 6
                }];
            } else {
                datasets = [{
                    label: 'Reservas Registradas',
                    data: data.reservacionesMes.values,
                    borderColor: '#0984e3',
                    borderWidth: 2,
                    backgroundColor: 'rgba(9, 132, 227, 0.25)',
                    pointBackgroundColor: '#0984e3'
                }];
            }

            chartInstances.tiempo = new Chart(ctxTiempo, {
                type: type,
                data: {
                    labels: data.reservacionesMes.labels.map(m => {
                        if (m.length === 10) {
                            const [year, month, day] = m.split('-');
                            const date = new Date(year, month - 1, day);
                            return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }).toUpperCase();
                        } else {
                            const [year, month] = m.split('-');
                            const date = new Date(year, month - 1);
                            return date.toLocaleDateString('es-ES', { month: 'short', year: '2-digit' }).toUpperCase();
                        }
                    }),
                    datasets: datasets
                },
                options: {
                    ...getBaseOptions('Reservas en el tiempo', theme),
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.tiempo) loaders.tiempo.classList.add('loaded');
        }

        else if (chartName === 'estado') {
            if (chartInstances.estado) chartInstances.estado.destroy();
            const ctxEstado = document.getElementById('chart-reservas-estado').getContext('2d');
            const colors = [
                'rgba(253, 121, 168, 0.85)', // PENDIENTE - Rosa
                'rgba(9, 132, 227, 0.85)',   // CONFIRMADA - Azul
                'rgba(214, 48, 49, 0.85)',   // CANCELADA - Rojo
                'rgba(0, 184, 148, 0.85)'    // COMPLETADA - Verde
            ];

            chartInstances.estado = new Chart(ctxEstado, {
                type: type,
                data: {
                    labels: data.reservacionesEstado.labels,
                    datasets: [{
                        label: 'Reservaciones',
                        data: data.reservacionesEstado.values,
                        backgroundColor: colors,
                        borderColor: theme.tooltipBg,
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    ...getBaseOptions('Estados de Reservas', theme),
                    cutout: type === 'doughnut' ? '65%' : undefined,
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.estado) loaders.estado.classList.add('loaded');
        }

        else if (chartName === 'asistencia') {
            if (chartInstances.asistencia) chartInstances.asistencia.destroy();
            const ctxAsistencia = document.getElementById('chart-asistencia').getContext('2d');
            const colors = [
                'rgba(0, 184, 148, 0.85)',   // A_TIEMPO - Verde
                'rgba(253, 203, 110, 0.85)',  // TARDE - Amarillo
                'rgba(225, 112, 85, 0.85)'    // FALTA - Rojo
            ];

            chartInstances.asistencia = new Chart(ctxAsistencia, {
                type: type,
                data: {
                    labels: data.asistenciasEstado.labels,
                    datasets: [{
                        label: 'Asistencias',
                        data: data.asistenciasEstado.values,
                        backgroundColor: colors,
                        borderColor: theme.tooltipBg,
                        borderWidth: 2
                    }]
                },
                options: {
                    ...getBaseOptions('Asistencia del Personal', theme),
                    cutout: type === 'doughnut' ? '65%' : undefined,
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.asistencia) loaders.asistencia.classList.add('loaded');
        }

        else if (chartName === 'menu') {
            if (chartInstances.menu) chartInstances.menu.destroy();
            const ctxMenu = document.getElementById('chart-menu').getContext('2d');

            chartInstances.menu = new Chart(ctxMenu, {
                type: type,
                data: {
                    labels: data.productosCategoria.labels,
                    datasets: [{
                        label: 'Cantidad de Platos',
                        data: data.productosCategoria.values,
                        backgroundColor: type === 'line' ? 'rgba(108, 92, 231, 0.15)' : 'rgba(108, 92, 231, 0.8)',
                        borderColor: '#6c5ce7',
                        borderWidth: 1.5,
                        fill: type === 'line',
                        borderRadius: type === 'bar' ? 6 : undefined
                    }]
                },
                options: {
                    ...getBaseOptions('Variedad de Menú', theme),
                    plugins: {
                        ...getBaseOptions('Variedad de Menú', theme).plugins,
                        legend: { display: type !== 'bar' && type !== 'line' }
                    },
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.menu) loaders.menu.classList.add('loaded');
        }

        else if (chartName === 'bitacora') {
            if (chartInstances.bitacora) chartInstances.bitacora.destroy();
            const ctxBitacora = document.getElementById('chart-bitacora').getContext('2d');

            chartInstances.bitacora = new Chart(ctxBitacora, {
                type: type,
                data: {
                    labels: data.bitacoraActividad.labels,
                    datasets: [{
                        label: 'Registros de Auditoría',
                        data: data.bitacoraActividad.values,
                        backgroundColor: type === 'line' ? 'rgba(225, 112, 85, 0.15)' : (type === 'bar' ? 'rgba(225, 112, 85, 0.8)' : undefined),
                        borderColor: '#e17055',
                        borderWidth: 1.5,
                        fill: type === 'line',
                        borderRadius: type === 'bar' ? 6 : undefined
                    }]
                },
                options: {
                    ...getBaseOptions('Actividad de Seguridad', theme),
                    indexAxis: type === 'bar' ? 'y' : 'x',
                    plugins: {
                        ...getBaseOptions('Actividad de Seguridad', theme).plugins,
                        legend: { display: type !== 'bar' && type !== 'line' }
                    },
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.bitacora) loaders.bitacora.classList.add('loaded');
        }

        else if (chartName === 'productosTop') {
            if (chartInstances.productosTop) chartInstances.productosTop.destroy();
            const ctxProductosTop = document.getElementById('chart-productos-top').getContext('2d');
            
            const gradProdTop = ctxProductosTop.createLinearGradient(0, 0, 400, 0);
            gradProdTop.addColorStop(0, 'rgba(255, 71, 87, 0.85)');
            gradProdTop.addColorStop(1, 'rgba(255, 71, 87, 0.15)');

            chartInstances.productosTop = new Chart(ctxProductosTop, {
                type: type,
                data: {
                    labels: data.topProductos.labels,
                    datasets: [{
                        label: 'Unidades Vendidas',
                        data: data.topProductos.values,
                        backgroundColor: type === 'bar' ? gradProdTop : 'rgba(255, 71, 87, 0.8)',
                        borderColor: '#ff4757',
                        borderWidth: 1.5,
                        borderRadius: type === 'bar' ? 6 : undefined
                    }]
                },
                options: {
                    ...getBaseOptions('Top 5 Productos Más Vendidos', theme),
                    indexAxis: type === 'bar' ? 'y' : 'x',
                    plugins: {
                        ...getBaseOptions('Top 5 Productos Más Vendidos', theme).plugins,
                        legend: { display: type !== 'bar' && type !== 'line' }
                    },
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.productosTop) loaders.productosTop.classList.add('loaded');
        }

        else if (chartName === 'metodosPago') {
            if (chartInstances.metodosPago) chartInstances.metodosPago.destroy();
            const ctxMetodosPago = document.getElementById('chart-metodos-pago').getContext('2d');
            const colors = [
                'rgba(46, 213, 115, 0.85)',   // Verde
                'rgba(9, 132, 227, 0.85)',     // Azul
                'rgba(108, 92, 231, 0.85)',    // Morado
                'rgba(255, 159, 67, 0.85)',    // Naranja
                'rgba(253, 121, 168, 0.85)'    // Rosa
            ];

            chartInstances.metodosPago = new Chart(ctxMetodosPago, {
                type: type,
                data: {
                    labels: data.metodosPago.labels,
                    datasets: [{
                        label: 'Preferencia de Pago',
                        data: data.metodosPago.values,
                        backgroundColor: colors,
                        borderColor: theme.tooltipBg,
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    ...getBaseOptions('Métodos de Pago', theme),
                    cutout: type === 'doughnut' ? '65%' : undefined,
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.metodosPago) loaders.metodosPago.classList.add('loaded');
        }

        else if (chartName === 'mesasPopularidad') {
            if (chartInstances.mesasPopularidad) chartInstances.mesasPopularidad.destroy();
            const ctxMesasPopularidad = document.getElementById('chart-mesas-popularidad').getContext('2d');

            chartInstances.mesasPopularidad = new Chart(ctxMesasPopularidad, {
                type: type,
                data: {
                    labels: data.mesasPopularidad.labels,
                    datasets: [{
                        label: 'Reservas Totales',
                        data: data.mesasPopularidad.values,
                        backgroundColor: type === 'radar' ? (theme.isDark ? 'rgba(9, 132, 227, 0.25)' : 'rgba(9, 132, 227, 0.12)') : 'rgba(9, 132, 227, 0.8)',
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
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.mesasPopularidad) loaders.mesasPopularidad.classList.add('loaded');
        }

        else if (chartName === 'ingredientesAlerta') {
            if (chartInstances.ingredientesAlerta) chartInstances.ingredientesAlerta.destroy();
            const ctxIngredientesAlerta = document.getElementById('chart-ingredientes-alerta').getContext('2d');

            chartInstances.ingredientesAlerta = new Chart(ctxIngredientesAlerta, {
                type: type,
                data: {
                    labels: data.ingredientesAlerta.labels,
                    datasets: [
                        {
                            label: 'Stock Actual',
                            data: data.ingredientesAlerta.actual,
                            backgroundColor: 'rgba(253, 126, 20, 0.85)',
                            borderColor: '#fd7e14',
                            borderWidth: 1.5,
                            borderRadius: type === 'bar' ? 4 : undefined
                        },
                        {
                            label: 'Stock Mínimo',
                            data: data.ingredientesAlerta.minimo,
                            backgroundColor: theme.isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.1)',
                            borderColor: theme.isDark ? 'rgba(255, 255, 255, 0.35)' : 'rgba(0, 0, 0, 0.2)',
                            borderWidth: 1.5,
                            borderRadius: type === 'bar' ? 4 : undefined
                        }
                    ]
                },
                options: {
                    ...getBaseOptions('Stock Crítico de Ingredientes', theme),
                    scales: getChartScales(type, theme)
                }
            });
            if (loaders.ingredientesAlerta) loaders.ingredientesAlerta.classList.add('loaded');
        }
    }

    // Inicializador principal de las gráficas
    function renderAllCharts(data) {
        Object.keys(chartTypes).forEach(key => {
            renderSingleChart(key, data, chartTypes[key]);
        });
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

    // Poblar de forma dinámica los selectores de filtros globales y locales (con los catálogos del backend)
    function populateFilterCatalogs(catalogs) {
        if (catalogsPopulated) return;

        // 1. Catálogo de Mesas (Global)
        const selectMesa = document.getElementById('reserva_mesa');
        if (selectMesa && catalogs.mesas) {
            catalogs.mesas.forEach(mesa => {
                const opt = document.createElement('option');
                opt.value = mesa.id_mesa;
                opt.textContent = `Mesa #${mesa.numero_mesa}`;
                selectMesa.appendChild(opt);
            });
        }

        // 2. Catálogo de Métodos de Pago (Global)
        const selectPago = document.getElementById('pedido_metodo_pago');
        if (selectPago && catalogs.metodosPago) {
            catalogs.metodosPago.forEach(mp => {
                const opt = document.createElement('option');
                opt.value = mp.id_metodo_pago;
                opt.textContent = mp.nombre;
                selectPago.appendChild(opt);
            });
        }

        // 3. Catálogo de Empleados (Global)
        const selectEmpleado = document.getElementById('asistencia_empleado');
        if (selectEmpleado && catalogs.empleados) {
            catalogs.empleados.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.cedula;
                opt.textContent = `${emp.nombre} ${emp.apellido} (${emp.cedula})`;
                selectEmpleado.appendChild(opt);
            });
        }

        // --- POPULACIÓN DE CATÁLOGOS LOCALES ---
        // 4. Mesas Locales (.local-select-mesas)
        document.querySelectorAll('.local-select-mesas').forEach(select => {
            if (catalogs.mesas) {
                catalogs.mesas.forEach(mesa => {
                    const opt = document.createElement('option');
                    opt.value = mesa.id_mesa;
                    opt.textContent = `Mesa #${mesa.numero_mesa}`;
                    select.appendChild(opt);
                });
            }
        });

        // 5. Empleados Locales (.local-select-empleados)
        document.querySelectorAll('.local-select-empleados').forEach(select => {
            if (catalogs.empleados) {
                catalogs.empleados.forEach(emp => {
                    const opt = document.createElement('option');
                    opt.value = emp.cedula;
                    opt.textContent = `${emp.nombre} ${emp.apellido}`;
                    select.appendChild(opt);
                });
            }
        });

        // 6. Categorías Locales (.local-select-categorias)
        document.querySelectorAll('.local-select-categorias').forEach(select => {
            if (catalogs.categorias) {
                catalogs.categorias.forEach(cat => {
                    const opt = document.createElement('option');
                    opt.value = cat.id_categoria;
                    opt.textContent = cat.nombre;
                    select.appendChild(opt);
                });
            }
        });

        catalogsPopulated = true;
    }

    // Compila todos los parámetros de filtrado activos (globales + locales específicos)
    function getCompiledFilterParams() {
        const params = [];
        
        // 1. Filtros Globales del Panel Principal
        const globalForm = document.getElementById('form-filtros-estadistica');
        if (globalForm) {
            const globalData = new FormData(globalForm);
            for (const [key, value] of globalData.entries()) {
                if (value.trim() !== '') {
                    params.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
                }
            }
        }

        // 2. Filtros Locales de cada Tarjeta
        Object.keys(activeLocalFilters).forEach(chartName => {
            const localFilters = activeLocalFilters[chartName];
            Object.keys(localFilters).forEach(key => {
                const val = localFilters[key];
                if (val.trim() !== '') {
                    params.push(`${encodeURIComponent(key)}=${encodeURIComponent(val)}`);
                }
            });
        });

        return params.join('&');
    }

    // Consultar datos de estadísticas vía AJAX con soporte para query string y refresco aislado
    function loadData(targetChartName = null) {
        // Determinar qué cargador activar. Si es un refresco local, solo cargamos esa tarjeta
        if (targetChartName) {
            if (targetChartName === 'global') {
                Object.keys(loaders).forEach(key => {
                    if (loaders[key]) loaders[key].classList.remove('loaded');
                });
            } else if (loaders[targetChartName]) {
                loaders[targetChartName].classList.remove('loaded');
            }
        } else {
            // Carga inicial completa: mostrar todos los loaders
            Object.keys(loaders).forEach(key => {
                if (loaders[key]) loaders[key].classList.remove('loaded');
            });
        }

        const filterParams = getCompiledFilterParams();
        const url = `${BASE_URL}/?page=estadistica&action=data${filterParams ? '&' + filterParams : ''}`;
        
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Error al conectar con la base de datos');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    rawData = data;

                    // Poblar catálogos en el panel una única vez
                    if (data.catalogs) {
                        populateFilterCatalogs(data.catalogs);
                    }

                    // Animar los KPIs (se animan siempre de forma veloz)
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

                    // Refrescar de forma aislada solo la tarjeta de gráfico afectada (micro-interacción ultra-rápida)
                    if (targetChartName && targetChartName !== 'global') {
                        renderSingleChart(targetChartName, data, chartTypes[targetChartName]);
                        if (loaders[targetChartName]) loaders[targetChartName].classList.add('loaded');
                    } else {
                        // Carga inicial o filtro global: redibujar y apagar todos los loaders
                        renderAllCharts(data);
                        Object.keys(loaders).forEach(key => {
                            if (loaders[key]) loaders[key].classList.add('loaded');
                        });
                    }
                } else {
                    // Apagar loaders en caso de error para evitar loops de carga
                    Object.keys(loaders).forEach(key => {
                        if (loaders[key]) loaders[key].classList.add('loaded');
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Datos',
                        text: data.message || 'No se pudieron recuperar las estadísticas.',
                        confirmButtonText: 'Entendido'
                    });
                }
            })
            .catch(err => {
                Object.keys(loaders).forEach(key => {
                    if (loaders[key]) loaders[key].classList.add('loaded');
                });
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Fallo Crítico',
                    text: 'No se pudo conectar con el servidor para obtener los datos estadísticos.',
                    confirmButtonText: 'Entendido'
                });
            });
    }

    // Inicializar listeners para cambio de tipo de gráfico en caliente (.chart-type-select)
    document.querySelectorAll('.chart-type-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const chartName = e.target.getAttribute('data-chart');
            const newType = e.target.value;
            
            // Registrar cambio
            chartTypes[chartName] = newType;

            // Si hay datos cargados, redibujar de inmediato ese gráfico únicamente
            if (rawData) {
                if (loaders[chartName]) loaders[chartName].classList.remove('loaded');
                setTimeout(() => {
                    renderSingleChart(chartName, rawData, newType);
                }, 150);
            }
        });
    });

    // Envío del Formulario de Filtros Avanzados Globales
    const filterForm = document.getElementById('form-filtros-estadistica');
    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Recopilar parámetros globales para actualizar el badge de control
            const formData = new FormData(filterForm);
            let activeFiltersCount = 0;

            for (const [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    activeFiltersCount++;
                }
            }

            // Actualizar badge de estado de filtros
            const badge = document.getElementById('filter-status-text');
            if (badge) {
                if (activeFiltersCount > 0) {
                    badge.textContent = `${activeFiltersCount} filtros activos`;
                    badge.className = 'badge bg-success text-white px-3 py-2 rounded-pill fw-bold filter-status-badge';
                } else {
                    badge.textContent = 'Filtros inactivos';
                    badge.className = 'badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold filter-status-badge';
                }
            }

            // Lanzar carga AJAX para todo el panel
            loadData('global');
        });
    }

    // Botón Limpiar Filtros Globales (Restablece todo, incluyendo locales para coherencia)
    const btnLimpiar = document.getElementById('btn-limpiar-filtros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            if (filterForm) {
                filterForm.reset();
                
                // Restablecer el estado de todos los filtros locales
                Object.keys(activeLocalFilters).forEach(key => {
                    activeLocalFilters[key] = {};
                    
                    // Resetear el respectivo formulario local
                    const localForm = document.querySelector(`.form-local-filter[data-chart="${key}"]`);
                    if (localForm) localForm.reset();
                    
                    // Apagar indicador visual del botón de embudo
                    const toggleBtn = document.querySelector(`[data-bs-target="#local-filter-${key}"]`);
                    if (toggleBtn) toggleBtn.classList.remove('active-filter');
                });

                // Forzar trigger en badge
                const badge = document.getElementById('filter-status-text');
                if (badge) {
                    badge.textContent = 'Filtros inactivos';
                    badge.className = 'badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold filter-status-badge';
                }

                // Refrescar todo el dashboard limpio
                loadData('global');
            }
        });
    }

    // --- CONTROLADORES DE EVENTOS DE FILTROS LOCALES (NUEVOS) ---

    // Envío de cada Formulario de Filtro Local
    document.querySelectorAll('.form-local-filter').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const chartName = form.getAttribute('data-chart');
            const formData = new FormData(form);
            const localParams = {};
            let hasActiveFilter = false;

            for (const [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    localParams[key] = value;
                    hasActiveFilter = true;
                }
            }

            // Registrar parámetros en el almacén de filtros locales
            activeLocalFilters[chartName] = localParams;

            // Retroalimentación visual premium sobre el botón de embudo
            const toggleBtn = document.querySelector(`[data-bs-target="#local-filter-${chartName}"]`);
            if (toggleBtn) {
                if (hasActiveFilter) {
                    toggleBtn.classList.add('active-filter');
                } else {
                    toggleBtn.classList.remove('active-filter');
                }
            }

            // Disparar recarga aislada rápida para esta tarjeta únicamente
            loadData(chartName);
        });
    });

    // Limpieza de cada Filtro Local Individual
    document.querySelectorAll('.btn-clear-local-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            const chartName = btn.getAttribute('data-chart');
            const form = document.querySelector(`.form-local-filter[data-chart="${chartName}"]`);
            if (form) {
                form.reset();
                
                // Vaciar estado
                activeLocalFilters[chartName] = {};

                // Apagar indicador visual del botón de embudo
                const toggleBtn = document.querySelector(`[data-bs-target="#local-filter-${chartName}"]`);
                if (toggleBtn) {
                    toggleBtn.classList.remove('active-filter');
                }

                // Disparar recarga aislada rápida limpia
                loadData(chartName);
            }
        });
    });

    // Ejecutar carga inicial de datos (por defecto, sin filtros)
    loadData();

    // Redibujar gráficos de forma fluida si cambia el tema
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            setTimeout(() => {
                if (rawData) {
                    renderAllCharts(rawData);
                }
            }, 120);
        });
    }
});

