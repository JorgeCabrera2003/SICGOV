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


    <!-- Calendario Principal -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div id="calendarPublico" style="min-height: 700px;"></div>
        </div>
    </div>
</div>


<!-- Modal para Registro/Edición -->
<div class="modal fade" id="modalReservacion" tabindex="-1" aria-labelledby="modalReservacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="modalReservacionLabel">Detalle de Reservación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReservacion" method="POST" action="<?= BASE_URL ?>/?page=Reservacion">
                <div class="modal-body p-4">
                    <input type="hidden" name="peticion" id="peticion" value="registrar">
                    <input type="hidden" name="id_reservacion" id="id_reservacion">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase d-block">Seleccionar Cliente</label>
                        <select class="form-select select2-cliente" name="cedula_cliente" id="cedula_cliente" required>
                            <option value="">Buscar por nombre o cédula...</option>
                            <?php foreach($clientes as $c): 
                                $localImagePath = "assets/img/perfil/" . $c['cedula'] . ".png";
                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url(BASE_URL, PHP_URL_PATH) . $localImagePath) || file_exists(__DIR__ . '/../../../public/' . $localImagePath)) {
                                    $avatarUrl = BASE_URL . $localImagePath;
                                } else {
                                    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($c['nombre'] . ' ' . $c['apellido']) . "&background=random&color=fff&rounded=true&bold=true";
                                }
                            ?>
                                <option value="<?= $c['cedula'] ?>" data-avatar="<?= $avatarUrl ?>">
                                    <?= "{$c['nombre']} {$c['apellido']} - {$c['cedula']}" ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="scedula_cliente"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Fecha</label>
                            <input type="text" class="form-control bg-light" name="fecha" id="fecha" required>
                            <div id="sfecha"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Inicio</label>
                            <input type="text" class="form-control bg-light" name="hora" id="hora" placeholder="Inicio" required>
                            <div id="shora"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Fin</label>
                            <input type="text" class="form-control bg-light" name="hora_fin" id="hora_fin" placeholder="Fin" required>
                            <div id="shora_fin"></div>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-uppercase">Mesa (Opcional)</label>
                            <select class="form-select bg-light" name="id_mesa" id="id_mesa">
                                <option value="">Sin Asignar</option>
                                <?php if (!empty($mesas)): ?>
                                    <?php foreach($mesas as $m): ?>
                                        <option value="<?= $m['id_mesa'] ?>">
                                            Mesa <?= $m['numero_mesa'] ?> - <?= $m['area_nombre'] ?? 'General' ?> (Capacidad: <?= $m['capacidad'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
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
                    <button type="submit" class="btn btn-save-custom fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
