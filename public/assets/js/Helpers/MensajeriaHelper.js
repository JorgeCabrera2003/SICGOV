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

export function DiccionarioValidacion(termino){
    let mensaje = "";
        const RESPUESTAS = {
        'Cedula': "Error: el formato es 00000000",
        'DocumentoLegal': "Error: el formato es 00000000",
        'ID': "Error: ID ingresado no válido",
        'NombrePersona': "El nombre debe tener 3 a 65 carácteres",
        'NombreUsuario': "El nombre de usuario debe tener 3 a 65 carácteres sin espacios",
        'NombreObjeto': "El nombre debe tener 3 a 65 carácteres",
        'Telefono': "Error: el formato para el número de telefono es: 0000-0000000",
        'Telefono-Segmento': "Error: el número debe tener 7 dígitos en este campo",
        'Correo': "Error: el formato del correo eléctronico es: usuario@servidor.com",
        'Titulo': "Error: contenido no válido",
        'Direccion': "Error: dirección no válida",
    };
    const DEFAULT = '';

    mensaje = RESPUESTAS[termino] || DEFAULT;
    return mensaje;
}