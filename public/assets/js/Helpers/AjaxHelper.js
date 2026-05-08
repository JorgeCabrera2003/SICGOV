import * as mensajeriaHelper from "./MensajeriaHelper.js";

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
          response = {
            resultado: 204,
            mensaje: ''
          }
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
        mensajeriaHelper.GenerarMensaje("error", 10000, errorMsg || mensajeHTTP(response.resultado), null);
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