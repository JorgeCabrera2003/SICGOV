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

    // Almacenará IDs de categorías válidas al cargar para evitar inyección
    let validCategorias = [];

    // Observador del DOM
    let domObserver;

    init();

    function init() {
        // Capturar categorías originales válidas
        const catSelect = document.getElementById('id_categoria');
        if (catSelect) {
            Array.from(catSelect.options).forEach(opt => {
                if (opt.value !== '') validCategorias.push(opt.value);
            });
        }

        renderCatalogoIngredientes();
        cargarMenu();

        // Listeners básicos
        document.getElementById('btnNuevoMenu').addEventListener('click', abrirModalNuevo);
        formMenu.addEventListener('submit', guardarMenu);
        document.getElementById('imagen').addEventListener('change', (e) => {
            handlePreviewImagen(e);
            validateForm();
        });
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

        // Eventos de validación y sanitización en tiempo real
        document.getElementById('nombre').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            validateForm();
        });

        document.getElementById('descripcion').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/g, '');
            validateForm();
        });

        document.getElementById('precio').addEventListener('input', validateForm);
        document.getElementById('id_categoria').addEventListener('change', validateForm);

        document.getElementById('tipo_producto').addEventListener('change', function (e) {
            const seccionIngredientes = document.getElementById('seccionIngredientes');
            const seccionSinIngredientes = document.getElementById('seccionSinIngredientes');
            if (this.value === 'COCINA') {
                seccionIngredientes.style.display = 'block';
                seccionSinIngredientes.style.display = 'none';
            } else {
                seccionIngredientes.style.display = 'none';
                seccionSinIngredientes.style.display = 'flex';
            }
            validateForm();
        });

        // Observador de mutaciones para validar si manipulan el DOM desde Inspect Element
        domObserver = new MutationObserver((mutationsList) => {
            let shouldValidate = false;
            for (let mutation of mutationsList) {
                if (mutation.type === 'attributes' && (mutation.attributeName === 'class' || mutation.attributeName === 'style')) {
                    continue; // Evita bucle infinito cuando validateForm agrega clases o estilos
                }
                shouldValidate = true;
                break;
            }
            if (shouldValidate) {
                validateForm();
                // Limpiar la cola de mutaciones generadas por validateForm para evitar bucle infinito
                domObserver.takeRecords();
            }
        });

        const catSelectObserver = document.getElementById('id_categoria');
        if (catSelectObserver) {
            domObserver.observe(catSelectObserver, { attributes: true, childList: true, subtree: true, characterData: true });
        }

        // Observar las tablas de recetas completas
        const tbodyP = document.querySelector('#tablaPrincipales tbody');
        if (tbodyP) domObserver.observe(tbodyP, { attributes: true, childList: true, subtree: true, characterData: true });

        const tbodyA = document.querySelector('#tablaAdicionales tbody');
        if (tbodyA) domObserver.observe(tbodyA, { attributes: true, childList: true, subtree: true, characterData: true });
    }

    function validateForm() {
        const nombre = document.getElementById('nombre').value.trim();
        const precio = document.getElementById('precio').value;
        const tipo = document.getElementById('tipo_producto').value;
        const categoria = document.getElementById('id_categoria').value;
        const descripcion = document.getElementById('descripcion').value.trim();
        const idProducto = document.getElementById('id_producto').value;

        let isValid = true;

        // Validación de nombre duplicado
        const inputNombre = document.getElementById('nombre');
        const errorNombre = document.getElementById('errorNombre');

        let isDuplicate = false;
        if (nombre) {
            const nombreLower = nombre.toLowerCase();
            isDuplicate = productosActuales.some(p =>
                p.nombre_producto.toLowerCase() === nombreLower &&
                p.estatus == 1 &&
                p.id_producto != idProducto
            );
        }

        if (nombre.length > 0 && nombre.length < 3) {
            isValid = false;
            inputNombre.classList.add('is-invalid');
            if (errorNombre) {
                errorNombre.classList.add('invalid-tooltip', 'd-inline-block');
                errorNombre.textContent = 'El nombre debe tener al menos 3 caracteres.';
            }
        } else if (isDuplicate) {
            isValid = false;
            inputNombre.classList.add('is-invalid');
            if (errorNombre) {
                errorNombre.classList.add('invalid-tooltip', 'd-inline-block');
                errorNombre.textContent = 'Ya existe un producto registrado con este nombre.';
            }
        } else {
            inputNombre.classList.remove('is-invalid');
            if (errorNombre) {
                errorNombre.classList.remove('invalid-tooltip', 'd-inline-block');
                errorNombre.textContent = '';
            }
        }

        // Validación de imagen
        const imagenFile = document.getElementById('imagen').files[0];
        const imagenGaleria = document.getElementById('imagen_galeria').value;

        let hasImage = false;

        if (imagenFile || imagenGaleria) {
            hasImage = true;
            if (imagenFile) {
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                const extension = imagenFile.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(extension)) {
                    isValid = false;
                }
            }
        } else if (idProducto !== '') {
            // Es edición, mantiene la imagen anterior
            hasImage = true;
        }

        if (!hasImage) {
            isValid = false;
        }

        if (!nombre || !precio || !tipo || !categoria || !descripcion) {
            isValid = false;
        }

        if (parseFloat(precio) <= 0 || isNaN(parseFloat(precio))) {
            isValid = false;
        }

        // Validación de categoría contra manipulación del DOM
        const catSelect = document.getElementById('id_categoria');
        const catError = catSelect ? catSelect.nextElementSibling : null;
        if (categoria && validCategorias.length > 0 && !validCategorias.includes(categoria)) {
            isValid = false;
            catSelect.classList.add('is-invalid');
            if (catError) {
                catError.classList.add('invalid-tooltip', 'd-inline-block');
                catError.textContent = 'El valor de categoría seleccionado no existe.';
            }
        } else {
            if (catSelect) catSelect.classList.remove('is-invalid');
            if (catError) {
                catError.classList.remove('invalid-tooltip', 'd-inline-block');
                catError.textContent = '';
            }
        }

        if (tipo === 'COCINA') {
            if (listPrincipales.length === 0) {
                isValid = false;
            } else {
                const allValidQty = listPrincipales.every(i => parseFloat(i.cantidad) > 0);
                if (!allValidQty) isValid = false;

                // Validar DOM e ingredientes (Principales)
                const rowsP = document.querySelectorAll('#tablaPrincipales tbody tr:not(.empty-row)');
                rowsP.forEach(row => {
                    const rowId = row.getAttribute('data-id');
                    const ingMemoria = listPrincipales.find(item => item.id == rowId);

                    const select = row.querySelector('.unit-select');
                    const inputQty = row.querySelector('.qty-input');
                    const spanNombre = row.querySelector('span.fw-semibold');
                    const rowError = row.querySelector('.row-error');

                    let isRowTampered = false;
                    let errorMsg = "";

                    if (!ingMemoria) {
                        isRowTampered = true;
                        errorMsg = "¡ID del ingrediente no existe!";
                    } else if (!spanNombre || spanNombre.textContent !== ingMemoria.nombre) {
                        isRowTampered = true;
                        errorMsg = "¡Nombre del ingrediente no existe!";
                    } else if (!select || select.getAttribute('data-id') != rowId) {
                        isRowTampered = true;
                        errorMsg = "¡ID de unidad no existe!";
                    } else if (!inputQty || inputQty.getAttribute('data-id') != rowId) {
                        isRowTampered = true;
                        errorMsg = "¡ID del input de cantidad alterado!";
                    }

                    if (isRowTampered) {
                        isValid = false;
                        row.classList.add('table-danger');
                        if (rowError) {
                            rowError.classList.add('invalid-tooltip', 'd-inline-block');
                            rowError.textContent = errorMsg;
                        }
                    } else {
                        row.classList.remove('table-danger');
                        if (rowError) {
                            rowError.classList.remove('invalid-tooltip', 'd-inline-block');
                            rowError.textContent = '';
                        }

                        // Validar unidad
                        const validUnit = unidadesDB.some(u => u.id_unidad == select.value);
                        const errUnit = select.closest('td').querySelector('.invalid-tooltip');
                        if (!validUnit) {
                            isValid = false;
                            select.classList.add('is-invalid');
                            if (errUnit) {
                                errUnit.classList.add('invalid-tooltip', 'd-inline-block');
                                errUnit.textContent = "Valor de la unidad no existe";
                            }
                        } else {
                            select.classList.remove('is-invalid');
                            if (errUnit) {
                                errUnit.classList.remove('invalid-tooltip', 'd-inline-block');
                                errUnit.textContent = '';
                            }
                        }
                        ingMemoria.unidad = select.value; // Sincronizar
                    }
                });

                // Si borraron filas completas del DOM
                if (rowsP.length !== listPrincipales.length) isValid = false;
            }

            if (listAdicionales.length > 0) {
                const allValidAdic = listAdicionales.every(i =>
                    parseFloat(i.cantidad) > 0 &&
                    !isNaN(parseFloat(i.precio)) &&
                    parseFloat(i.precio) > 0
                );
                if (!allValidAdic) isValid = false;

                // Validar DOM e ingredientes (Adicionales)
                const rowsA = document.querySelectorAll('#tablaAdicionales tbody tr:not(.empty-row)');
                rowsA.forEach(row => {
                    const rowId = row.getAttribute('data-id');
                    const ingMemoria = listAdicionales.find(item => item.id == rowId);

                    const select = row.querySelector('.unit-select');
                    const inputQty = row.querySelector('.qty-input');
                    const inputPrice = row.querySelector('.price-input');
                    const spanNombre = row.querySelector('span.fw-semibold');
                    const rowError = row.querySelector('.row-error');

                    let isRowTampered = false;
                    let errorMsg = "";

                    if (!ingMemoria) {
                        isRowTampered = true;
                        errorMsg = "¡Fila o ID del ingrediente alterado!";
                    } else if (!spanNombre || spanNombre.textContent !== ingMemoria.nombre) {
                        isRowTampered = true;
                        errorMsg = "¡Nombre del ingrediente alterado!";
                    } else if (!select || select.getAttribute('data-id') != rowId) {
                        isRowTampered = true;
                        errorMsg = "¡ID del selector de unidad alterado!";
                    } else if (!inputQty || inputQty.getAttribute('data-id') != rowId) {
                        isRowTampered = true;
                        errorMsg = "¡ID del input de cantidad alterado!";
                    } else if (!inputPrice || inputPrice.getAttribute('data-id') != rowId) {
                        isRowTampered = true;
                        errorMsg = "¡ID del input de precio alterado!";
                    }

                    if (isRowTampered) {
                        isValid = false;
                        row.classList.add('table-danger');
                        if (rowError) {
                            rowError.classList.add('invalid-tooltip', 'd-inline-block');
                            rowError.textContent = errorMsg;
                        }
                    } else {
                        row.classList.remove('table-danger');
                        if (rowError) {
                            rowError.classList.remove('invalid-tooltip', 'd-inline-block');
                            rowError.textContent = '';
                        }

                        const validUnit = unidadesDB.some(u => u.id_unidad == select.value);
                        const errUnit = select.closest('td').querySelector('.invalid-tooltip');
                        if (!validUnit) {
                            isValid = false;
                            select.classList.add('is-invalid');
                            if (errUnit) {
                                errUnit.classList.add('invalid-tooltip', 'd-inline-block');
                                errUnit.textContent = "Unidad no válida";
                            }
                        } else {
                            select.classList.remove('is-invalid');
                            if (errUnit) {
                                errUnit.classList.remove('invalid-tooltip', 'd-inline-block');
                                errUnit.textContent = '';
                            }
                        }
                        ingMemoria.unidad = select.value;
                    }
                });

                if (rowsA.length !== listAdicionales.length) isValid = false;
            }
        }

        document.getElementById('btnGuardarMenu').disabled = !isValid;
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
                    validateForm();
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

        document.getElementById('tipo_producto').dispatchEvent(new Event('change'));
        validateForm();

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
        validateForm();
    }

    function removeIngrediente(id, isPrincipal) {
        if (isPrincipal) {
            listPrincipales = listPrincipales.filter(i => i.id !== id);
        } else {
            listAdicionales = listAdicionales.filter(i => i.id !== id);
        }
        renderReceta();
        validateForm();
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

        list.forEach((ing, index) => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', ing.id);

            // Obtener el ingrediente correspondiente de ingredientesDB para saber su unidad base/tipo
            const ingredienteInfo = ingredientesDB.find(i => i.id_ingrediente === ing.id);
            const idUnidadMedida = ingredienteInfo ? ingredienteInfo.id_unidad_medida : null;
            const unidadBase = unidadesDB.find(u => u.id_unidad === idUnidadMedida);
            const tipoUnidad = unidadBase ? unidadBase.tipo : null;

            // Filtrar unidadesDB para que solo muestre las del mismo tipo
            const unidadesFiltradas = tipoUnidad 
                ? unidadesDB.filter(u => u.tipo === tipoUnidad)
                : unidadesDB;

            const unidadesHtml = unidadesFiltradas.map(u =>
                `<option value="${u.id_unidad}" ${u.id_unidad == ing.unidad ? 'selected' : ''}>${u.abreviatura}</option>`
            ).join('');

            let precioHtml = '';
            if (!isPrincipal) {
                precioHtml = `
                <td>
                     <input type="number" step="0.01" min="0" class="form-control form-control-sm price-input" 
                        data-id="${ing.id}" data-type="adicional" value="${ing.precio || 0}" required>
                </td>`;
            }

            tr.innerHTML = `
                <td class="position-relative">
                    <span class="fw-semibold">${ing.nombre}</span>
                    <span class="invalid-tooltip fw-bold row-error" style="font-size: 0.75rem; width: fit-content;"></span>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" class="form-control form-control-sm qty-input" 
                        data-id="${ing.id}" data-type="${isPrincipal ? 'principal' : 'adicional'}" value="${ing.cantidad}" required>
                </td>
                <td class="position-relative">
                    <select class="form-select form-select-sm unit-select" data-id="${ing.id}" data-type="${isPrincipal ? 'principal' : 'adicional'}">
                        ${unidadesHtml}
                    </select>
                    <span class="invalid-tooltip fw-bold" style="font-size: 0.75rem; width: fit-content;">Valor no existe</span>
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

            const unitSelect = tr.querySelector('.unit-select');
            unitSelect.addEventListener('change', (e) => updateIngrediente(ing.id, isPrincipal, 'unidad', e.target.value));

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
        validateForm();
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

                document.getElementById('tipo_producto').dispatchEvent(new Event('change'));
                validateForm();

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