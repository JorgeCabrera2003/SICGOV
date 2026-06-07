import * as moduloSistema from "../Handlers/ModuloSistemaHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"

//MODULO DE MÓDULO DEL SISTEMA

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btnComprobarModulo").on("click", async function () {
  let respuesta = null;
  respuesta = await moduloSistema.EnviarFormulario("Comprobar");

  console.log(respuesta);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    Reestablecer(respuesta)
  };
});

async function Reestablecer(respuesta){
  let json = null;
  if(respuesta.verificacion == false){
    await confirmarAccion(`Se encontraron algunas incosistencias...`, "Se realizara la reparación de los módulos y permisos", "warning");
  }
  json = await moduloSistema.EnviarFormulario("Reestablecer");
  if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
   crearDataTable()
  };
}

async function crearDataTable(controlador = "") {
  const peticion = new FormData();
  let json = null;
  let arreglo = [];
  peticion.append("peticion", "consultar");

  try {
    json = await AjaxHelper.enviaAjax(peticion);
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if (Array.isArray(arreglo)) {

    moduloSistema.DataTablePrincipal(arreglo);

  } else {
    console.log("falso");
  }
}
