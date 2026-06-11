import * as AjaxHelper from "./AjaxHelper.js";

export async function LlamarPermiso(modulo = "") {
  let response = [];
  let permisos = [];
  const endpoint = "?page=Permiso";
  const peticion = new FormData();
  peticion.append("modulo", modulo);
  peticion.append("peticion", "filtrar");
  try {
    response = await AjaxHelper.enviaAjax(peticion, endpoint);
    permisos = response.permisos;
  } catch (e) {
    permisos = [];
  }

  return permisos;
}