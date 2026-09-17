<div class="table-responsive">
    <table class="table table-hover align-middle" id="tablaPermisoLaboral"
        data-puede-modificar="<?= (($permisosPermisoLaboral['permiso_laboral']['modificar'] ?? 0) == 1) ? '1' : '0' ?>"
        data-puede-aprobar-rechazar="<?= (($permisosPermisoLaboral['permiso_laboral']['aprobar_rechazar'] ?? 0) == 1) ? '1' : '0' ?>"
        data-puede-eliminar="<?= (($permisosPermisoLaboral['permiso_laboral']['eliminar'] ?? 0) == 1) ? '1' : '0' ?>"
        style="width:100%">
        <thead class="table-light">
            <tr>
                <th>Empleado</th>
                <th>Tipo</th>
                <th>Días</th>
                <th>Estado</th>
                <th>Rango de Fechas</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
