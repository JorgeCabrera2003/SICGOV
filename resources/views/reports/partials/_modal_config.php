<!-- MODAL DE CONFIGURACIÓN DE REPORTE -->
<div class="modal fade" id="modalConfigReporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="tituloConfigModal">Configurar Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" target="_blank" action="<?= BASE_URL ?>/?page=reportes">
                <input type="hidden" name="peticion" value="generar">
                <input type="hidden" name="tipo" id="reportTipo">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Rango de Fechas -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase opacity-50">Rango de Fechas (Opcional)</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="fecha_inicio" title="Fecha Inicio">
                                <span class="input-group-text bg-light">al</span>
                                <input type="date" class="form-control" name="fecha_fin" title="Fecha Fin">
                            </div>
                        </div>

                        <!-- Formato de Papel -->
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase opacity-50">Tamaño Papel</label>
                            <select class="form-select" name="paper">
                                <option value="letter" selected>Carta (Letter)</option>
                                <option value="a4">A4</option>
                                <option value="legal">Oficio (Legal)</option>
                            </select>
                        </div>

                        <!-- Orientación -->
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase opacity-50">Orientación</label>
                            <select class="form-select" name="orientation">
                                <option value="portrait" selected>Vertical</option>
                                <option value="landscape">Horizontal</option>
                            </select>
                        </div>

                        <!-- Título personalizado -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase opacity-50">Nota o Comentario (Inyectado al reporte)</label>
                            <textarea class="form-control" name="resumen" rows="2" placeholder="Ej: Reporte trimestral de ventas..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                        <i class="bi bi-file-earmark-check me-2"></i>Confirmar y Generar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
