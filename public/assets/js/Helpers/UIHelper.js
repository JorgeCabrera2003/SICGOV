import { capitalizarTexto } from './FormatHelper.js';
import { patrones, SistemaValidacion } from './ValidationHelper.js';

export function limpiarValidacionVisualGlobal() {
  $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
  $('.invalid-feedback, .valid-feedback').removeClass('invalid-feedback valid-feedback').text('');
}

export function estadoSelect(input, span, mensaje, estado) {
  if (estado === 1) {
    $(input).addClass("is-valid").removeClass("is-invalid");
    $(span).removeClass("invalid-feedback").addClass("valid-feedback").text("✓ Correcto");
  } else {
    $(input).addClass("is-invalid").removeClass("is-valid");
    $(span).removeClass("valid-feedback").addClass("invalid-feedback").text(mensaje);
  }
}

export function mensajes(icono, tiempo, titulo, mensaje) {
  Swal.fire({
    icon: icono,
    timer: tiempo,
    title: titulo,
    text: mensaje,
    showConfirmButton: true,
    confirmButtonText: 'Aceptar',
  });
}

export async function confirmarAccion(titulo, mensaje, icono) {
  let resultado = false;
  await Swal.fire({
    title: titulo,
    text: mensaje,
    icon: icono,
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      resultado = true;
    } else {
      resultado = false;
    }
  });
  return resultado;
}

export async function buscarSelect(etiqueta, valor, opcion) {
  if (!etiqueta || !etiqueta.length) {
    console.error("El selector " + etiqueta + " no existe");
    return false;
  }
  if (opcion === 'text') {
    let bool = false;
    etiqueta.each(function () {
      if ($(this).text().trim() === valor.trim()) {
        $(this).prop('selected', true);
        etiqueta.trigger('change');
        bool = true;
        return false;
      }
    });
    if (bool) {
      return true;
    } else {
      console.error("El valor '" + valor + "' no se encuentra en el campo select.")
    }
  } else if (opcion === 'value') {
    if ((`${etiqueta} option[value="${valor}"]`).length > 0) {
      etiqueta.val(`${valor}`).trigger('change');
      return true;
    } else {
      console.error("El valor " + valor + " no se encuentra en el campo select.");
    }
  } else {
    console.error("Opcion no Válida: " + opcion + "")
  }
  return false;
}

export function selectEdificio(arreglo) {
  if (!$("#id_edificio").length) return;
  $("#id_edificio").empty();
  $("#id_edificio").append(new Option('Seleccione un Edificio', 'default'));
  if (Array.isArray(arreglo)) {
    arreglo.forEach(item => {
      $("#id_edificio").append(new Option(item.nombre, item.id_edificio));
    });
  }
}

export function inicializarTooltips() {
  $('[data-bs-toggle="tooltip"]').tooltip({
    trigger: 'hover',
    placement: 'top'
  });
}

export function mostrarLoading(mostrar = true) {
  if (mostrar) {
    $('body').append(`
      <div id="loading-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
      </div>
    `);
  } else {
    $('#loading-overlay').remove();
  }
}

export function UIActionBtn(options = {}) {
    const $dropdown = $('<div>', { class: 'dropdown d-inline-block' });
    const $btn = $('<button>', {
        class: `action-btn dropdown-toggle ${options.class || ''}`,
        type: 'button',
        'data-bs-toggle': 'dropdown',
        'aria-expanded': 'false',
        title: options.title || 'Acciones'
    }).append($('<i>', { class: options.icon || 'fas fa-ellipsis-v' }));

    if (options.text) {
        $btn.append($('<span>', { class: 'ms-2 small fw-bold d-none d-md-inline', text: options.text }));
    }

    const $menu = $('<ul>', { class: 'dropdown-menu dropdown-menu-end shadow-sm' });
    
    if (options.items && Array.isArray(options.items)) {
        options.items.forEach(item => {
            if (item.divider) {
                $menu.append($('<li>').append($('<hr>', { class: 'dropdown-divider' })));
            } else {
                const $link = $('<a>', {
                    class: `dropdown-item d-flex align-items-center ${item.class || ''}`,
                    href: item.href || 'javascript:void(0)',
                    'data-id': item.id || '',
                    onclick: item.onclick || null
                });

                if (item.icon) {
                    $link.append($('<i>', { class: `${item.icon} me-2 opacity-75`, css: { width: '1.2rem' } }));
                }
                $link.append($('<span>', { text: item.text }));
                $menu.append($('<li>').append($link));
            }
        });
    }
    return $dropdown.append($btn, $menu).prop('outerHTML');
}

// INICIALIZAR COMPONENTES GLOBALES AL IMPORTAR
$(document).ready(function () {
  inicializarTooltips();

  $('form').on('submit', function () {
    const $submitBtn = $(this).find('button[type="submit"], input[type="submit"]');
    $submitBtn.prop('disabled', true);
    setTimeout(() => {
      $submitBtn.prop('disabled', false);
    }, 5000);
  });

  $('input[type="text"]').on('blur', function () {
    const $this = $(this);
    if ($this.val()) {
      $this.val(capitalizarTexto($this.val()));
    }
  });

  $('body').on('input', '#nombre, #nombre_material', function () {
    const $this = $(this);
    const valor = $this.val();
    if (valor && patrones.nombre_material.test(valor)) {
      $this.removeClass('is-invalid').addClass('is-valid');
    } else if (valor) {
      $this.removeClass('is-valid').addClass('is-invalid');
    }
  });

  $('body').on('input', '#stock', function () {
    const $this = $(this);
    const valor = $this.val();
    if (valor && patrones.stock_material.test(valor)) {
      $this.removeClass('is-invalid').addClass('is-valid');
    } else if (valor) {
      $this.removeClass('is-valid').addClass('is-invalid');
    }
  });

  $('body').on('input', '#id_material', function () {
    const $this = $(this);
    const valor = $this.val();
    if (valor && patrones.id_material.test(valor)) {
      $this.removeClass('is-invalid').addClass('is-valid');
    } else if (valor) {
      $this.removeClass('is-valid').addClass('is-invalid');
    }
  });

  $('body').on('focus', 'select', function () {
    $(this).data('touched', true);
  });

  $('body').on('change', 'select', function (e) {
    try {
      if (!(e && e.originalEvent) && !$(this).data('touched')) {
        SistemaValidacion.limpiarEstilosCampo($(this));
        return;
      }
      SistemaValidacion.validarCampo.call(this);
    } catch (err) {
      console.error('Error en manejador global de select change:', err);
    }
  });

  console.log("UIHelper cargado completamente (reemplaza $(document).ready de utils)");
});
