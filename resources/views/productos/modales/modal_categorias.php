<!-- Modal Categorías -->
<div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriasLabel">
                    <i class="fas fa-tags me-2"></i>
                    Categorías de Productos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="nuevaCategoria" placeholder="Nueva categoría">
                        <button class="btn btn-primary" type="button" id="btnGuardarCategoria">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm" id="tablaCategorias">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th width="100">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                                <tr data-id="<?= $cat['id_categoria'] ?>">
                                    <td><?= $cat['nombre_categoria'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger btn-eliminar-categoria" data-id="<?= $cat['id_categoria'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>