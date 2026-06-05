<!-- ==========================================
    MODAL DE ROL - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalRolLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextRol"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formRol" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: Nombre -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6 d-none">
                            <input type="hidden" name="id_rol" id="id_rol">
                            <div class="form-label" id="sid_rol"></div>
                        </div>

                        <div class="col-md-10 position-relative">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre del Rol <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                            <div class="form-label" id="snombre"></div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-12">
                            <div class="mt-4 accordion permissions-section" id="accordionPermisos">
                                <?php include_once 'acordeon/_acordeon_seguridad.php';
                                include_once 'acordeon/_acordeon_areas_mesas.php';
                                include_once 'acordeon/_acordeon_difusion_digital.php';
                                include_once 'acordeon/_acordeon_equipo_horario.php';
                                include_once 'acordeon/_acordeon_menu_recetas.php';
                                include_once 'acordeon/_acordeon_suministros_activos.php';
                                include_once 'acordeon/_acordeon_servicios_citas.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnRolForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>