<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <!-- Header Simplificado -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 fw-bold text-primary mb-0">Reservar Mesa</h1>
            <p class="text-muted small mb-0">Selecciona el día y la hora para tu visita. Confirmaremos tu solicitud a la brevedad.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button class="btn btn-primary shadow-sm fw-bold px-4 rounded-3" id="btnNuevaReservacion">
                <i class="bi bi-plus-lg me-2"></i>Nueva Reservación
            </button>
        </div>
    </div>

    <!-- Calendario Full Width -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <div id="calendarPublico" style="min-height: 700px;"></div>
        </div>
    </div>

    <!-- Info de estados -->
    <div class="mt-3 d-flex gap-3 justify-content-center">
        <div class="small text-muted"><span class="badge rounded-circle p-1 me-1" style="background-color: #ff9800; width: 10px; height: 10px; display: inline-block;"></span> Pendiente</div>
        <div class="small text-muted"><span class="badge rounded-circle p-1 me-1" style="background-color: #2196f3; width: 10px; height: 10px; display: inline-block;"></span> Confirmada</div>
        <div class="small text-muted"><span class="badge rounded-circle p-1 me-1" style="background: repeating-linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.1) 5px, rgba(255,255,255,0.2) 5px, rgba(255,255,255,0.2) 10px); width: 10px; height: 10px; display: inline-block; border: 1px solid rgba(255,255,255,0.1);"></span> Ocupado</div>
    </div>
</div>

<!-- Modal de Reservación Unificado -->
<div class="modal fade" id="modalPublico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Solicitar Reservación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReservarPublico" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="peticion" value="registrar">
                    
                    <div class="user-info-card d-flex align-items-center gap-3 mb-4">
                        <div class="avatar-wrapper">
                             <i class="bi bi-person text-dark fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold"><?= $datos['nombres'] ?></h6>
                            <span class="text-muted small">Confirmaremos tu cita vía SMS</span>
                        </div>
                    </div>

                    <div class="reservation-form-group mb-4">
                        <label>Fecha de tu visita</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-calendar3"></i></span>
                            <input type="date" class="form-control border-start-0 ps-0" name="fecha" id="fechaPublica" required readonly>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="reservation-form-group">
                                <label>Desde las</label>
                                <input type="text" class="form-control text-center" name="hora" id="horaPublica" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="reservation-form-group">
                                <label>Hasta las</label>
                                <input type="text" class="form-control text-center" name="hora_fin" id="hora_finPublica" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-confirmar-premium w-100">Confirmar Solicitud</button>
                </div>
            </form>

        </div>
    </div>
</div>
