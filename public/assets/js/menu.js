/**
 * MÓDULO DE MENÚ - LÓGICA FRONTEND
 */

document.addEventListener('DOMContentLoaded', () => {
    // Referencias DOM
    const modalMenu = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMenu'));
    const formMenu = document.getElementById('formMenu');
    const loadingGallery = document.getElementById('loadingGallery');
    const galleryContainer = document.getElementById('galleryContainer');
    const emptyGallery = document.getElementById('emptyGallery');
    const filtrosCategorias = document.querySelectorAll('.btn-filtro');

    // Arrays de ingredientes seleccionados
    let listPrincipales = [];
    let listAdicionales = [];

    // Almacenará productos para filtro en cliente
    let productosActuales = [];

    init();

    function init() {
        renderCatalogoIngredientes();
        cargarMenu();

        // Listeners básicos
        document.getElementById('btnNuevoMenu').addEventListener('click', abrirModalNuevo);
        formMenu.addEventListener('submit', guardarMenu);
        document.getElementById('imagen').addEventListener('change', handlePreviewImagen);
        document.getElementById('btnAbrirGaleria').addEventListener('click', handleAbrirGaleria);

        // Listeners Filtros
        filtrosCategorias.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filtrosCategorias.forEach(b => b.classList.remove('active'));

                const target = e.target;
                target.classList.add('active');

                filtrarGaleria(target.dataset.categoria);
            });
        });

        // Search de ingredientes
        const searchInput = document.querySelector('.select-ingrediente-input');
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const collapse = new bootstrap.Collapse(document.getElementById('catalogoIngredientes'), { toggle: false });
            collapse.show();
            renderCatalogoIngredientes(query);
        });

        // Cambio de Tabs
        document.getElementById('principales-tab').addEventListener('shown.bs.tab', () => {
            document.getElementById('catalogoIngredientes').classList.remove('show');
        });
    }

    // ==========================================
    // RENDERIZADO GALERIA
    // ==========================================

    async function cargarMenu() {
        try {
            loadingGallery.style.display = 'block';
            galleryContainer.style.display = 'none';
            emptyGallery.style.display = 'none';

            const res = await fetch(`${BASE_URL}/?page=menu&action=listarJson`);
            const json = await res.json();

            loadingGallery.style.display = 'none';

            if (json.data && json.data.length > 0) {
                productosActuales = json.data;
                const catElement = document.querySelector('.btn-filtro.active');
                filtrarGaleria(catElement ? catElement.dataset.categoria : 'todas');
            } else {
                emptyGallery.style.display = 'block';
            }
        } catch (error) {
            console.error('Error cargando menú', error);
            Swal.fire('Error', 'No se pudo cargar el menú del restaurante.', 'error');
        }
    }

    function filtrarGaleria(idCategoria) {
        galleryContainer.innerHTML = '';
        let filtrados = [];

        if (idCategoria === 'todas') {
            filtrados = productosActuales.filter(p => p.estatus == 1);
        } else {
            filtrados = productosActuales.filter(p => p.estatus == 1 && p.id_categoria == idCategoria);
        }

        if (filtrados.length === 0) {
            galleryContainer.style.display = 'none';
            emptyGallery.style.display = 'block';
        } else {
            emptyGallery.style.display = 'none';
            galleryContainer.style.display = 'flex';
            filtrados.forEach(p => renderCard(p));
        }
    }

    function renderCard(p) {
        const imgUrl = (p.imagen && p.imagen !== 'default-product.png') ? `${BASE_URL}/assets/img/productos/${p.imagen}` : `${BASE_URL}/assets/img/placeholder.png`;

        const card = document.createElement('div');
        card.className = 'col';
        card.innerHTML = `
            <div class="card h-100 shadow-sm border-0 position-relative hover-shadow transition-all">
                <div class="ratio ratio-4x3 overflow-hidden bg-light rounded-top">
                    <img src="${imgUrl}" class="card-img-top object-fit-cover" alt="${p.nombre_producto}" onerror="this.onerror=null; this.src='${BASE_URL}/assets/img/placeholder.png'">
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title fw-bold mb-0 text-truncate" title="${p.nombre_producto}">${p.nombre_producto}</h5>
                    </div>
                    <p class="text-primary fw-bold fs-5 mb-2">$${parseFloat(p.precio).toFixed(2)}</p>
                    <p class="card-text text-muted small text-truncate-2" style="height: 40px;">${p.descripcion || 'Sin descripción'}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="badge border border-secondary bg-transparent text-body"><i class="fas fa-tag me-1 text-primary"></i>${p.categoria_nombre}</span>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary btn-editar" data-id="${p.id_producto}" title="Editar Menú">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${p.id_producto}" title="Eliminar del Menú">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        galleryContainer.appendChild(card);

        // Listeners para botones creados
        card.querySelector('.btn-editar').addEventListener('click', () => editarMenu(p.id_producto));
        card.querySelector('.btn-eliminar').addEventListener('click', (e) => eliminarMenu(p.id_producto));
    }

    // ==========================================
    // GESTIÓN DE MODAL Y RECETA
    // ==========================================

    function handlePreviewImagen(e) {
        const file = e.target.files[0];
        if (!file) {
            if (!document.getElementById('imagen_galeria').value) {
                document.getElementById('previewImagenContainer').style.display = 'none';
            }
            return;
        }
        document.getElementById('imagen_galeria').value = '';
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('previewImagen').src = ev.target.result;
            document.getElementById('previewImagenContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function handleAbrirGaleria() {
        if (typeof MediaPicker !== 'undefined') {
            MediaPicker.open({
                onSelect: function (ruta) {
                    document.getElementById('imagen_galeria').value = ruta;
                    document.getElementById('imagen').value = '';
                    document.getElementById('previewImagen').src = `${BASE_URL}${ruta}`;
                    document.getElementById('previewImagenContainer').style.display = 'block';
                }
            });
        } else {
            console.warn('Omitiendo MediaPicker, asegúrate de haberlo incluido.');
        }
    }

    function abrirModalNuevo() {
        formMenu.reset();
        document.getElementById('id_producto').value = '';
        document.getElementById('imagen_galeria').value = '';
        document.getElementById('previewImagenContainer').style.display = 'none';
        document.getElementById('modalTitleText').textContent = 'Nuevo Producto al Menú';

        listPrincipales = [];
        listAdicionales = [];
        renderReceta();

        modalMenu.show();
    }

    function renderCatalogoIngredientes(query = '') {
        const container = document.getElementById('listaIngredientesUI');
        container.innerHTML = '';

        const q = query.toLowerCase();
        const results = ingredientesDB.filter(i => i.nombre_ingrediente.toLowerCase().includes(q));

        if (results.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-muted">No se encontraron ingredientes.</div>';
            return;
        }

        results.forEach(ing => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item d-flex justify-content-between align-items-center flex-wrap';
            item.innerHTML = `
                <div>
                    <strong>${ing.nombre_ingrediente}</strong> 
                    <small class="text-muted ms-1">(${ing.nombre_unidad})</small>
                </div>
                <div class="btn-group btn-group-sm mt-1 mt-sm-0 shadow-sm">
                    <button class="btn btn-warning text-dark fw-bold btn-add-principal border-0" type="button">Principal</button>
                    <button class="btn btn-info text-dark fw-bold btn-add-adicional border-0" type="button">Extra</button>
                </div>
            `;

            // Add listeners
            item.querySelector('.btn-add-principal').addEventListener('click', (e) => {
                e.preventDefault();
                addIngredienteTo(ing, 'principal');
            });
            item.querySelector('.btn-add-adicional').addEventListener('click', (e) => {
                e.preventDefault();
                addIngredienteTo(ing, 'adicional');
            });

            container.appendChild(item);
        });
    }

    function addIngredienteTo(ing, listType) {
        let isPrincipal = listType === 'principal';
        let targetList = isPrincipal ? listPrincipales : listAdicionales;

        // Evitar duplicados
        if (targetList.find(i => i.id === ing.id_ingrediente)) {
            return;
        }

        targetList.push({
            id: ing.id_ingrediente,
            nombre: ing.nombre_ingrediente,
            cantidad: 1,
            unidad: ing.id_unidad_medida,
            default_unidad_name: ing.nombre_unidad,
            precio: 0
        });

        // Activar el tab correspondiente
        const tabEl = document.getElementById(isPrincipal ? 'principales-tab' : 'adicionales-tab');
        new bootstrap.Tab(tabEl).show();

        renderReceta();
    }

    function removeIngrediente(id, isPrincipal) {
        if (isPrincipal) {
            listPrincipales = listPrincipales.filter(i => i.id !== id);
        } else {
            listAdicionales = listAdicionales.filter(i => i.id !== id);
        }
        renderReceta();
    }

    function renderReceta() {
        renderTablaReceta('tablaPrincipales', listPrincipales, true);
        renderTablaReceta('tablaAdicionales', listAdicionales, false);

        document.getElementById('contPrincipales').innerText = listPrincipales.length;
        document.getElementById('contAdicionales').innerText = listAdicionales.length;
    }

    function renderTablaReceta(tableId, list, isPrincipal) {
        const tbody = document.querySelector(`#${tableId} tbody`);
        tbody.innerHTML = '';

        if (list.length === 0) {
            tbody.innerHTML = `<tr class="empty-row text-center text-muted"><td colspan="4" class="py-4">No hay ingredientes añadidos</td></tr>`;
            return;
        }

        // Crear options para unidades basado en unidadesDB
        const unidadesHtml = (selectedId) => unidadesDB.map(u =>
            `<option value="${u.id_unidad}" ${u.id_unidad == selectedId ? 'selected' : ''}>${u.abreviatura}</option>`
        ).join('');

        list.forEach((ing, index) => {
            const tr = document.createElement('tr');

            let precioHtml = '';
            if (!isPrincipal) {
                precioHtml = `
                <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm price-input" 
                        data-id="${ing.id}" data-type="adicional" value="${ing.precio || 0}" required>
                </td>`;
            }

            tr.innerHTML = `
                <td><span class="fw-semibold">${ing.nombre}</span></td>
                <td>
                    <input type="number" step="0.01" min="0.01" class="form-control form-control-sm qty-input" 
                        data-id="${ing.id}" data-type="${isPrincipal ? 'principal' : 'adicional'}" value="${ing.cantidad}" required>
                </td>
                <td>
                    <select class="form-select form-select-sm unit-select" data-id="${ing.id}" data-type="${isPrincipal ? 'principal' : 'adicional'}">
                        ${unidadesHtml(ing.unidad)}
                    </select>
                </td>
                ${precioHtml}
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-remove-ing">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;

            // Listeners updates
            tr.querySelector('.btn-remove-ing').addEventListener('click', () => removeIngrediente(ing.id, isPrincipal));
            tr.querySelector('.qty-input').addEventListener('change', (e) => updateIngrediente(ing.id, isPrincipal, 'cantidad', e.target.value));
            tr.querySelector('.unit-select').addEventListener('change', (e) => updateIngrediente(ing.id, isPrincipal, 'unidad', e.target.value));
            if (!isPrincipal) {
                tr.querySelector('.price-input').addEventListener('change', (e) => updateIngrediente(ing.id, isPrincipal, 'precio', e.target.value));
            }

            tbody.appendChild(tr);
        });
    }

    function updateIngrediente(id, isPrincipal, field, value) {
        let list = isPrincipal ? listPrincipales : listAdicionales;
        let item = list.find(i => i.id === id);
        if (item) item[field] = value;
    }

    // ==========================================
    // CRUDS BASE DE DATOS
    // ==========================================

    async function guardarMenu(e) {
        e.preventDefault();

        const btnSave = document.getElementById('btnGuardarMenu');
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        try {
            const formData = new FormData(formMenu);

            // Adjuntar recetas como string JSON
            formData.append('ingredientes_principales', JSON.stringify(listPrincipales));
            formData.append('ingredientes_adicionales', JSON.stringify(listAdicionales));

            const req = await fetch(`${BASE_URL}/?page=menu&action=guardar`, {
                method: 'POST',
                body: formData
            });
            const res = await req.json();

            if (res.success) {
                modalMenu.hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                cargarMenu(); // Recargar Galería
            } else {
                Swal.fire('Error', res.message || 'Error al guardar.', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Ha ocurrido un problema de conexión', 'error');
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Menú';
        }
    }

    async function editarMenu(id) {
        try {
            const req = await fetch(`${BASE_URL}/?page=menu&action=buscar&id=${id}`);
            const res = await req.json();

            if (res.success) {
                const p = res.data;
                document.getElementById('modalTitleText').textContent = 'Editar Producto del Menú';
                document.getElementById('id_producto').value = p.id_producto;
                document.getElementById('nombre').value = p.nombre_producto;
                document.getElementById('precio').value = p.precio;
                document.getElementById('tipo_producto').value = p.tipo_producto;
                document.getElementById('id_categoria').value = p.id_categoria;
                document.getElementById('descripcion').value = p.descripcion;

                if (p.imagen && p.imagen !== 'default-product.png') {
                    document.getElementById('previewImagenContainer').style.display = 'block';
                    document.getElementById('previewImagen').src = `${BASE_URL}/assets/img/productos/${p.imagen}`;
                } else {
                    document.getElementById('previewImagenContainer').style.display = 'none';
                }

                // Cargar listas
                listPrincipales = (p.ingredientes_principales || []).map(i => ({
                    id: i.id_ingrediente,
                    nombre: i.nombre_ingrediente,
                    cantidad: parseFloat(i.cantidad),
                    unidad: i.id_unidad_medida,
                    default_unidad_name: i.nombre_unidad
                }));

                listAdicionales = (p.ingredientes_adicionales || []).map(i => ({
                    id: i.id_ingrediente,
                    nombre: i.nombre_ingrediente,
                    cantidad: parseFloat(i.cantidad),
                    unidad: i.id_unidad_medida,
                    default_unidad_name: i.nombre_unidad,
                    precio: parseFloat(i.precio_ingrediente || 0)
                }));

                renderReceta();
                modalMenu.show();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'No se pudo cargar la información.', 'error');
        }
    }

    function eliminarMenu(id) {
        Swal.fire({
            title: '¿Estás seguro de eliminar este producto?',
            text: 'El producto será eliminado del menú (borrado lógico)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('id', id);

                    const req = await fetch(`${BASE_URL}/?page=menu&action=eliminar`, {
                        method: 'POST',
                        body: formData
                    });
                    const res = await req.json();

                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'Producto eliminado correctamente',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        cargarMenu();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Error de conexión', 'error');
                }
            }
        });
    }

    // CSS para mejorar la galería por inyección
    const style = document.createElement('style');
    style.innerHTML = `
        .hover-shadow:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .transition-all { transition: all .3s ease; }
        .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal; }
    `;
    document.head.appendChild(style);
});
