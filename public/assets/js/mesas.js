// ============================================
// MÓDULO DE MESAS - GOOD VIBES
// ============================================

// Verificar si el script ya fue cargado
if (typeof window.mesasScriptLoaded === 'undefined') {
    window.mesasScriptLoaded = true;
    
    console.log("Inicializando script de Mesas");

    // Verificar que enviaAjax existe
    if (typeof enviaAjax === 'undefined') {
        console.error("ERROR CRÍTICO: La función enviaAjax no está definida");
    } else {
        console.log("enviaAjax encontrada correctamente");
        
        // Solo instalar el wrapper si no existe ya
        if (typeof window.originalEnviaAjax === 'undefined') {
            window.originalEnviaAjax = window.enviaAjax;
            window.enviaAjax = async function(formData, url) {
                console.log("=== enviaAjax wrapper ===");
                console.log("URL:", url);
                console.log("FormData contents:");
                if (formData.forEach) {
                    formData.forEach((value, key) => {
                        console.log(key + ':', value);
                    });
                }
                
                try {
                    const result = await window.originalEnviaAjax(formData, url);
                    console.log("enviaAjax response:", result);
                    return result;
                } catch (error) {
                    console.error("enviaAjax error:", error);
                    throw error;
                }
            };
            console.log("enviaAjax wrapper instalado para debugging");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOMContentLoaded - Iniciando script de Mesas");
        
        let tablaMesas;
        
        // Verificar que los modales existen antes de inicializar
        const modalMesaElement = document.getElementById('modalMesa');
        const modalCambiarEstadoElement = document.getElementById('modalCambiarEstado');
        const modalEliminarMesaElement = document.getElementById('modalEliminarMesa');
        
        if (!modalMesaElement) {
            console.error("ERROR: No se encontró el modal #modalMesa");
        }
        if (!modalCambiarEstadoElement) {
            console.error("ERROR: No se encontró el modal #modalCambiarEstado");
        }
        if (!modalEliminarMesaElement) {
            console.error("ERROR: No se encontró el modal #modalEliminarMesa");
        }
        
        // Inicializar modales solo si existen
        let modalMesa, modalCambiarEstado, modalEliminarMesa;
        
        try {
            if (modalMesaElement && typeof bootstrap !== 'undefined') {
                modalMesa = new bootstrap.Modal(modalMesaElement);
                console.log("Modal Mesa inicializado");
            } else {
                console.warn("No se pudo inicializar modalMesa");
                modalMesa = { show: () => {}, hide: () => {} };
            }
            
            if (modalCambiarEstadoElement && typeof bootstrap !== 'undefined') {
                modalCambiarEstado = new bootstrap.Modal(modalCambiarEstadoElement);
                console.log("Modal Cambiar Estado inicializado");
            } else {
                console.warn("No se pudo inicializar modalCambiarEstado");
                modalCambiarEstado = { show: () => {}, hide: () => {} };
            }
            
            if (modalEliminarMesaElement && typeof bootstrap !== 'undefined') {
                modalEliminarMesa = new bootstrap.Modal(modalEliminarMesaElement);
                console.log("Modal Eliminar Mesa inicializado");
            } else {
                console.warn("No se pudo inicializar modalEliminarMesa");
                modalEliminarMesa = { show: () => {}, hide: () => {} };
            }
        } catch (e) {
            console.error("Error inicializando modales:", e);
            // Crear objetos dummy para evitar errores
            modalMesa = { show: () => console.warn("modalMesa.show() llamado pero no inicializado"), hide: () => {} };
            modalCambiarEstado = { show: () => console.warn("modalCambiarEstado.show() llamado pero no inicializado"), hide: () => {} };
            modalEliminarMesa = { show: () => console.warn("modalEliminarMesa.show() llamado pero no inicializado"), hide: () => {} };
        }
        
        const $formMesa = $('#formMesa');
        
        if (!$formMesa.length) {
            console.error("ERROR: No se encontró el formulario #formMesa");
        } else {
            console.log("Formulario encontrado");
        }

        // Cargar mesas al iniciar
        console.log("Llamando a cargarMesas()");
        cargarMesas();

        async function cargarMesas() {
            console.log("=== cargarMesas() iniciado ===");
            const peticion = new FormData();
            peticion.append('peticion', 'consultar');
            
            console.log("Enviando petición AJAX a:", BASE_URL + '?page=mesas');
            
            try {
                const json = await enviaAjax(peticion, BASE_URL + '?page=mesas');
                console.log("Respuesta recibida en cargarMesas:", json);
                
                let arreglo = [];
                if (json && json.resultado === 200) {
                    console.log("Respuesta exitosa, cantidad de registros:", json.datos ? json.datos.length : 0);
                    arreglo = json.datos || [];
                    if (arreglo.length > 0) {
                        console.log("Primer registro:", arreglo[0]);
                    } else {
                        console.warn("No hay datos de mesas disponibles");
                    }
                } else {
                    console.error("Error en respuesta:", json?.mensaje || "Respuesta no válida");
                }
                renderTablaMesas(arreglo);
            } catch (e) {
                console.error("Error en catch de cargarMesas:", e);
                console.error("Stack trace:", e.stack);
                renderTablaMesas([]);
            }
            console.log("=== cargarMesas() finalizado ===");
        }

        function renderTablaMesas(datos) {
            console.log("=== renderTablaMesas() iniciado ===");
            console.log("Cantidad de datos:", datos ? datos.length : 0);
            
            const $tabla = $('#tablaMesas');
            
            if (!$tabla.length) {
                console.error("ERROR: No se encontró la tabla #tablaMesas");
                return;
            }
            
            // Verificar si DataTable está disponible
            if (typeof $.fn.DataTable === 'undefined') {
                console.error("ERROR CRÍTICO: DataTable no está cargado");
                return;
            }
            
            // Destruir DataTable existente
            if ($.fn.DataTable.isDataTable('#tablaMesas')) {
                console.log("DataTable ya existente, destruyendo...");
                $('#tablaMesas').DataTable().destroy();
            }

            console.log("Inicializando nueva DataTable...");
            
            try {
                tablaMesas = $('#tablaMesas').DataTable({
                    responsive: true,
                    data: datos,
                    order: [[1, 'asc']],
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                    columns: [
                        { data: 'id_mesa', defaultContent: 'N/A' },
                        { data: 'numero_mesa', defaultContent: 'N/A' },
                        { data: 'area_nombre', defaultContent: 'Sin área' },
                        { data: 'capacidad', defaultContent: '0' },
                        {
                            data: 'estado',
                            defaultContent: 'DESCONOCIDO',
                            render: function (data) {
                                const badges = {
                                    'DISPONIBLE': '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Disponible</span>',
                                    'LIBRE': '<span class="badge bg-info"><i class="fas fa-check me-1"></i> Libre</span>',
                                    'OCUPADA': '<span class="badge bg-danger"><i class="fas fa-user me-1"></i> Ocupada</span>',
                                    'MANTENIMIENTO': '<span class="badge bg-warning text-dark"><i class="fas fa-tools me-1"></i> Mantenimiento</span>'
                                };
                                return badges[data] || `<span class="badge bg-secondary">${data || 'DESCONOCIDO'}</span>`;
                            }
                        },
                        {
                            data: 'estatus',
                            defaultContent: '0',
                            render: function (data) {
                                if (data == 1) {
                                    return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Activo</span>';
                                }
                                return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Inactivo</span>';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            className: 'text-center',
                            render: function (data, type, row) {
                                return `
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-warning btn-cambiar-estado" 
                                                data-id="${data.id_mesa || ''}" 
                                                data-numero="${data.numero_mesa || ''}"
                                                title="Cambiar Estado">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-editar" 
                                                data-id="${data.id_mesa || ''}"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-eliminar" 
                                                data-id="${data.id_mesa || ''}"
                                                data-numero="${data.numero_mesa || ''}"
                                                title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    drawCallback: function() {
                        console.log("DataTable terminó de dibujarse");
                    },
                    initComplete: function() {
                        console.log("DataTable inicializada correctamente");
                        console.log("Filas totales:", this.api().rows().count());
                    }
                });
                console.log("DataTable creada exitosamente");
            } catch (e) {
                console.error("Error al crear DataTable:", e);
            }
            console.log("=== renderTablaMesas() finalizado ===");
        }

        // Función para cargar áreas
        async function cargarAreas() {
            console.log("=== cargarAreas() iniciado ===");
            try {
                const peticion = new FormData();
                peticion.append('peticion', 'consultar_areas');
                console.log("Solicitando áreas a:", BASE_URL + '?page=areas');
                
                const json = await enviaAjax(peticion, BASE_URL + '?page=areas');
                console.log("Respuesta de áreas:", json);
                
                if (json && json.resultado === 200 && json.datos) {
                    console.log("Áreas cargadas:", json.datos.length);
                    const $select = $('#id_area');
                    $select.empty().append('<option value="">Seleccione un área</option>');
                    json.datos.forEach(area => {
                        console.log("Agregando área:", area.id_area, area.nombre);
                        $select.append(`<option value="${area.id_area}">${area.nombre}</option>`);
                    });
                } else {
                    console.warn("No se pudieron cargar áreas o no hay datos");
                }
            } catch (e) {
                console.error("Error en cargarAreas:", e);
            }
            console.log("=== cargarAreas() finalizado ===");
        }

        // Botón Nueva Mesa
        $('#btnNuevaMesa').on('click', function () {
            console.log("Click en btnNuevaMesa");
            if ($formMesa.length) {
                $formMesa[0].reset();
            }
            $('#peticion').val('registrar');
            $('#id_mesa').val('');
            $('#estatus').prop('checked', true);
            $('#modalTitle').text('Registrar Mesa');
            cargarAreas();
            modalMesa.show();
        });

        // Guardar Mesa
        $('#btnGuardarMesa').on('click', async function () {
            console.log("Click en btnGuardarMesa");
            
            if (!$formMesa.length || !$formMesa[0].checkValidity()) {
                console.warn("Formulario inválido o no existe");
                if ($formMesa.length) $formMesa[0].reportValidity();
                return;
            }

            const $btnSubmit = $(this);
            const originalContent = $btnSubmit.html();
            $btnSubmit.prop('disabled', true);
            $btnSubmit.html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

            const fd = new FormData($formMesa[0]);
            fd.append('estatus', $('#estatus').is(':checked') ? 1 : 0);
            
            try {
                console.log("Enviando petición a:", BASE_URL + '?page=mesas');
                const res = await enviaAjax(fd, BASE_URL + '?page=mesas');
                console.log("Respuesta recibida:", res);
                
                if (res && res.resultado === 200) {
                    Swal.fire('Éxito', res.mensaje, 'success');
                    modalMesa.hide();
                    cargarMesas();
                } else {
                    Swal.fire('Error', res?.mensaje || 'Error en respuesta', 'error');
                }
            } catch (error) {
                console.error("Error en catch de guardar:", error);
                Swal.fire('Error', 'Ocurrió un error al guardar la mesa', 'error');
            } finally {
                $btnSubmit.prop('disabled', false).html(originalContent);
            }
        });

        // Editar Mesa
        $(document).on('click', '#tablaMesas tbody .btn-editar', async function (e) {
            e.preventDefault();
            const idMesa = $(this).data('id');
            console.log("Click en editar mesa ID:", idMesa);
            
            const fd = new FormData();
            fd.append('peticion', 'consultar_una');
            fd.append('id_mesa', idMesa);

            try {
                const res = await enviaAjax(fd, BASE_URL + '?page=mesas');
                console.log("Respuesta de consultar_una:", res);
                
                if (res && res.resultado === 200 && res.datos) {
                    const mesa = res.datos;
                    $('#peticion').val('modificar');
                    $('#id_mesa').val(mesa.id_mesa);
                    $('#id_area').val(mesa.id_area);
                    $('#numero_mesa').val(mesa.numero_mesa);
                    $('#capacidad').val(mesa.capacidad);
                    $('#estado').val(mesa.estado);
                    $('#estatus').prop('checked', mesa.estatus == 1);
                    $('#modalTitle').text('Editar Mesa');
                    await cargarAreas();
                    $('#id_area').val(mesa.id_area);
                    modalMesa.show();
                } else {
                    Swal.fire('Error', 'No se encontró la mesa', 'error');
                }
            } catch (error) {
                console.error("Error en editar:", error);
                Swal.fire('Error', 'Error al cargar los datos de la mesa', 'error');
            }
        });

        // Cambiar Estado - Abrir modal
        $(document).on('click', '#tablaMesas tbody .btn-cambiar-estado', function (e) {
            e.preventDefault();
            const idMesa = $(this).data('id');
            const numeroMesa = $(this).data('numero');
            
            $('#cambiarEstadoIdMesa').val(idMesa);
            $('#cambiarEstadoNumeroMesa').text(numeroMesa);
            modalCambiarEstado.show();
        });

        // Confirmar Cambio de Estado
        $('#btnConfirmarCambioEstado').on('click', async function () {
            const idMesa = $('#cambiarEstadoIdMesa').val();
            const nuevoEstado = $('#nuevoEstado').val();

            const $btn = $(this);
            const originalContent = $btn.html();
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>');

            const fd = new FormData();
            fd.append('peticion', 'cambiar_estado');
            fd.append('id_mesa', idMesa);
            fd.append('estado', nuevoEstado);

            try {
                const res = await enviaAjax(fd, BASE_URL + '?page=mesas');
                if (res && res.resultado === 200) {
                    Swal.fire('Éxito', res.mensaje, 'success');
                    modalCambiarEstado.hide();
                    cargarMesas();
                } else {
                    Swal.fire('Error', res?.mensaje || 'Error al cambiar el estado', 'error');
                }
            } catch (error) {
                console.error("Error:", error);
                Swal.fire('Error', 'Ocurrió un error al cambiar el estado', 'error');
            } finally {
                $btn.prop('disabled', false).html(originalContent);
            }
        });

        // Eliminar Mesa - Abrir modal
        $(document).on('click', '#tablaMesas tbody .btn-eliminar', function (e) {
            e.preventDefault();
            const idMesa = $(this).data('id');
            const numeroMesa = $(this).data('numero');
            
            $('#eliminarIdMesa').val(idMesa);
            $('#eliminarNumeroMesa').text(numeroMesa);
            modalEliminarMesa.show();
        });

        // Confirmar Eliminación
        $('#btnConfirmarEliminar').on('click', async function () {
            const idMesa = $('#eliminarIdMesa').val();

            const $btn = $(this);
            const originalContent = $btn.html();
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>');

            const fd = new FormData();
            fd.append('peticion', 'eliminar');
            fd.append('id_mesa', idMesa);

            try {
                const res = await enviaAjax(fd, BASE_URL + '?page=mesas');
                if (res && res.resultado === 200) {
                    Swal.fire('Eliminado', res.mensaje, 'success');
                    modalEliminarMesa.hide();
                    cargarMesas();
                } else {
                    Swal.fire('Error', res?.mensaje || 'Error al eliminar la mesa', 'error');
                }
            } catch (error) {
                console.error("Error:", error);
                Swal.fire('Error', 'Ocurrió un error al eliminar la mesa', 'error');
            } finally {
                $btn.prop('disabled', false).html(originalContent);
            }
        });

        console.log("Script de Mesas completamente cargado y eventos registrados");
    });
} else {
    console.warn("Script de Mesas ya estaba cargado, omitiendo ejecución");
}