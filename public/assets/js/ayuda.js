import { debounce } from './Helpers/MiscHelper.js';
import { TourHelper } from './Helpers/TourHelper.js';

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ayudaSearchInput');
    const dropdownMenu = document.getElementById('ayudaDropdownMenu');
    const resultsList = document.getElementById('ayudaResultsList');
    
    if (!searchInput) return;

    let debounceTimer;

    const ayudaOffcanvasEl = document.getElementById('ayudaOffcanvas');
    let ayudaOffcanvas = null;
    if (ayudaOffcanvasEl) {
        ayudaOffcanvas = new bootstrap.Offcanvas(ayudaOffcanvasEl);
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length > 0) {
            dropdownMenu.style.display = 'block';
            resultsList.innerHTML = '<div class="px-3 py-2 text-muted small"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Buscando...</div>';
            
            debounceTimer = setTimeout(() => {
                fetchAyudaResults(query);
            }, 300);
        } else {
            dropdownMenu.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            dropdownMenu.style.display = 'block';
        } else {
            dropdownMenu.style.display = 'block';
            fetchAyudaResults('');
        }
    });

    function fetchAyudaResults(query) {
        const urlParams = new URLSearchParams(window.location.search);
        let currentPage = urlParams.get('page') || 'Dashboard';
        if (currentPage === 'Reservacion' && urlParams.get('type') === 'publico') {
            currentPage = 'ReservacionPublico';
        }
        fetch(`${BASE_URL}/?page=Ayuda&action=search&q=${encodeURIComponent(query)}&module=${encodeURIComponent(currentPage)}`)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    renderResults(res.data);
                } else {
                    resultsList.innerHTML = `<div class="px-3 py-2 text-danger small">Error al buscar.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                resultsList.innerHTML = `<div class="px-3 py-2 text-danger small">Error de conexión.</div>`;
            });
    }

    function renderResults(data) {
        if (data.length === 0) {
            resultsList.innerHTML = '<div class="px-3 py-2 text-muted small">No se encontraron resultados. Intenta otra palabra.</div>';
            return;
        }

        let html = '';
        data.forEach(item => {
            html += `
                <a href="javascript:void(0)" class="dropdown-item d-flex align-items-center py-2 ayuda-item" data-id="${item.id}">
                    <i class="bi bi-question-circle me-2 text-secondary"></i>
                    <span class="text-wrap">${item.title}</span>
                </a>
            `;
        });
        
        resultsList.innerHTML = html;

        document.querySelectorAll('.ayuda-item').forEach(el => {
            el.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                dropdownMenu.style.display = 'none';
                searchInput.value = '';
                openTopic(id);
            });
        });
    }

    function openTopic(id) {
        if (!ayudaOffcanvas) return;
        
        const offcanvasBody = document.getElementById('ayudaOffcanvasBody');
        offcanvasBody.innerHTML = `
            <div class="d-flex justify-content-center my-4">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="text-center">Cargando guía...</div>
        `;
        
        ayudaOffcanvas.show();

        fetch(`${BASE_URL}/?page=Ayuda&action=getTopic&id=${encodeURIComponent(id)}`)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    let extraHTML = '';
                    const tourIds = ['gestion_reservaciones', 'formulario_reservacion', 'drag_drop_reservacion', 'solicitar_cita_publico', 'formulario_publico', 'gestion_pedidos', 'tomar_pedido_pos', 'crear_menu', 'gestion_clientes', 'formulario_cliente', 'formulario_producto_menu', 'gestion_personal', 'formulario_empleado', 'seguridad_usuarios', 'formulario_usuario', 'crear_categoria', 'formulario_categoria'];
                    
                    if (tourIds.includes(id)) {
                        extraHTML = `
                            <div class="mt-4 text-center border-top pt-4">
                                <button id="btn-tour-ayuda" class="btn btn-primary btn-sm rounded-pill shadow-sm px-3">
                                    <i class="bi bi-play-circle me-1"></i> Iniciar Tutorial Interactivo
                                </button>
                            </div>
                        `;
                    }

                    offcanvasBody.innerHTML = `
                        <h4 class="mb-4" style="color: var(--brand-dark-orange);">${res.data.title}</h4>
                        <div class="help-content lh-lg" style="font-size: 0.95rem;">
                            ${res.data.content}
                        </div>
                        ${extraHTML}
                    `;

                    if (tourIds.includes(id)) {
                        document.getElementById('btn-tour-ayuda').addEventListener('click', async () => {
                            ayudaOffcanvas.hide();
                            
                            const currentPage = window.location.search.toLowerCase();
                            const isReservacion = currentPage.includes('page=reservacion');
                            const isPedido = currentPage.includes('page=pedido');
                            const isMenu = currentPage.includes('page=menu');
                            const isCliente = currentPage.includes('page=cliente');
                            const isEmpleado = currentPage.includes('page=empleado');
                            const isUsuario = currentPage.includes('page=usuario');
                            const isCategoria = currentPage.includes('page=categoria') && !currentPage.includes('insumo');
                            
                            if (isReservacion || isPedido || isMenu || isCliente || isEmpleado || isUsuario || isCategoria) {
                                const { TourHelper } = await import('./Helpers/TourHelper.js');
                                let steps = [];

                                if (id === 'gestion_reservaciones') {
                                    steps = [
                                        { element: '.fc-toolbar-title', popover: { title: 'Calendario', description: 'Aquí visualizarás el mes en curso. Usa los botones laterales para navegar entre meses.', side: "bottom", align: 'start' } },
                                        { element: '.fc-view-harness', popover: { title: 'Cuadrícula Interactiva', description: 'Para registrar una nueva reservación, haz clic directamente en cualquier día de este calendario.', side: "top", align: 'start' } },
                                        { element: 'a[href*="Reservacion"]', popover: { title: 'Acceso Rápido', description: 'Siempre puedes volver a esta Agenda Global desde el menú principal.', side: "right", align: 'start' } }
                                    ];
                                } else if (id === 'solicitar_cita_publico') {
                                    steps = [
                                        { element: '#calendarPublico', popover: { title: 'Calendario', description: 'Selecciona el día en el que deseas solicitar tu reservación.', side: "top", align: 'start' } },
                                        { element: '#btnNuevaReservacion', popover: { title: 'Nueva Reservación', description: 'También puedes hacer clic aquí para iniciar el proceso de reserva.', side: "bottom", align: 'end' } },
                                        { element: '.mb-3.d-flex.gap-3', popover: { title: 'Estados', description: 'Aquí puedes ver qué significa cada color de las reservas.', side: "bottom", align: 'start' } }
                                    ];
                                } else if (id === 'formulario_reservacion') {
                                    const modalEl = document.getElementById('modalReservacion');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formReservacion').reset();
                                        if (typeof $ !== 'undefined' && $('.select2-cliente').length) {
                                            $('.select2-cliente').val(null).trigger('change');
                                        }
                                        modal.show();
                                        
                                        // Esperar a que termine la animación del modal
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { 
                                                element: '#formReservacion .modal-body > div:nth-of-type(1)', 
                                                popover: { title: 'Seleccionar Cliente', description: 'Busca al cliente registrado. Si es nuevo, regístralo primero en el módulo Clientes.', side: "bottom", align: 'start' } 
                                            },
                                            { 
                                                element: '#formReservacion .modal-body > div:nth-of-type(2)', 
                                                popover: { title: 'Fecha y Horarios', description: 'La fecha se asigna sola desde el calendario. Fija las horas de inicio y fin para calcular la ocupación de la mesa.', side: "bottom", align: 'start' } 
                                            },
                                            { 
                                                element: '#formReservacion .modal-body > div:nth-of-type(3)', 
                                                popover: { title: 'Mesa y Estado', description: 'Asigna una mesa específica si lo deseas, y cambia el estado a Confirmado si aseguraron la reserva.', side: "top", align: 'start' } 
                                            },
                                            { 
                                                element: '.btn-save-custom', 
                                                popover: { title: 'Guardar', description: 'Guarda los cambios y verás el bloque de reservación en el calendario.', side: "top", align: 'end' } 
                                            }
                                        ];
                                    }
                                } else if (id === 'formulario_publico') {
                                    const modalEl = document.getElementById('modalPublico');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formReservarPublico').reset();
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { 
                                                element: '#formReservarPublico .reservation-form-group.mb-4', 
                                                popover: { title: 'Fecha de tu visita', description: 'Verifica la fecha que seleccionaste.', side: "bottom", align: 'start' } 
                                            },
                                            { 
                                                element: '#formReservarPublico .row.g-3', 
                                                popover: { title: 'Horarios', description: 'Fija las horas de inicio y fin para tu visita.', side: "bottom", align: 'start' } 
                                            },
                                            { 
                                                element: '#formReservarPublico .btn-confirmar-premium', 
                                                popover: { title: 'Confirmar', description: 'Envía tu solicitud y confirmaremos tu cita.', side: "top", align: 'end' } 
                                            }
                                        ];
                                    }
                                } else if (id === 'drag_drop_reservacion') {
                                    steps = [
                                        { element: '.fc-event', popover: { title: 'Reservación Existente', description: 'Ubica un bloque de reservación (si hay alguno visible).', side: "bottom", align: 'start' } },
                                        { element: '.fc-view-harness', popover: { title: 'Arrastrar y Soltar', description: 'Simplemente haz clic sostenido en una reserva y arrástrala a un nuevo día. ¡El cambio se guardará automáticamente!', side: "top", align: 'start' } }
                                    ];
                                } else if (id === 'gestion_pedidos' || id === 'gestion_pedidos_tabla') {
                                    steps = [
                                        { element: '#pedidosTable', popover: { title: 'Tabla de Pedidos', description: 'Aquí se listan todos los pedidos realizados, su estado, total y el cliente asociado.', side: "top", align: 'start' } },
                                        { element: 'button[data-bs-target="#modalPOS"]', popover: { title: 'Nuevo Pedido POS', description: 'Haz clic aquí para abrir el Punto de Venta y registrar un nuevo pedido para un cliente.', side: "bottom", align: 'end' } }
                                    ];
                                } else if (id === 'tomar_pedido_pos') {
                                    const modalEl = document.getElementById('modalPOS');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { element: '#posFiltros', popover: { title: 'Filtros de Menú', description: 'Usa estas categorías para encontrar rápidamente los platillos o bebidas.', side: "bottom", align: 'start' } },
                                            { element: '#posProductos', popover: { title: 'Catálogo de Productos', description: 'Haz clic en cualquier producto para agregarlo inmediatamente a tu orden actual.', side: "right", align: 'start' } },
                                            { element: '.pos-ticket', popover: { title: 'Orden Actual', description: 'Aquí verás el resumen de los productos seleccionados, el total calculado en $ y Bs.', side: "left", align: 'start' } },
                                            { element: '#posForm', popover: { title: 'Datos de Pago', description: 'Define si el pedido es para llevar o en mesa, selecciona el método de pago y procede a cobrar.', side: "top", align: 'start' } }
                                        ];
                                    }
                                } else if (id === 'crear_menu') {
                                    steps = [
                                        { element: '#filtrosCategorias', popover: { title: 'Filtros de Categoría', description: 'Usa estos botones para filtrar rápidamente los platillos de tu menú.', side: "bottom", align: 'start' } },
                                        { element: '#btnNuevoMenu', popover: { title: 'Nuevo Producto', description: 'Haz clic aquí para agregar un nuevo platillo o bebida a tu carta.', side: "bottom", align: 'end' } },
                                        { element: 'a[href*="nuestro-menu"]', popover: { title: 'Menú Público', description: 'Este botón te llevará a la vista pública de tu menú, donde tus clientes verán todos los productos activos.', side: "bottom", align: 'start' } },
                                        { element: '#galleryContainer', popover: { title: 'Galería de Menú', description: 'Aquí verás todos tus platillos. Puedes editar, desactivar o eliminar productos desde sus respectivas tarjetas.', side: "top", align: 'center' } }
                                    ];
                                } else if (id === 'gestion_clientes') {
                                    steps = [
                                        { element: '#btnNuevoCliente', popover: { title: 'Nuevo Cliente', description: 'Presiona este botón para registrar un nuevo cliente en el sistema.', side: "bottom", align: 'end' } },
                                        { element: '#tablaCliente', popover: { title: 'Registro de Clientes', description: 'Aquí aparecerá la lista de todos tus clientes registrados. Puedes consultar su información, editarla o eliminarlos usando los botones de acción.', side: "top", align: 'center' } }
                                    ];
                                } else if (id === 'formulario_cliente') {
                                    const modalEl = document.getElementById('modalCliente');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formCliente').reset();
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { element: '#formCliente .row.g-3.mb-3:nth-of-type(1)', popover: { title: 'Identificación y Nacimiento', description: 'Ingresa la cédula con su prefijo y la fecha de nacimiento del cliente.', side: "bottom", align: 'start' } },
                                            { element: '#formCliente .row.g-3.mb-3:nth-of-type(2)', popover: { title: 'Datos Personales', description: 'Escribe el nombre y apellido completos del cliente.', side: "bottom", align: 'start' } },
                                            { element: '#formCliente .row.g-3.mb-3:nth-of-type(3)', popover: { title: 'Contacto y Sexo', description: 'El teléfono es opcional, pero útil. Asegúrate de seleccionar el sexo.', side: "bottom", align: 'start' } },
                                            { element: '#formCliente .row.g-3.mb-3:nth-of-type(4)', popover: { title: 'Correo y Dirección', description: 'Información adicional como correo electrónico y dirección física.', side: "top", align: 'start' } },
                                            { element: '#btnClienteForm', popover: { title: 'Guardar Registro', description: 'Presiona este botón para guardar el nuevo cliente en el sistema.', side: "top", align: 'end' } }
                                        ];
                                    }
                                } else if (id === 'formulario_producto_menu') {
                                    const modalEl = document.getElementById('modalMenu');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formMenu').reset();
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { element: '#formMenu .col-lg-5', popover: { title: 'Datos Requeridos', description: 'Aquí debes establecer la información visual y comercial: nombre, categoría, precio y una foto del producto.', side: "right", align: 'start' } },
                                            { element: '#formMenu .col-lg-7', popover: { title: 'Receta e Insumos', description: 'De este lado podrás agregar todos los ingredientes necesarios que componen el plato.', side: "left", align: 'start' } },
                                            { element: '.select-insumo-input', popover: { title: 'Buscador de Insumos', description: 'Escribe el nombre del ingrediente que necesitas y agrégalo a la receta.', side: "bottom", align: 'start' } },
                                            { element: '#recetaTabs', popover: { title: 'Separación Lógica', description: 'Agrega los ingredientes que conforman el plato base en "Principales". Todo lo extra que el cliente pueda pedir va en "Adicionales".', side: "bottom", align: 'center' } },
                                            { element: '#btnGuardarMenu', popover: { title: 'Guardar Producto', description: 'Una vez todo esté configurado correctamente, presiona guardar.', side: "top", align: 'end' } }
                                        ];
                                    }
                                } else if (id === 'gestion_personal') {
                                    steps = [
                                        { element: '#btnNuevoEmpleado', popover: { title: 'Nuevo Empleado', description: 'Presiona este botón para registrar un nuevo miembro del personal en el sistema.', side: "bottom", align: 'end' } },
                                        { element: '#tablaEmpleado', popover: { title: 'Registro de Empleados', description: 'Aquí aparecerá la lista de todos tus empleados. Puedes consultar su información, editarla o eliminarlos usando los botones de acción.', side: "top", align: 'center' } }
                                    ];
                                } else if (id === 'formulario_empleado') {
                                    const modalEl = document.getElementById('modalEmpleado');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formEmpleado').reset();
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { element: '#formEmpleado .row.g-3.mb-3:nth-of-type(1)', popover: { title: 'Identificación y Nacimiento', description: 'Ingresa la cédula con su prefijo y la fecha de nacimiento del empleado.', side: "bottom", align: 'start' } },
                                            { element: '#formEmpleado .row.g-3.mb-3:nth-of-type(2)', popover: { title: 'Datos Personales', description: 'Escribe el nombre y apellido completos del empleado.', side: "bottom", align: 'start' } },
                                            { element: '#formEmpleado .row.g-3.mb-3:nth-of-type(3)', popover: { title: 'Cargo y Sexo', description: 'Asigna el rol que cumplirá este empleado y selecciona su sexo.', side: "bottom", align: 'start' } },
                                            { element: '#formEmpleado .row.g-3.mb-3:nth-of-type(4)', popover: { title: 'Contacto', description: 'Información de contacto telefónico y correo electrónico.', side: "top", align: 'start' } },
                                            { element: '#formEmpleado .row.g-3.mb-3:nth-of-type(5)', popover: { title: 'Dirección', description: 'Dirección de residencia del empleado.', side: "top", align: 'start' } },
                                            { element: '#btnEmpleadoForm', popover: { title: 'Guardar Registro', description: 'Presiona este botón para guardar el nuevo empleado en el sistema.', side: "top", align: 'end' } }
                                        ];
                                    }
                                } else if (id === 'seguridad_usuarios') {
                                    steps = [
                                        { element: '#btn-nuevo', popover: { title: 'Nuevo Usuario', description: 'Haz clic aquí para otorgar acceso al sistema a un empleado.', side: "bottom", align: 'end' } },
                                        { element: '#tabla-usuario', popover: { title: 'Lista de Usuarios', description: 'Aquí verás todos los usuarios activos y podrás modificar sus accesos o desactivarlos.', side: "top", align: 'center' } }
                                    ];
                                } else if (id === 'formulario_usuario') {
                                    const modalEl = document.getElementById('modalUsuario');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formUsuario').reset();
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { element: '#grupo-seleccion-empleado', popover: { title: 'Seleccionar Empleado', description: 'Elige de la lista al empleado al cual le crearás las credenciales.', side: "bottom", align: 'start' } },
                                            { element: '#username', popover: { title: 'Nombre de Usuario', description: 'Crea el nombre que usará para ingresar (ej. jperez).', side: "bottom", align: 'start' } },
                                            { element: '#rol', popover: { title: 'Rol del Sistema', description: 'Asigna qué puede hacer este usuario. Dependiendo del rol, tendrá acceso a más o menos módulos.', side: "bottom", align: 'start' } },
                                            { element: '#clave', popover: { title: 'Contraseñas', description: 'Asigna una contraseña segura. El siguiente campo es para confirmarla.', side: "top", align: 'start' } },
                                            { element: '#btnUsuarioForm', popover: { title: 'Guardar Usuario', description: 'Una vez completado, guarda el registro para activar el acceso.', side: "top", align: 'end' } }
                                        ];
                                    }
                                } else if (id === 'crear_categoria') {
                                    steps = [
                                        { element: '#btnNuevaCategoria', popover: { title: 'Nueva Categoría', description: 'Haz clic aquí para agregar una nueva clasificación al menú.', side: "bottom", align: 'end' } },
                                        { element: '#tablaCategoria', popover: { title: 'Lista de Categorías', description: 'Aquí verás todas las categorías registradas. Puedes editar sus nombres o eliminarlas si ya no son necesarias.', side: "top", align: 'center' } }
                                    ];
                                } else if (id === 'formulario_categoria') {
                                    const modalEl = document.getElementById('modalCategoria');
                                    if (modalEl) {
                                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                        document.getElementById('formCategoria').reset();
                                        modal.show();
                                        
                                        await new Promise(r => setTimeout(r, 400));
                                        
                                        steps = [
                                            { element: '#nombre_categoria', popover: { title: 'Nombre de la Categoría', description: 'Escribe el nombre con el que quieres agrupar tus productos (ej. Postres, Bebidas).', side: "bottom", align: 'start' } },
                                            { element: '#btnGuardarCategoria', popover: { title: 'Guardar', description: 'Guarda los cambios para que la categoría esté disponible al crear productos.', side: "top", align: 'end' } }
                                        ];
                                    }
                                }

                                if (steps.length > 0) {
                                    const tour = new TourHelper({ steps: steps });
                                    await tour.init();
                                    tour.start();
                                }

                            } else {
                                import('./Helpers/UIHelper.js').then(({ mensajes }) => {
                                    mensajes('info', 3000, 'Aviso', 'Debes estar en el módulo correspondiente para iniciar este tutorial.');
                                });
                            }
                        });
                    }
                } else {
                    offcanvasBody.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                offcanvasBody.innerHTML = `<div class="alert alert-danger">Ocurrió un error al cargar la ayuda.</div>`;
            });
    }
});
