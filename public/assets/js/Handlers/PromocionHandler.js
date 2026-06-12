import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

let productosSeleccionados = [];

function EtiquetasFormulario(etiquetas) {
  const inputPromocion = {
    id_promocion: $('#id_promocion'),
    productos: $('#productos'),
    nombre: $('#nombre'),
    tipo_descuento: $('#tipo_descuento'),
    valor_descuento: $('#valor_descuento'),
    descripcion: $('#descripcion'),
    fecha_inicio: $('#fecha_inicio'),
    fecha_fin: $('#fecha_fin'),
    hora_inicio: $('#hora_inicio'),
    hora_fin: $('#hora_fin')
  };

  const spanPromocion = {
    producto: $('#sproducto'),
    nombre: $('#snombre'),
    tipo_descuento: $('#stipo_descuento'),
    valor_descuento: $('#svalor_descuento'),
    descripcion: $('#sdescripcion'),
    fecha_inicio: $('#sfecha_inicio'),
    fecha_fin: $('#sfecha_fin'),
    hora_inicio: $('#shora_inicio'),
    hora_fin: $('#shora_fin')
  };

  if (etiquetas === 'input') return inputPromocion;
  if (etiquetas === 'span') return spanPromocion;
  return null;
}

function EtiquetasModal(etiqueta) {
  const modalPromocion = {
    modal: $('#modalPromocion'),
    titulo: $('#modalTitleTextPromocion'),
    boton: $('#btnPromocionForm')
  };

  if (etiqueta === 'Promocion') return modalPromocion;
  return null;
}

export function EditarModal(operacion) {
  const etiqueta_modal = EtiquetasModal('Promocion');
  let titulo = '';
  let boton = '';

  const form = $('#formPromocion');
  form.find('input, select, textarea').prop('readonly', false).prop('disabled', false);

  if (operacion === 'registrar') {
    titulo = 'Registrar Promoción';
    boton = 'Nuevo';
  }

  if (operacion === 'modificar') {
    titulo = 'Modificar Promoción';
    boton = 'Actualizar';
  }

  if (operacion === 'eliminar') {
    titulo = 'Eliminar Promoción';
    boton = 'Borrar';
    form.find('input[type!=hidden], select, textarea').prop('readonly', true).prop('disabled', true);
  }

  etiqueta_modal.titulo.text(titulo);
  etiqueta_modal.boton.text(boton);
  etiqueta_modal.modal.modal('show');
}

export async function EnviarDatos(operacion) {
  const input = EtiquetasFormulario('input');
  const modal = EtiquetasModal('Promocion');
  const peticion = new FormData();

  peticion.append('peticion', operacion);

  if (operacion === 'modificar' || operacion === 'eliminar') {
    peticion.append('id_promocion', input.id_promocion.val());
  }

  if (operacion !== 'eliminar') {
    if (!ValidarEnvio()) return { resultado: 400, mensaje: 'Datos no válidos' };
    peticion.append('nombre', input.nombre.val().trim());
    peticion.append('tipo_descuento', input.tipo_descuento.val());
    peticion.append('valor_descuento', input.valor_descuento.val().trim());
    peticion.append('descripcion', input.descripcion.val().trim());
    peticion.append('fecha_inicio', input.fecha_inicio.val());
    peticion.append('fecha_fin', input.fecha_fin.val());
    peticion.append('hora_inicio', input.hora_inicio.val());
    peticion.append('hora_fin', input.hora_fin.val());
    peticion.append('productos', input.productos.val());
  }

  const json = await AjaxHelper.enviaAjax(peticion);

  if (typeof json?.resultado === 'number' && json.resultado >= 200 && json.resultado < 300) {
    modal.modal.modal('hide');
    MensajeriaHelper.GenerarMensaje(json.icon, 5000, json.mensaje, null);
  } else {
    MensajeriaHelper.GenerarMensaje(json?.icon || 'error', 5000, json?.mensaje || 'Error en la operación', null);
  }

  return json;
}

