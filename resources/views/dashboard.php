<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Productos en Menú</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">10</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hamburger fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Mesas Activas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chair fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pedidos Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ingresos del Día</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$450</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bienvenido al Sistema Good Vibes</h6>
                </div>
                <div class="card-body">
                    <p>Hola, <strong><?php echo $datos['nombres']; ?></strong>. El sistema ha sido configurado correctamente y está listo para usar.</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Fecha de hoy: <?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de ejemplo -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ventas del Mes</h6>
                </div>
                <div class="card-body">
                    <div class="grafico-container">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Productos Más Vendidos</h6>
                </div>
                <div class="card-body">
                    <div class="grafico-container">
                        <canvas id="productosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Gráficos
    document.addEventListener('DOMContentLoaded', function() {
        // Ventas del mes
        const ventasCtx = document.getElementById('ventasChart')?.getContext('2d');
        if (ventasCtx) {
            new Chart(ventasCtx, {
                type: 'line',
                data: {
                    labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
                    datasets: [{
                        label: 'Ventas ($)',
                        data: [1200, 1900, 1500, 2100],
                        borderColor: '#7C1D21',
                        backgroundColor: 'rgba(124, 29, 33, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Productos más vendidos
        const productosCtx = document.getElementById('productosChart')?.getContext('2d');
        if (productosCtx) {
            new Chart(productosCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hamburguesas', 'Pizzas', 'Bebidas', 'Postres'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: [
                            '#7C1D21',
                            '#8FD16F',
                            '#F4C542',
                            '#A52D33'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
</script>