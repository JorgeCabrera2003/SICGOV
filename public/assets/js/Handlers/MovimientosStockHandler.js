import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js"

//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

export async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Movimiento");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let json = { resultado: 0 };

  peticion.append("modulo", "EntradaInsumo");

  //Registrar y Modificar
  if (operacion == "suministrar") {

    console.log(Validarenvio())

    if (Validarenvio()) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion(`Se va a suministrar un insumo`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', "suministrar");
        peticion.append('id_entrada', input.proveedor.val());
        peticion.append('stock', input.stock.val());
        peticion.append('id_unidad', input.unidad_medida.val());
        peticion.append('id_insumo', input.insumo.prop('dataset').insumo);
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "suministrar_lote") {

    if (ValidadorHelper.ValidarCampo("ID", input.id_insumo, span.id_insumo)) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion("Se eliminará un Movimiento", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_insumo', input.id_insumo.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Movimiento no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await AjaxHelper.enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      MensajeriaHelper.GenerarMensaje(json.icon, 10000, json.mensaje, null);
    }
    modal.boton.prop('disabled', false);
  }

  if (!confirmacion) {
    modal.boton.prop('disabled', false);
  }

  input = null;
  modal = null;
  return json;
}

//Manejo de envio de datos desde el modal
export async function EnviarFormulario(btn_string) {
  let accion = null;
  let respuesta = null;
  const MANEJADOR = {
    'Recargar': 'recargar'
  }
  const DEFAULT = null

  accion = MANEJADOR[btn_string] || DEFAULT

  if (accion != null) {
    respuesta = await EnviarDatos(accion)
  } else {
    respuesta = { resultado: 0 }
    MensajeriaHelper.GenerarMensaje("danger", 10000, "Error, acción no válida", "")
  }
  return respuesta;
};

export function IniciarTablas() {
  DataTableEntradas();
}

export async function DataTableEntradas() {

  let json = null;
  let datos = new FormData();
  const endpoint = "?page=Insumo";
  const modulo = "Movimiento";
  let arreglo = [];
  datos.append("modulo", modulo);
  datos.append("peticion", "historialEntradas");

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      arreglo = json.datos;
    };

  } catch (error) {
    console.log(error);
    arreglo = [];
  }

  if ($.fn.DataTable.isDataTable('#tablaEntradas')) {
    $('#tablaEntradas').DataTable().destroy();
  }

  $('#tablaEntradas').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'fecha' },
      { data: 'proveedor' },
      {
        data: null,
        render: function (row) {
          let cantidad = ValidadorHelper.FormatearNumeroSinCeros(row.cantidad);
          let texto = cantidad+""+ row.abreviatura
          return texto;
        }
      },
      { data: 'descripcion' },
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}

export async function DataTableSalidas(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaEntradas')) {
    $('#tablaEntradas').DataTable().destroy();
  }

  $('#tablaEntradas').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'fecha' },
      { data: 'cantidad' },
      { data: 'proveedor' },
      { data: 'descripcion' },
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}

export async function EditarFormMovimiento(datos) {
  LimpiarFormulario();
  console.log(datos);
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Movimiento")

  input.insumo.val(datos.nombre_insumo).prop("disabled", true);
  input.insumo.prop('dataset').insumo = datos.id_insumo;
  input.stock.val("").prop("disabled", false);


  await CrearSelectProveedores(datos.id_insumo);
  await CrearSelectUnidadMedida(datos.id_unidad_medida);

  EditarModal("suministrar");
};
