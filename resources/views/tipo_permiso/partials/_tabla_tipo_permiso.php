<!-- ==========================================
    TABLA DE CATEGORÍA DE INGREDIENTE - REUTILIZABLE
    ========================================== -->

<div class="table-responsive">
    <table class="table table-hover align-middle" id="tablaTipoPermiso"
        data-puede-modificar="<?= (($permisosTipoPermiso['tipo_permiso']['modificar'] ?? 0) == 1) ? '1' : '0' ?>"
        data-puede-eliminar="<?= (($permisosTipoPermiso['tipo_permiso']['eliminar'] ?? 0) == 1) ? '1' : '0' ?>"
        style="width:100%">
        <thead class="table-light">
            <tr>
                <th scope="col">Nombre</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <!-- DataTables carga los datos aquí -->
        </tbody>
    </table>
</div>