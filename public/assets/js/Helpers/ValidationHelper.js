import { capitalizarTexto } from './FormatHelper.js';

export const patrones = {
  // Patrones básicos
  letras: /^[a-zA-ZÀ-ÿ\s]{1,40}$/,
  letrasConNumeros: /^[0-9 a-zA-ZÀ-ÿ\s]{1,100}$/,
  numeros: /^\d{1,20}$/,
  email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  telefono: /^\(\d{3}\)\s\d{3}-\d{4}$/,
  telefonoSimple: /^\d{7}$/,
  username: /^[a-zA-Z0-9_]{4,20}$/,
  password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
  postalCode: /^\d{5}$/,
  direccion: /^[0-9 a-zA-ZÀ-ÿ\s-./#]{10,100}$/,
  nombreEnte: /^[0-9 a-zA-ZáéíóúüñÑçÇ -.]{4,90}$/,
  responsable: /^[a-zA-ZÀ-ÿ\s-.]{4,65}$/,

  // Patrones para Bienes/Equipos
  codigoBien: /^[0-9a-zA-Z\-]{3,20}$/,
  descripcion: /^[0-9 a-zA-ZáéíóúüñÑçÇ -.,]{3,100}$/,
  serial: /^[0-9a-zA-ZáéíóúüñÑçÇ.-]{3,45}$/,
  tipoEquipo: /^[0-9 a-zA-ZáéíóúüñÑçÇ -.]{3,45}$/,
  titulo: /^[0-9a-zA-ZáéíóúüñÑçÇ\s\-.,()!?"'%:;]{3,150}$/,

  // PATRONES PARA MATERIAL
  id_material: /^[A-Z0-9\-_]{1,50}$/,
  nombre_material: /^[0-9a-zA-ZáéíóúüñÑçÇ\s\-.,()]{3,100}$/,
  id_oficina: /^[A-Z0-9]{1,30}$/,
  stock_material: /^[0-9]{1,6}$/,

  // Patrones para IDs generados
  id_generado: /^[A-Z0-9]{1,30}$/,

  // Patrones para nombres
  nombre_natural_largo: /^[0-9a-zA-ZáéíóúüñÑçÇ\s\-.,()]{1,100}$/,
  nombre_natural: /^[a-zA-ZáéíóúüñÑçÇ\s\-.]{1,65}$/,

  // Patrones para textos largos
  texto_largo: /^[0-9a-zA-ZáéíóúüñÑçÇ\s\-.,()!?]{1,200}$/,

  // Patrones para cédulas
  cedula: /^[VEJPGvejpg]\-\d{4,10}$/,

  // Patrones para estados
  estado_simple: /^[A-Za-z\s]{1,20}$/,

  // Patrones para fechas
  fecha: /^\d{4}-\d{2}-\d{2}$/,
  fecha_hora: /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/
};

export const SistemaValidacion = {
  elementos: {},
  callbackCambioEstado: null,

  inicializar: function (elements, callbackCambioEstado = null) {
    this.elementos = elements;
    this.callbackCambioEstado = callbackCambioEstado;

    $.each(elements, function (key, element) {
      if (element && element.length) {
        element.on('focus', function () {
          $(this).data('touched', true);
        });

        element.on('blur', SistemaValidacion.validarCampo);
        element.on('input', SistemaValidacion.validarCampo);

        if (key === 'descripcion' || key === 'tipo_equipo' || key === 'nombre' || key === 'nombre_material') {
          element.on('blur', function () {
            SistemaValidacion.autoCapitalizar($(this));
          });
        }
      }
    });

    setTimeout(() => {
      const formularioValido = SistemaValidacion.verificarEstadoFormulario();
      if (callbackCambioEstado) {
        callbackCambioEstado(formularioValido);
      }
    }, 100);
  },

  validarCampo: function () {
    const $campo = $(this);
    const valor = $campo.val() ? $campo.val().trim() : '';
    const id = this.id;
    let esValido = true;
    let mensajeError = '';
    const fueInteractuado = $campo.data('touched') || $campo.is(':focus');

    const regla = $campo.data('regla');
    const requerido = $campo.data('requerido');

    if (requerido && (valor === '' || valor === 'default' || valor === null)) {
      esValido = false;
      mensajeError = $campo.data('mensaje') || 'Este campo es requerido';
    } else if (valor !== '' && regla && patrones[regla]) {
      esValido = patrones[regla].test(valor);
      mensajeError = $campo.data('mensaje') || 'Formato no válido';
    } else if (!regla && id) {
      switch (id) {
        case 'codigo_bien':
          esValido = patrones.codigoBien.test(valor);
          mensajeError = 'El código del bien debe tener de 3 a 20 caracteres (letras, números y guiones)';
          break;
        case 'descripcion':
          esValido = patrones.descripcion.test(valor);
          mensajeError = 'La descripción debe tener de 3 a 100 caracteres';
          break;
        case 'serial_equipo':
        case 'serial':
          esValido = patrones.serial.test(valor);
          mensajeError = 'El serial debe tener de 3 a 45 caracteres';
          break;
        case 'tipo_equipo':
          esValido = patrones.tipoEquipo.test(valor);
          mensajeError = 'El tipo de equipo debe tener de 3 a 45 caracteres';
          break;
        case 'nombre':
        case 'nombre_material':
          esValido = patrones.nombre_material.test(valor);
          mensajeError = 'El nombre debe tener 3-100 caracteres alfanuméricos';
          break;
        case 'stock':
          esValido = patrones.stock_material.test(valor);
          mensajeError = 'El stock debe ser un número entre 0 y 999999';
          break;
        case 'id_material':
          esValido = patrones.id_material.test(valor);
          mensajeError = 'ID no válido. Debe ser alfanumérico de 1-50 caracteres';
          break;
        case 'id_categoria':
        case 'id_marca':
        case 'id_oficina':
        case 'ubicacion':
        case 'cedula_empleado':
        case 'id_unidad_equipo':
        case 'estado':
        case 'rol':
        case 'estatus':
        case 'tipo':
          esValido = valor !== "default" && valor !== "" && valor !== null;
          mensajeError = 'Debe seleccionar una opción válida';
          break;
        case 'cedula_solicitante':
        case 'cedula_empleado':
        case 'cedula_tecnico':
          esValido = patrones.cedula.test(valor);
          mensajeError = 'La cédula debe tener formato V-12345678';
          break;
        case 'correo':
        case 'correo_empleado':
        case 'email':
          esValido = (valor === '') ? true : patrones.email.test(valor);
          mensajeError = 'El formato del email no es válido';
          break;
        case 'telefono_empleado':
        case 'telefono':
          esValido = (valor === '') ? true : patrones.telefonoSimple.test(valor);
          mensajeError = 'El teléfono debe tener formato 0412-1234567 o 7 dígitos';
          break;
        default:
          if ($campo.attr('type') === 'text' || $campo.is('input') || $campo.is('textarea')) {
            esValido = valor.length >= 1;
            mensajeError = 'Este campo es requerido';
          }
      }
    }

    if (fueInteractuado) {
      SistemaValidacion.aplicarEstilos($campo, esValido, mensajeError);
    } else {
      SistemaValidacion.limpiarEstilosCampo($campo);
    }

    if (SistemaValidacion.callbackCambioEstado) {
      const formularioValido = SistemaValidacion.verificarEstadoFormulario();
      SistemaValidacion.callbackCambioEstado(formularioValido);
    }

    return esValido;
  },

  verificarEstadoFormulario: function () {
    let esValido = true;

    $.each(this.elementos, function (key, elemento) {
      if (elemento && elemento.length && elemento.is(':visible') && !elemento.prop('disabled')) {
        const valor = elemento.val() ? elemento.val().trim() : '';
        let campoValido = true;
        
        const regla = elemento.data('regla');
        const requerido = elemento.data('requerido');

        if (requerido && (valor === '' || valor === 'default' || valor === null)) {
          campoValido = false;
        } else if (valor !== '' && regla && patrones[regla]) {
          campoValido = patrones[regla].test(valor);
        } else if (!regla && elemento.attr('id')) {
          switch (elemento.attr('id')) {
            case 'codigo_bien':
              campoValido = patrones.codigoBien.test(valor);
              break;
            case 'descripcion':
              campoValido = patrones.descripcion.test(valor);
              break;
            case 'serial_equipo':
            case 'serial':
              campoValido = patrones.serial.test(valor);
              break;
            case 'tipo_equipo':
              campoValido = patrones.tipoEquipo.test(valor);
              break;
            case 'nombre':
            case 'nombre_material':
              campoValido = patrones.nombre_material.test(valor);
              break;
            case 'stock':
              campoValido = patrones.stock_material.test(valor);
              break;
            case 'id_material':
              campoValido = patrones.id_material.test(valor);
              break;
            case 'id_categoria':
            case 'id_marca':
            case 'id_oficina':
            case 'ubicacion':
            case 'cedula_empleado':
            case 'id_unidad_equipo':
            case 'estado':
            case 'rol':
            case 'estatus':
            case 'tipo':
              campoValido = valor !== "default" && valor !== "" && valor !== null;
              break;
            case 'cedula_solicitante':
            case 'cedula_empleado':
            case 'cedula_tecnico':
              campoValido = patrones.cedula.test(valor);
              break;
            case 'correo':
            case 'correo_empleado':
            case 'email':
              campoValido = (valor === '') ? true : patrones.email.test(valor);
              break;
            case 'telefono_empleado':
            case 'telefono':
              campoValido = (valor === '') ? true : patrones.telefonoSimple.test(valor);
              break;
            default:
              if (elemento.attr('type') === 'text' || elemento.is('input') || elemento.is('textarea')) {
                campoValido = valor.length >= 1;
              }
          }
        }

        if (!campoValido) {
          esValido = false;
        }
      }
    });

    return esValido;
  },

  autoCapitalizar: function ($elemento) {
    const valor = $elemento.val() ? $elemento.val().trim() : '';
    if (valor) {
      const capitalizado = capitalizarTexto(valor);
      $elemento.val(capitalizado);
      setTimeout(() => {
        if ($elemento[0]) {
          SistemaValidacion.validarCampo.call($elemento[0]);
        }
      }, 100);
    }
  },

  aplicarEstilos: function ($elemento, esValido, mensajeError) {
    const id = $elemento.attr('id');
    const $feedback = $(`#s${id}`);

    if (esValido) {
      $elemento.removeClass("is-invalid").addClass("is-valid");
      if ($feedback.length) {
        $feedback.removeClass("invalid-feedback").addClass("valid-feedback").text("✓ Correcto");
      }
    } else {
      $elemento.removeClass("is-valid").addClass("is-invalid");
      if ($feedback.length) {
        $feedback.removeClass("valid-feedback").addClass("invalid-feedback").text(mensajeError);
      }
    }
  },

  limpiarEstilosCampo: function ($elemento) {
    const id = $elemento.attr('id');
    const $feedback = $(`#s${id}`);

    $elemento.removeClass("is-valid is-invalid");
    if ($feedback.length) {
      $feedback.removeClass("invalid-feedback valid-feedback").text("");
    }
  },

  validarFormulario: function (elementos) {
    let esValido = true;

    $.each(elementos, function (key, elemento) {
      if (elemento && elemento.length && elemento.is(':visible') && !elemento.prop('disabled')) {
        elemento.trigger('blur');
        if (elemento.hasClass('is-invalid')) {
          esValido = false;
        }
      }
    });

    return esValido;
  },

  limpiarValidacion: function (elementos) {
    $.each(elementos, function (key, elemento) {
      if (elemento && elemento.length) {
        elemento.removeClass("is-valid is-invalid");
        elemento.removeData('touched');
        const id = elemento.attr('id');
        const $feedback = $(`#s${id}`);
        if ($feedback.length) {
          $feedback.removeClass("invalid-feedback valid-feedback").text("");
        }
      }
    });
  },

  validarFormularioSilencioso: function (elementos) {
    let esValido = true;

    $.each(elementos, function (key, elemento) {
      if (elemento && elemento.length && elemento.is(':visible') && !elemento.prop('disabled')) {
        const valor = elemento.val() ? elemento.val().trim() : '';
        let campoValido = true;

        const regla = elemento.data('regla');
        const requerido = elemento.data('requerido');

        if (requerido && (valor === '' || valor === 'default' || valor === null)) {
          campoValido = false;
        } else if (valor !== '' && regla && patrones[regla]) {
          campoValido = patrones[regla].test(valor);
        } else if (!regla && elemento.attr('id')) {
          switch (elemento.attr('id')) {
            case 'codigo_bien':
              campoValido = patrones.codigoBien.test(valor);
              break;
            case 'descripcion':
              campoValido = patrones.descripcion.test(valor);
              break;
            case 'serial_equipo':
            case 'serial':
              campoValido = patrones.serial.test(valor);
              break;
            case 'tipo_equipo':
              campoValido = patrones.tipoEquipo.test(valor);
              break;
            case 'nombre':
            case 'nombre_material':
              campoValido = patrones.nombre_material.test(valor);
              break;
            case 'stock':
              campoValido = patrones.stock_material.test(valor);
              break;
            case 'id_material':
              campoValido = patrones.id_material.test(valor);
              break;
            case 'id_categoria':
            case 'id_marca':
            case 'id_oficina':
            case 'ubicacion':
            case 'cedula_empleado':
            case 'id_unidad_equipo':
            case 'estado':
            case 'rol':
            case 'estatus':
            case 'tipo':
              campoValido = valor !== "default" && valor !== "" && valor !== null;
              break;
            case 'cedula_solicitante':
            case 'cedula_empleado':
            case 'cedula_tecnico':
              campoValido = patrones.cedula.test(valor);
              break;
            case 'correo_empleado':
            case 'email':
              campoValido = patrones.email.test(valor);
              break;
            case 'telefono_empleado':
            case 'telefono':
              campoValido = patrones.telefonoSimple.test(valor);
              break;
            default:
              if (elemento.attr('type') === 'text' || elemento.is('input') || elemento.is('textarea')) {
                campoValido = valor.length >= 1;
              }
          }
        }

        if (!campoValido) {
          esValido = false;
        }
      }
    });

    return esValido;
  }
};

export function validarEmail(email) {
  return patrones.email.test(email);
}

export function validarMaterialCompleto() {
  const elementos = {
    nombre: $('#nombre'),
    ubicacion: $('#ubicacion'),
    stock: $('#stock'),
    id_material: $('#id_material')
  };
  return SistemaValidacion.validarFormularioSilencioso(elementos);
}

export function limpiarValidacionMaterial() {
  const elementos = {
    nombre: $('#nombre'),
    ubicacion: $('#ubicacion'),
    stock: $('#stock'),
    id_material: $('#id_material')
  };
  SistemaValidacion.limpiarValidacion(elementos);
}
