<!-- ==========================================================================
     CENTRO DE NOTIFICACIONES - GOOD VIBES
     HTML Semántico + Bootstrap 5.3 + Integración con módulo ES6
     ========================================================================== -->

<main class="container py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-warning-subtle rounded-3 text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-bell-fill fs-4"></i>
            </div>
            <div>
                <h1 class="h3 mb-0 fw-bold text-body">Centro de Notificaciones</h1>
                <p class="text-muted small mb-0">Revisa tus alertas y actualizaciones del sistema</p>
            </div>
        </div>
        <div>
            <button class="btn btn-warning fw-semibold shadow-sm text-dark d-flex align-items-center gap-2" id="btnPageMarkAllRead">
                <i class="bi bi-check2-all fs-5"></i>
                <span>Marcar todas como leídas</span>
            </button>
        </div>
    </header>

    <!-- Lista de notificaciones (section semántica) -->
    <section class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="notificacion__lista-principal" id="pageNotificationList">
                <!-- Carga dinámica vía public/assets/js/modulo_notificaciones.js -->
                <div class="notificacion__vacio py-5">
                    <div class="spinner-border text-warning mb-3" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <span class="d-block text-muted">Cargando tus notificaciones...</span>
                </div>
            </div>
        </div>
    </section>
</main>
