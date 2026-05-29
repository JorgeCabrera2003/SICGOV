export function GenerarMensaje(icono, tiempo, titulo, mensaje) {
  Swal.fire({
    icon: icono,
    timer: tiempo,
    title: titulo,
    text: mensaje,
    showConfirmButton: true,
    confirmButtonText: 'Aceptar',
  });
};

export function MensajeHTTP(codigo = null) {
  let mensaje = "";
  const CODIGOS = {
    '400': 'Datos del Formulario no Válidos',
    '403': 'No tienes permiso para realizar esta acción',
    '409': 'Registro duplicado',
    '500': 'Ups, intente de nuevo más tarde'
  }
  const DEFAULT = "Algo no a salido bien..."

  mensaje = CODIGOS[codigo] || DEFAULT

  return mensaje;
}

export async function MostrarConfirmacion(titulo, mensaje, icono) {
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
      console.log("Confirmado");
      resultado = true;
    } else {
      console.log("Negado");
      resultado = false;
    }
  });

  return resultado;
}

export function DiccionarioValidacion(termino) {
  let mensaje = "";
  const RESPUESTAS = {
    'Cedula': "El formato es 00000000",
    'DocumentoLegal': "El formato es 00000000",
    'FormatoCedula': "El formato para la Céduia es V-00000000",
    'FormatoDocumentoLegal': "El formato para el Documento Legal es es J-00000000",
    'ID': "Datos ingresados no válidos",
    'NombrePersona': "El nombre debe tener 3 a 65 carácteres",
    'NombreUsuario': "El nombre de usuario debe tener 3 a 65 carácteres sin espacios",
    'NombreObjeto': "El nombre debe tener 3 a 65 carácteres",
    'Telefono': "El formato para el número de telefono es: 0000-0000000",
    'Telefono-Segmento': "El número debe tener 7 dígitos en este campo",
    'Correo': "El formato para el correo eléctronico es: usuario@servidor.com",
    'Titulo': "Contenido no válido",
    'Direccion': "Dirección no válida",
    'NumeroDecimal': "Solo se permiten números",

  };
  const DEFAULT = '';

  mensaje = RESPUESTAS[termino] || DEFAULT;
  return mensaje;
}

export function FeedbackToltipInput(etiqueta, span, mensaje, estado = 1) {
  etiqueta.removeClass("is-valid is-invalid");
  span.removeClass("valid-tooltip-tooltip invalid-tooltip");
  span.text("");

  if (estado == 1) {
    etiqueta.addClass("is-valid");
    span.text("");
  } else {
    etiqueta.addClass("is-invalid");
    span.addClass("invalid-tooltip");
    span.text(mensaje);
  }
}