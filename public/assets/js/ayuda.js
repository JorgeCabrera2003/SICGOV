import { debounce } from './Helpers/MiscHelper.js';

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ayudaSearchInput');
    const dropdownMenu = document.getElementById('ayudaDropdownMenu');
    const resultsList = document.getElementById('ayudaResultsList');
    
    // Si no existe el input, salimos
    if (!searchInput) return;

    let debounceTimer;

    // Elementos del Offcanvas de Bootstrap
    const ayudaOffcanvasEl = document.getElementById('ayudaOffcanvas');
    let ayudaOffcanvas = null;
    if (ayudaOffcanvasEl) {
        ayudaOffcanvas = new bootstrap.Offcanvas(ayudaOffcanvasEl);
    }

    // Escuchar cambios en el input
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length > 0) {
            dropdownMenu.style.display = 'block';
            resultsList.innerHTML = '<div class="px-3 py-2 text-muted small"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Buscando...</div>';
            
            debounceTimer = setTimeout(() => {
                fetchAyudaResults(query);
            }, 300); // 300ms debounce
        } else {
            dropdownMenu.style.display = 'none';
        }
    });

    // Cerrar dropdown si se hace clic fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    });

    // Abrir dropdown si se hace clic en input y hay texto
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            dropdownMenu.style.display = 'block';
        } else {
            // Podríamos cargar sugerencias iniciales
            dropdownMenu.style.display = 'block';
            fetchAyudaResults('');
        }
    });

    function fetchAyudaResults(query) {
        fetch(`${BASE_URL}/?page=Ayuda&action=search&q=${encodeURIComponent(query)}`)
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

        // Añadir eventos click a los resultados
        document.querySelectorAll('.ayuda-item').forEach(el => {
            el.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                dropdownMenu.style.display = 'none';
                searchInput.value = ''; // Limpiar
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
                    offcanvasBody.innerHTML = `
                        <h4 class="mb-4" style="color: var(--brand-dark-orange);">${res.data.title}</h4>
                        <div class="help-content lh-lg" style="font-size: 0.95rem;">
                            ${res.data.content}
                        </div>
                    `;
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
