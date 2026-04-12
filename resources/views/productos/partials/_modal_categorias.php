<!-- ==========================================
     MODAL DE CATEGORÍAS - Reutilizable
     ========================================== -->

<div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalCategoriasLabel">
                    <i class="fas fa-tags text-warning me-2"></i>
                    Categorías de Productos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">
                <!-- Formulario para nueva categoría -->
                <div class="input-group mb-4">
                    <input type="text" class="form-control" id="nuevaCategoria" 
                           placeholder="Nueva categoría" maxlength="45">
                    <button class="btn btn-warning text-dark fw-semibold" type="button" id="btnGuardarCategoria">
                        <i class="fas fa-plus me-2"></i>Agregar
                    </button>
                </div>

                <!-- Tabla de categorías -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle" id="tablaCategorias">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Categoría</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categorias)): ?>
                                <?php foreach ($categorias as $cat): ?>
                                    <tr data-id="<?= $cat['id_categoria'] ?>">
                                        <td><?= htmlspecialchars($cat['nombre_categoria']) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger border-0 btn-eliminar-categoria" 
                                                    data-id="<?= $cat['id_categoria'] ?>"
                                                    title="Eliminar categoría">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">
                                        No hay categorías disponibles
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>