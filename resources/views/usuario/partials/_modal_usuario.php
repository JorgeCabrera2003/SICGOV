<!-- ==========================================
    MODAL DE USUARIO - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalUsuarioLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextUsuario"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formUsuario" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php $formContext = 'admin'; include __DIR__ . '/../../partials/_user_form.php'; ?>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnUsuarioForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>