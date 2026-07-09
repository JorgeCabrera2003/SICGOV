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
                    const tourIds = ['gestion_reservaciones', 'formulario_reservacion', 'drag_drop_reservacion', 'solicitar_cita_publico', 'formulario_publico'];
                    
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
                            
                            if (window.location.search.includes('page=Reservacion')) {
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
                                }

                                if (steps.length > 0) {
                                    const tour = new TourHelper({ steps: steps });
                                    await tour.init();
                                    tour.start();
                                }

                            } else {
                                import('./Helpers/UIHelper.js').then(({ mensajes }) => {
                                    mensajes('info', 3000, 'Aviso', 'Debes estar en la sección "Agenda Global" para iniciar este tutorial.');
                                    setTimeout(() => { window.location.href = '?page=Reservacion'; }, 2000);
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