export async function EnviarFormulario(btn_string) {
  const MANEJADOR = {
    'Nuevo': 'registrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  };

  const accion = MANEJADOR[btn_string] || null;
  if (accion !== null) {
    return await EnviarDatos(accion);
  }
  return { resultado: 400, mensaje: 'Acción no reconocida' };
}

export function CapaValidar() {
  KeyUpPromocion();
  iniciarCatalogoProductos();
}

function limpiarValidacionPromocion() {
  const form = $('#formPromocion');
  form.find('input, select, textarea').removeClass('is-valid is-invalid');
  form.find('.valid-feedback').removeClass('valid-feedback').text('');
}

function KeyUpPromocion() {
  const input = EtiquetasFormulario('input');
  $(input.nombre).on('keyup input', () => {
    $('#snombre').text('');
    $(input.nombre).removeClass('is-valid is-invalid');
  });
  $(input.tipo_descuento).on('change', () => {
    $('#stipo_descuento').text('');
    $(input.tipo_descuento).removeClass('is-valid is-invalid');
    limpiarValidacionPromocion();
    actualizarEtiquetaValorDescuento();
  });
  $(input.valor_descuento).on('input', () => {
    $('#svalor_descuento').text('');
    $(input.valor_descuento).removeClass('is-valid is-invalid');
    aplicarFormatoValorDescuento();
  });
  $(input.fecha_inicio).on('change', () => {
    $('#sfecha_inicio').text('');
    $(input.fecha_inicio).removeClass('is-valid is-invalid');
  });
  $('#formPromocion').on('input change focus', 'input, select, textarea', function () {
    const span = $('#s' + $(this).attr('id'));
    span.removeClass('valid-feedback invalid-feedback').text('');
    $(this).removeClass('is-valid is-invalid');
  });
  $('#modalPromocion').on('shown.bs.modal', limpiarValidacionPromocion);
}

function actualizarEtiquetaValorDescuento() {
  const tipo = $('#tipo_descuento').val();
  const label = $('#valorDescLabel');
  if (tipo === 'MONTO_FIJO') {
    label.text('$');
  } else {
    label.text('- %');
  }
  aplicarFormatoValorDescuento();
}

function formatValorDescuento(valor) {
  const digits = String(valor || '').replace(/\D/g, '');
  if (digits.length === 0) return '00,00';
  if (digits.length === 1) return '00,0' + digits;
  if (digits.length === 2) return '00,' + digits;
  const cents = digits.slice(-2);
  const integer = digits.slice(0, -2).replace(/^0+/, '') || '0';
  return integer + ',' + cents;
}

function normalizarValorDescuento(valor) {
  const normalized = String(valor || '').replace(/\s/g, '').replace(',', '.').replace(/[^0-9\.]/g, '');
  return Number(normalized) || 0;
}

function aplicarFormatoValorDescuento() {
  const input = EtiquetasFormulario('input').valor_descuento;
  input.val(formatValorDescuento(input.val()));
}

function formatMoney(valor) {
  const numero = Number(String(valor || '').replace(/,/g, '.')) || 0;
  return new Intl.NumberFormat('es-VE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numero);
}

function iniciarCatalogoProductos() {
  const searchInput = $('#buscar_producto_promocion');
  const btnCatalogo = $('#btnVerCatalogoPromocion');
  const collapseProductos = new bootstrap.Collapse($('#catalogoProductosPromocion')[0], { toggle: false });

  renderCatalogoProductos(productosDB || []);

  searchInput.off('input').on('input', function () {
    collapseProductos.show();
    renderCatalogoProductos(filtrarProductos($(this).val()));
  });

  btnCatalogo.off('click').on('click', function () {
    const query = searchInput.val().trim();
    if (query === '') {
      renderCatalogoProductos(productosDB || []);
    } else {
      renderCatalogoProductos(filtrarProductos(query));
    }
    collapseProductos.show();
    searchInput.focus();
  });

  $('#listaProductosPromocionUI').off('click', '.btn-seleccionar-producto').on('click', '.btn-seleccionar-producto', function () {
    const item = $(this).closest('.list-group-item');
    const productoId = item.data('id');
    const productoNombre = item.data('nombre');
    const productoPrecio = Number(item.data('precio') || 0);
    agregarProductoSeleccionado(productoId, productoNombre, productoPrecio);
  });
}

function filtrarProductos(termino) {
  termino = termino.trim().toLowerCase();
  if (termino === '') return productosDB || [];
  return (productosDB || []).filter(p => {
    const nombre = (p.nombre_producto || '').toLowerCase();
    const categoria = (p.categoria_nombre || '').toLowerCase();
    return nombre.includes(termino) || categoria.includes(termino);
  });
}

function renderCatalogoProductos(productos) {
  const lista = $('#listaProductosPromocionUI');
  lista.empty();

  if (!Array.isArray(productos) || productos.length === 0) {
    lista.append('<div class="text-center text-muted py-3">No se encontraron productos</div>');
    return;
  }

  productos.forEach(producto => {
    const nombre = $('<div>').text(producto.nombre_producto || '').html();
    const categoria = $('<div>').text(producto.categoria_nombre || 'Sin categoría').html();
    const precio = producto.precio !== undefined ? formatMoney(producto.precio) : null;
    const infoPrecio = precio ? `<div><small class="text-muted">Precio original: $ ${precio}</small></div>` : '';
    const item = $(
      `<a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center flex-wrap" data-id="${producto.id_producto || ''}" data-nombre="${nombre}" data-precio="${producto.precio ?? 0}">
        <div class="me-3">
          <strong>${nombre}</strong><br>
          <small class="text-muted">${categoria}</small>
          ${infoPrecio}
        </div>
        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold btn-seleccionar-producto">Agregar</button>
      </a>`
    );
    lista.append(item);
  });
}

function agregarProductoSeleccionado(id, nombre, precio = 0, render = true) {
  if (!id) return;
  const productoExistente = productosSeleccionados.find(p => p.id === id);
  if (productoExistente) {
    productoExistente.cantidad = Number(productoExistente.cantidad || 1) + 1;
    productoExistente.precio = Number(precio || productoExistente.precio || 0);
  } else {
    productosSeleccionados.push({ id: id, nombre: nombre, cantidad: 1, precio: Number(precio || 0) });
  }
  if (render) renderProductosSeleccionados();
}

function renderProductosSeleccionados() {
  const input = EtiquetasFormulario('input');
  const lista = $('#productosSeleccionadosLista');
  const contador = $('#contadorProductosSeleccionados');
  lista.empty();

  if (productosSeleccionados.length === 0) {
    lista.append('<div class="list-group-item bg-light text-center text-muted">No hay productos seleccionados</div>');
  } else {
    productosSeleccionados.forEach(producto => {
      const cantidad = Number(producto.cantidad || 1);
      const precioUnitario = Number(producto.precio || 0);
      const totalProducto = precioUnitario * cantidad;
      const item = $(
        '<div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">' +
          '<div class="flex-grow-1">' +
            '<div class="d-flex justify-content-between align-items-center gap-2 mb-2">' +
              '<strong>' + $('<div>').text(producto.nombre).html() + '</strong>' +
            '</div>' +
            '<div class="text-muted small">Precio unitario: $ ' + formatMoney(precioUnitario) + '</div>' +
            '<div class="text-muted small">Total: $ ' + formatMoney(totalProducto) + '</div>' +
          '</div>' +
          '<div class="d-flex align-items-center gap-2">' +
            '<div class="input-group input-group-sm" style="width:110px;">' +
              '<span class="input-group-text">Cant.</span>' +
              '<input type="number" step="1" min="1" class="form-control cantidad-producto" data-id="' + producto.id + '" value="' + (producto.cantidad === '' ? '' : cantidad) + '">' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-producto" data-id="' + producto.id + '">Eliminar</button>' +
          '</div>' +
        '</div>'
      );
      lista.append(item);
    });
    // subtotal global
    const subtotal = productosSeleccionados.reduce((s, p) => s + (Number(p.precio || 0) * Number(p.cantidad || 1)), 0);
    lista.append('<div class="list-group-item d-flex justify-content-between align-items-center"><div>Subtotal</div><div><strong>$ ' + formatMoney(subtotal) + '</strong></div></div>');
  }

  const totalCantidad = productosSeleccionados.reduce((sum, producto) => sum + Number(producto.cantidad || 0), 0);
  contador.text(totalCantidad + ' producto' + (totalCantidad === 1 ? '' : 's'));
  input.productos.val(JSON.stringify(productosSeleccionados.map(p => ({ id: p.id, cantidad: Number(p.cantidad || 1) }))));
}

function actualizarProductosSeleccionados() {
  const input = EtiquetasFormulario('input');
  const contador = $('#contadorProductosSeleccionados');
  const totalCantidad = productosSeleccionados.reduce((sum, producto) => sum + Number(producto.cantidad || 0), 0);
  contador.text(totalCantidad + ' producto' + (totalCantidad === 1 ? '' : 's'));
  input.productos.val(JSON.stringify(productosSeleccionados.map(p => ({ id: p.id, cantidad: Number(p.cantidad || 1) }))));
}

$(document).off('input', '.cantidad-producto').on('input', '.cantidad-producto', function () {
  const id = $(this).data('id');
  let raw = $(this).val();
  raw = String(raw).replace(/\D/g, '');
  $(this).val(raw);

  const producto = productosSeleccionados.find(p => p.id === id);
  if (producto) {
    producto.cantidad = raw === '' ? '' : Number(raw);
    // actualizar contador y campo oculto sin re-render para no perder foco
    actualizarProductosSeleccionados();
  }
});

$(document).off('blur', '.cantidad-producto').on('blur', '.cantidad-producto', function () {
  const id = $(this).data('id');
  const raw = $(this).val().trim();
  const producto = productosSeleccionados.find(p => p.id === id);
  if (producto) {
    if (raw === '' || Number(raw) < 1) {
      producto.cantidad = 1;
      $(this).val('1');
    } else {
      producto.cantidad = Number(raw);
    }
    // re-render para actualizar subtotal y layout
    renderProductosSeleccionados();
  }
});

$(document).off('click', '.btn-eliminar-producto').on('click', '.btn-eliminar-producto', function () {
  const id = $(this).data('id');
  productosSeleccionados = productosSeleccionados.filter(p => p.id !== id);
  renderProductosSeleccionados();
});

function ValidarEnvio() {
  const input = EtiquetasFormulario('input');
  const span = EtiquetasFormulario('span');
  let valido = true;

  $('.form-label.text-danger').text('');

  if (input.nombre.val().trim() === '') {
    span.nombre.text('Nombre obligatorio');
    valido = false;
  }

  if (input.tipo_descuento.val() === 'default' || input.tipo_descuento.val() === '') {
    span.tipo_descuento.text('Seleccione un tipo de descuento');
    valido = false;
  }

  if (input.valor_descuento.val().trim() === '' || normalizarValorDescuento(input.valor_descuento.val()) <= 0) {
    span.valor_descuento.text('Ingrese un valor de descuento válido');
    valido = false;
  }

  if (input.fecha_inicio.val().trim() === '') {
    span.fecha_inicio.text('Fecha de inicio obligatoria');
    valido = false;
  }

  if (productosSeleccionados.length === 0) {
    span.producto.text('Debe seleccionar al menos un producto');
    valido = false;
  }

  return valido;
}

export async function DataTablePrincipal(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaPromocion')) {
    $('#tablaPromocion').DataTable().destroy();
  }

  function esPromocionActiva(row) {
    if (!row || !row.fecha_inicio) return false;
    const now = new Date();
    const fechaInicio = row.fecha_inicio;
    const fechaFin = row.fecha_fin || row.fecha_inicio;
    const horaInicio = row.hora_inicio || '00:00';
    const horaFin = row.hora_fin || '23:59';
    const inicio = new Date(fechaInicio + 'T' + horaInicio + ':00');
    const fin = new Date(fechaFin + 'T' + horaFin + ':00');
    return now >= inicio && now <= fin;
  }

  // Helper: convierte 'HH:MM' o 'HH:MM:SS' a formato 12h con AM/PM
  function to12Hour(hora) {
    if (!hora) return '';
    const parts = hora.split(':');
    let h = parseInt(parts[0], 10);
    const m = parts[1] || '00';
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    if (h === 0) h = 12;
    return h + ':' + m.padStart(2, '0') + ' ' + ampm;
  }

  // Helper: formatea fecha 'YYYY-MM-DD' a 'DD/MM/YYYY'
  function fmtDate(fecha) {
    if (!fecha) return '';
    // tratar fechas por defecto de MySQL como vacías
    if (/^0{4}-0{2}-0{2}$/.test(fecha)) return '';
    const parts = fecha.split('-');
    if (parts.length < 3) return '';
    if (parts[0] === '0000' || parts[1] === '00' || parts[2] === '00') return '';
    return parts[2] + '/' + parts[1] + '/' + parts[0];
  }

  // Construye HTML de botones igual que en proveedores (dropdown)
  function botonesAccion(modulo = 'Promocion') {
    const dropdown = $('<div>').addClass('dropdown');
    const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
      .attr('type', 'button')
      .attr('data-bs-toggle', 'dropdown')
      .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

    const menu = $('<ul>').addClass('dropdown-menu');
    const separador = $('<li>').html('<hr class="dropdown-divider">');

    const itemEditar = $('<li>');
    const linkEditar = $('<a>')
      .addClass('dropdown-item btn-editar text-primary')
      .attr('href', '#')
      .attr('data-accion', 'modificar')
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-edit me-2"></i>Editar');
    itemEditar.append(linkEditar);

    const itemEliminar = $('<li>');
    const linkEliminar = $('<a>')
      .addClass('dropdown-item btn-eliminar text-danger')
      .attr('href', '#')
      .attr('data-accion', 'eliminar')
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-trash me-2"></i>Eliminar');
    itemEliminar.append(linkEliminar);

    menu.append(itemEditar, separador, itemEliminar);
    dropdown.append(boton, menu);

    return dropdown.prop('outerHTML');
  }

  $('#tablaPromocion').DataTable({
    processing: true,
    data: arreglo,
    createdRow: function (row, data) {
      if (esPromocionActiva(data)) {
        $(row).addClass('table-success');
        $(row).find('td').addClass('text-success');
      }
    },
    columnDefs: [{ className: 'text-center align-middle', targets: '_all' }],
    columns: [
      { data: 'nombre' },
      {
        data: null,
        render: function (row) {
          const productos = (row.producto_list || '').split('||').filter(Boolean).map(entry => {
            const parts = entry.split(':::');
            return {
              nombre: parts[1] ? parts[1].trim() : '',
              cantidad: Number(parts[2] || 1)
            };
          });
          if (productos.length === 0) {
            return '<span class="text-muted">Sin producto</span>';
          }
          return productos.map(prod => {
            const safeNombre = $('<div>').text(prod.nombre).html();
            const qtyBadge = prod.cantidad > 1 ? '<span class="badge bg-secondary ms-2">x' + prod.cantidad + '</span>' : '';
            return '<div>- <strong>' + safeNombre + '</strong>' + qtyBadge + '</div>';
          }).join('');
        }
      },
      {
        data: 'tipo_descuento',
        render: function (val) {
          if (!val) return '';
          const s = val.replace(/_/g, ' ').toLowerCase();
          return s.charAt(0).toUpperCase() + s.slice(1);
        }
      },
      {
        data: 'valor_descuento',
        render: function (val, type, row) {
          if (!val) return '';
          const amount = Number(val).toFixed(2);
          if (row.tipo_descuento === 'MONTO_FIJO') {
            return '<span>$ ' + amount + '</span>';
          }
          return '<span>-' + amount + '%</span>';
        }
      },
      {
        data: null,
        render: function (row) {
          // Fecha inicio y hora inicio juntos
          const fecha = fmtDate(row.fecha_inicio || '');
          const hora = to12Hour(row.hora_inicio || '');
          return '<div>' + fecha + (fecha && hora ? '<br>' + hora : '') + '</div>';
        }
      },
      {
        data: null,
        render: function (row) {
          // Fecha fin + hora fin
          const fecha = fmtDate(row.fecha_fin || '');
          const hora = to12Hour(row.hora_fin || '');
          return '<div>' + fecha + (fecha && hora ? '<br>' + hora : '') + '</div>';
        }
      },
      {
        data: 'descripcion',
        render: function (txt) {
          if (!txt) return '';
          const max = 120;
          const safe = $('<div>').text(txt).html();
          // Mostrar igual que el resto de la tabla (sin small ni text-muted)
          return '<div title="' + safe + '">' + (safe.length > max ? safe.substr(0, max) + '...' : safe) + '</div>';
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function () {
          return botonesAccion();
        }
      }
    ],
    order: [[0, 'asc']],
    language: { url: idiomaTabla }
  });
}


export function LimpiarFormulario() {
  const input = EtiquetasFormulario('input');
  input.id_promocion.val('');
  input.productos.val('');
  input.nombre.val('');
  input.tipo_descuento.val('default');
  input.valor_descuento.val('00,00');
  actualizarEtiquetaValorDescuento();
  input.descripcion.val('');
  input.fecha_inicio.val('');
  input.fecha_fin.val('');
  input.hora_inicio.val('');
  input.hora_fin.val('');

  productosSeleccionados = [];
  renderProductosSeleccionados();

  $('.form-label.text-danger').text('');
  $('#formPromocion').find('input, select, textarea').removeClass('is-valid is-invalid');
  $('#formPromocion').find('.valid-feedback, .invalid-feedback').removeClass('valid-feedback invalid-feedback').text('');

  const form = $('#formPromocion');
  form.find('input, select, textarea').prop('readonly', false).prop('disabled', false);
}

export function EditarFormPromocion(datos, accion) {
  const input = EtiquetasFormulario('input');

  input.id_promocion.val(datos.id_promocion || '');
  input.nombre.val(datos.nombre || '');
  input.tipo_descuento.val(datos.tipo_descuento || 'default');
  input.valor_descuento.val(formatValorDescuento(datos.valor_descuento || ''));
  actualizarEtiquetaValorDescuento();
  input.descripcion.val(datos.descripcion || '');
  input.fecha_inicio.val(datos.fecha_inicio || '');
  input.fecha_fin.val(datos.fecha_fin || '');
  input.hora_inicio.val(datos.hora_inicio || '');
  input.hora_fin.val(datos.hora_fin || '');

  productosSeleccionados = [];
  const listaProductos = datos.producto_list || '';
  if (listaProductos) {
    listaProductos.split('||').forEach(entry => {
      const parts = entry.split(':::');
      const id = parts[0]?.trim();
      const nombre = parts[1]?.trim() || '';
      const cantidad = Number(parts[2] || 1);
      const precio = Number(parts[3] || 0);
      if (id) {
        productosSeleccionados.push({ id: id, nombre: nombre, cantidad: cantidad, precio: precio });
      }
    });
  }
  renderProductosSeleccionados();

  if (accion === 'eliminar') {
    const form = $('#formPromocion');
    form.find('input[type!=hidden], select, textarea').prop('readonly', true).prop('disabled', true);
  }

  EditarModal(accion);
}
