import { mensajes } from './UIHelper.js';

export function mensajeHTTP(codigo = null) {
  let mensaje = "";
  const CODIGOS = {
    '400': 'Datos del Formulario no Válidos',
    '403': 'No tienes permiso para realizar esta acción',
    '409': 'Registro duplicado',
    '500': 'Ups, intente de nuevo más tarde'
  }
  const DEFAULT = "Algo no ha salido bien..."
  mensaje = CODIGOS[codigo] || DEFAULT
  return mensaje;
}

export async function enviaAjax(datos, controlador = "") {
  let response = null;
  try {
    await $.ajax({
      async: true,
      url: controlador,
      type: "POST",
      contentType: false,
      data: datos,
      processData: false,
      cache: false,
      timeout: 10000,
      success: function (respuesta) {
        if (respuesta == undefined || respuesta == '' || respuesta == null) {
          response = { resultado: 204, mensaje: '' }
        } else {
          try {
             response = (typeof respuesta === 'string') ? JSON.parse(respuesta) : respuesta;
          } catch(e) {
             console.error("Error parseando respuesta JSON:", e, respuesta);
             response = { resultado: 500, mensaje: "Error procesando respuesta del servidor" };
          }
        }
      },
      error: function (request, status, err) {
        let errorMsg = null;
        try {
            if (request.responseText) {
                const jsonErr = JSON.parse(request.responseText);
                errorMsg = jsonErr.mensaje || null;
            }
        } catch(e) {}
        response = {
          resultado: request.status || 500,
          mensaje: errorMsg
        }
        if (status == "timeout") {
          console.log("Servidor ocupado", "Intente de nuevo");
        } else {
          console.log("Ocurrió un error", err);
        }
        mensajes("error", 10000, errorMsg || mensajeHTTP(response.resultado), null);
      },
    });
  } catch (error) {
     console.error("Excepcion atrapada en enviaAjax:", error);
     if (!response) {
       response = { resultado: error.status || 500, mensaje: "Fallo en la comunicación" };
     }
  }
  return response;
}

export function registrarEntrada() {
  var peticion = new FormData();
  peticion.append('peticion', 'entrada');
  enviaAjax(peticion);
}