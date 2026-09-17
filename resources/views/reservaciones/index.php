<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <!-- Header de la Página -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 fw-bold text-primary mb-0">
                <i class="bi bi-calendar-check me-2"></i>Agenda de Reservaciones
            </h1>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <?php if (isset($permisosReservacion['reservacion']['registrar']) && $permisosReservacion['reservacion']['registrar'] == 1): ?>
            <button class="btn btn-primary shadow-sm fw-bold px-4 rounded-3" id="btnNuevaReservacion">
                <i class="bi bi-plus-lg me-2"></i> Nueva Reservación
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Calendario Principal con Feedback de Carga Rápida -->
    <div class="card border-0 shadow-sm position-relative">
        <!-- Indicador visual sutil de carga rápida -->
        <div id="calendarLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-10 d-none align-items-center justify-content-center" style="z-index: 5; backdrop-filter: blur(1px); border-radius: inherit;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando reservaciones...</span>
            </div>
        </div>

        <div class="card-body p-4">
            <div id="calendarPublico" style="min-height: 700px;"></div>
        </div>
    </div>
</div>

<!-- Modal para Registro / Edición -->
<div class="modal fade" id="modalReservacion" tabindex="-1" aria-labelledby="modalReservacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="modalReservacionLabel">Detalle de Reservación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReservacion" method="POST" action="<?= BASE_URL ?>/?page=Reservacion">
                <div class="modal-body p-4">
                    <input type="hidden" name="peticion" id="peticion" value="registrar">
                    <input type="hidden" name="id_reservacion" id="id_reservacion">

                    <!-- Cliente (Carga ultra rápida sin I/O en bucle) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase d-block">Seleccionar Cliente</label>
                        <select class="form-select select2-cliente" name="cedula_cliente" id="cedula_cliente" required>
                            <option value="">Buscar por nombre o cédula...</option>
                            <?php foreach($clientes as $c): 
                                $avatarUrl = BASE_URL . "assets/img/default.jpg";
                            ?>
                                <option value="<?= htmlspecialchars($c['cedula']) ?>" data-avatar="<?= $avatarUrl ?>">
                                    <?= htmlspecialchars("{$c['nombre']} {$c['apellido']} - {$c['cedula']}") ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="scedula_cliente"></div>
                    </div>

                    <!-- Fecha, Horarios y Cantidad de Personas -->
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Fecha</label>
                            <input type="text" class="form-control bg-light" name="fecha" id="fecha" required>
                            <div id="sfecha"></div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Inicio</label>
                            <input type="text" class="form-control bg-light" name="hora" id="hora" placeholder="Inicio" required>
                            <div id="shora"></div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Fin</label>
                            <input type="text" class="form-control bg-light" name="hora_fin" id="hora_fin" placeholder="Fin" required>
                            <div id="shora_fin"></div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label class="form-label small fw-bold text-uppercase">N° Personas</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-people-fill text-warning"></i></span>
                                <input type="number" class="form-control bg-light border-start-0" name="cantidad_personas" id="cantidad_personas" min="1" max="50" value="2" required>
                            </div>
                            <div id="scantidad_personas"></div>
                        </div>
                    </div>

                    <!-- Mesa y Estado con verificación dinámica de capacidad y área -->
                    <div class="row mb-0">
                        <div class="col-md-7 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-uppercase">Mesa Asignada</label>
                            <select class="form-select bg-light" name="id_mesa" id="id_mesa">
                                <option value="" data-capacidad="999" data-area="Sin asignar">Sin Asignar (Mesa abierta)</option>
                                <?php if (!empty($mesas)): ?>
                                    <?php foreach($mesas as $m): ?>
                                        <option value="<?= $m['id_mesa'] ?>" 
                                                data-capacidad="<?= $m['capacidad'] ?>" 
                                                data-area="<?= htmlspecialchars($m['area_nombre'] ?? 'General') ?>" 
                                                data-numero="<?= $m['numero_mesa'] ?>">
                                            Mesa #<?= $m['numero_mesa'] ?> &bull; <?= htmlspecialchars($m['area_nombre'] ?? 'General') ?> (Capacidad: <?= $m['capacidad'] ?> pers.)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <!-- Feedback de recomendación y capacidad en vivo -->
                            <div id="mesa_info_recomendacion" class="mt-2 small"></div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-uppercase">Estado</label>
                            <select class="form-select bg-light" name="estado" id="estado">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="CONFIRMADA">CONFIRMADA</option>
                                <option value="COMPLETADA">COMPLETADA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4">
                    <button type="button" class="btn btn-delete-custom me-auto" id="btnEliminar" style="display:none;">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button>
                    <button type="button" class="btn btn-cancel-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-save-custom fw-bold">Guardar Reservación</button>
                </div>
            </form>
        </div>
    </div>
</div>
