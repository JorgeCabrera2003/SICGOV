<!-- ==========================================
    MODAL DE HORARIO DEL EMPLEADO
    ========================================== -->

<div class="modal fade" id="modalHorarioEmpleado" tabindex="-1" aria-labelledby="modalHorarioEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalHorarioEmpleadoLabel">
                    <i class="fas fa-user-clock text-warning me-2"></i>
                    Horario de <span id="nombreEmpleadoTitulo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="card border">
                    <div class="card-header bg-light py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMesAnteriorEmpleado" aria-label="Mes anterior">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="fw-bold fs-6" id="tituloMesEmpleado"></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMesSiguienteEmpleado" aria-label="Mes siguiente">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div id="calendarioDiasEmpleado" class="d-grid calendario-grid" style="grid-template-columns: 2.5rem repeat(7, minmax(0, 1fr));"></div>
                    </div>
                    <div class="card-footer bg-light py-2">
                        <div id="leyendaHorariosEmpleado" class="d-flex flex-wrap gap-3"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 d-flex justify-content-between">
                <div class="d-flex gap-2">
                    <span id="detalleTurno" class="badge fs-6 px-3 py-2"></span>
                    <span id="detalleDiasAsignados" class="badge bg-primary fs-6 px-3 py-2"></span>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #calendarioDiasEmpleado .dia-calendario {
        aspect-ratio: 1;
        border: 2px solid transparent;
        border-radius: 0.375rem;
        font-size: 0.85rem;
        user-select: none;
    }
    #calendarioDiasEmpleado .dia-calendario.hoy {
        border-color: #0d6efd;
    }
    #calendarioDiasEmpleado .dia-calendario.fin-semana {
        color: #dc3545;
    }
    #calendarioDiasEmpleado .dia-calendario.empleado-asignado {
        cursor: pointer;
    }
    #calendarioDiasEmpleado .dia-calendario.empleado-asignado:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    #calendarioDiasEmpleado .dia-calendario.otro-mes {
        opacity: 0.2;
    }
    #calendarioDiasEmpleado .selector-calendario {
        align-self: center;
        background: transparent;
        border: 0;
        color: #6c757d;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem;
        text-align: center;
    }
    #calendarioDiasEmpleado .selector-esquina {
        min-height: 1.75rem;
    }
    #leyendaHorariosEmpleado .leyenda-item {
        align-items: center;
        display: flex;
        font-size: 0.8rem;
        gap: 4px;
    }
    #leyendaHorariosEmpleado .leyenda-color {
        border-radius: 4px;
        display: inline-block;
        height: 16px;
        width: 16px;
    }
</style>