// ============================================
// MÓDULO DE ÁREAS - GOOD VIBES
// ============================================

// Verificar si el script ya fue cargado
if (typeof window.areasScriptLoaded === 'undefined') {
    window.areasScriptLoaded = true;
    
    console.log("Inicializando script de Áreas");

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
        console.log("DOMContentLoaded - Iniciando script de Áreas");
        
        let tablaAreas;
        
        // Verificar que los modales existen antes de inicializar
        const modalAreaElement = document.getElementById('modalArea');
        const modalEliminarAreaElement = document.getElementById('modalEliminarArea');
        
        if (!modalAreaElement) {
            console.error("ERROR: No se encontró el modal #modalArea");
        }
        if (!modalEliminarAreaElement) {
            console.error("ERROR: No se encontró el modal #modalEliminarArea");
        }
        
        // Inicializar modales solo si existen
        let modalArea, modalEliminarArea;
        
        try {
            if (modalAreaElement && typeof bootstrap !== 'undefined') {
                modalArea = new bootstrap.Modal(modalAreaElement);
                console.log("Modal Área inicializado");
            } else {
                console.warn("No se pudo inicializar modalArea");
                modalArea = { show: () => {}, hide: () => {} };
            }
            
            if (modalEliminarAreaElement && typeof bootstrap !== 'undefined') {
                modalEliminarArea = new bootstrap.Modal(modalEliminarAreaElement);
                console.log("Modal Eliminar Área inicializado");
            } else {
                console.warn("No se pudo inicializar modalEliminarArea");
                modalEliminarArea = { show: () => {}, hide: () => {} };
            }
        } catch (e) {
            console.error("Error inicializando modales:", e);
            modalArea = { show: () => console.warn("modalArea.show() llamado pero no inicializado"), hide: () => {} };
            modalEliminarArea = { show: () => console.warn("modalEliminarArea.show() llamado pero no inicializado"), hide: () => {} };
        }
        
        const $formArea = $('#formArea');
        
        if (!$formArea.length) {
            console.error("ERROR: No se encontró el formulario #formArea");
        } else {
            console.log("Formulario encontrado");
        }

        // Variable para almacenar temporalmente los datos del área en edición
        let areaEnEdicion = null;

        // Cargar áreas al iniciar
        console.log("Llamando a cargarAreas()");
        cargarAreas();

        async function cargarAreas() {
            console.log("=== cargarAreas() iniciado ===");
            const peticion = new FormData();
            peticion.append('peticion', 'consultar');
            
            console.log("Enviando petición AJAX a:", BASE_URL + '?page=areas');
            
            try {
                const json = await enviaAjax(peticion, BASE_URL + '?page=areas');
                console.log("Respuesta recibida en cargarAreas:", json);
                
                let arreglo = [];
                if (json && json.resultado === 200) {
                    console.log("Respuesta exitosa, cantidad de registros:", json.datos ? json.datos.length : 0);
                    arreglo = json.datos || [];
                    if (arreglo.length > 0) {
                        console.log("Primer registro:", arreglo[0]);
                    } else {
                        console.warn("No hay datos de áreas disponibles");
                    }
                } else {
                    console.error("Error en respuesta:", json?.mensaje || "Respuesta no válida");
                }
                renderTablaAreas(arreglo);
            } catch (e) {
                console.error("Error en catch de cargarAreas:", e);
                console.error("Stack trace:", e.stack);
                renderTablaAreas([]);
            }
            console.log("=== cargarAreas() finalizado ===");
        }

        function renderTablaAreas(datos) {
            console.log("=== renderTablaAreas() iniciado ===");
            console.log("Cantidad de datos:", datos ? datos.length : 0);
            
            const $tabla = $('#tablaAreas');
            
            if (!$tabla.length) {
                console.error("ERROR: No se encontró la tabla #tablaAreas");
                return;
            }
            
            if (typeof $.fn.DataTable === 'undefined') {
                console.error("ERROR CRÍTICO: DataTable no está cargado");
                return;
            }
            
            if ($.fn.DataTable.isDataTable('#tablaAreas')) {
                console.log("DataTable ya existente, destruyendo...");
                $('#tablaAreas').DataTable().destroy();
            }

            console.log("Inicializando nueva DataTable...");
            
            try {
                tablaAreas = $('#tablaAreas').DataTable({
                    responsive: true,
                    data: datos,
                    order: [[0, 'asc']], // Ordenar por nombre
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                    columns: [
                        { data: 'nombre', defaultContent: 'N/A' },
                        { data: 'descripcion', defaultContent: 'Sin descripción' },
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
                                        <button class="btn btn-outline-primary btn-editar" 
                                                data-id="${data.id_area || ''}"
                                                data-nombre="${data.nombre || ''}"
                                                data-descripcion="${data.descripcion || ''}"
                                                data-estatus="${data.estatus || ''}"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-eliminar" 
                                                data-id="${data.id_area || ''}"
                                                data-nombre="${data.nombre || ''}"
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
            console.log("=== renderTablaAreas() finalizado ===");
        }

        // Botón Nueva Área
        $('#btnNuevaArea').on('click', function () {
            console.log("Click en btnNuevaArea");
            if ($formArea.length) {
                $formArea[0].reset();
            }
            $('#peticion').val('registrar');
            $('#id_area').val('');
            $('#estatus').prop('checked', true);
            $('#modalTitle').text('Registrar Área');
            modalArea.show();
        });

        // Guardar Área (Registrar o Modificar)
        $('#btnGuardarArea').on('click', async function () {
            console.log("Click en btnGuardarArea");
            
            if (!$formArea.length || !$formArea[0].checkValidity()) {
                console.warn("Formulario inválido o no existe");
                if ($formArea.length) $formArea[0].reportValidity();
                return;
            }

            const $btnSubmit = $(this);
            const originalContent = $btnSubmit.html();
            $btnSubmit.prop('disabled', true);
            $btnSubmit.html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

            const fd = new FormData($formArea[0]);
            fd.append('estatus', $('#estatus').is(':checked') ? 1 : 0);
            
            const peticionActual = $('#peticion').val();
            console.log("Petición actual:", peticionActual);
            
            try {
                console.log("Enviando petición a:", BASE_URL + '?page=areas');
                const res = await enviaAjax(fd, BASE_URL + '?page=areas');
                console.log("Respuesta recibida:", res);
                
                if (res && res.resultado === 200) {
                    Swal.fire('Éxito', res.mensaje, 'success');
                    modalArea.hide();
                    areaEnEdicion = null;
                    cargarAreas();
                } else {
                    Swal.fire('Error', res?.mensaje || 'Error en respuesta', 'error');
                }
            } catch (error) {
                console.error("Error en catch de guardar:", error);
                Swal.fire('Error', 'Ocurrió un error al guardar el área', 'error');
            } finally {
                $btnSubmit.prop('disabled', false).html(originalContent);
            }
        });

        // Editar Área - Usando los datos del botón (sin consultar_una)
        $(document).on('click', '#tablaAreas tbody .btn-editar', async function (e) {
            e.preventDefault();
            
            // Obtener los datos directamente del botón
            const idArea = $(this).data('id');
            const nombre = $(this).data('nombre');
            const descripcion = $(this).data('descripcion');
            const estatus = $(this).data('estatus');
            
            console.log("Editando área con datos del botón:", {
                idArea, nombre, descripcion, estatus
            });
            
            // Guardar referencia del área en edición
            areaEnEdicion = {
                id_area: idArea,
                nombre: nombre,
                descripcion: descripcion,
                estatus: estatus
            };
            
            // Llenar el formulario
            $('#peticion').val('modificar');
            $('#id_area').val(idArea);
            $('#nombre').val(nombre);
            $('#descripcion').val(descripcion || '');
            $('#estatus').prop('checked', estatus == 1);
            $('#modalTitle').text('Editar Área');
            
            // Mostrar el modal
            modalArea.show();
        });

        // Eliminar Área - Abrir modal
        $(document).on('click', '#tablaAreas tbody .btn-eliminar', function (e) {
            e.preventDefault();
            const idArea = $(this).data('id');
            const nombreArea = $(this).data('nombre');
            
            $('#eliminarIdArea').val(idArea);
            $('#eliminarNombreArea').text(nombreArea);
            modalEliminarArea.show();
        });

        // Confirmar Eliminación
        $('#btnConfirmarEliminar').on('click', async function () {
            const idArea = $('#eliminarIdArea').val();

            const $btn = $(this);
            const originalContent = $btn.html();
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>');

            const fd = new FormData();
            fd.append('peticion', 'eliminar');
            fd.append('id_area', idArea);

            try {
                const res = await enviaAjax(fd, BASE_URL + '?page=areas');
                if (res && res.resultado === 200) {
                    Swal.fire('Eliminado', res.mensaje, 'success');
                    modalEliminarArea.hide();
                    cargarAreas();
                } else {
                    Swal.fire('Error', res?.mensaje || 'Error al eliminar el área', 'error');
                }
            } catch (error) {
                console.error("Error:", error);
                Swal.fire('Error', 'Ocurrió un error al eliminar el área', 'error');
            } finally {
                $btn.prop('disabled', false).html(originalContent);
            }
        });

        console.log("Script de Áreas completamente cargado y eventos registrados");
    });
} else {
    console.warn("Script de Áreas ya estaba cargado, omitiendo ejecución");
}