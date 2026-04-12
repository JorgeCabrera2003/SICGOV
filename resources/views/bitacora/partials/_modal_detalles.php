<!-- Modal para ver detalles de la bitácora -->
<div class="modal fade" id="modalDetalleBitacora" 
     tabindex="-1" 
     aria-labelledby="modalDetalleBitacoraLabel" 
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom border-warning border-3">
                <h5 class="modal-title" id="modalDetalleBitacoraLabel">
                    <i class="fas fa-history text-warning me-2"></i>
                    <span class="fw-bold">Detalles del Registro</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body" id="detalleBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>