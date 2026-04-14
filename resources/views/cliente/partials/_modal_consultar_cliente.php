<!-- ==========================================
    MODAL PARA CONSULTAR CLIENTE
    ========================================== -->

<div class="modal fade" id="modalConsultarCliente" tabindex="-1" aria-labelledby="modalConsultarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary border-bottom-0 text-white">
                <h5 class="modal-title fw-bold" id="modalConsultarClienteLabel">
                    <i class="fas fa-eye text-white me-2"></i>
                    Consulta de Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body pb-0">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Cédula</label>
                        <p class="fs-6 fw-bold" id="c_cedula"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Fecha de Nacimiento</label>
                        <p class="fs-6 fw-bold" id="c_fecha_nacimiento"></p>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Nombre Completo</label>
                        <p class="fs-6 fw-bold" id="c_nombre_apellido"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Edad</label>
                        <p class="fs-6 fw-bold" id="c_edad"></p>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Teléfono</label>
                        <p class="fs-6 fw-bold" id="c_telefono"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Sexo</label>
                        <p class="fs-6 fw-bold" id="c_sexo"></p>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Correo</label>
                        <p class="fs-6 fw-bold" id="c_correo"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-0">Dirección</label>
                        <p class="fs-6 fw-bold" id="c_direccion"></p>
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-muted mb-0">Fecha de Registro en el Sistema</label>
                        <p class="fs-6 fw-bold" id="c_fecha_registro"></p>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
