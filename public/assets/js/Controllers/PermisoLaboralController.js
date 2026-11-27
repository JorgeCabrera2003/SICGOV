import * as Handler from '../Handlers/PermisoLaboralHandler.js';
import * as AjaxHelper from '../Helpers/AjaxHelper.js';

let vistaPermisosActiva = 'mis';

$(document).ready(function(){
  Inicializar();
});

async function Inicializar(){
  await CargarPermisos();
  await Handler.CargarSelects();
  $('#btnMisPermisos').on('click', () => CambiarVistaPermisos('mis'));
  $('#btnTodosPermisos').on('click', () => CambiarVistaPermisos('todos'));
  $('#permiso-comprobante').on('change', function () {
    const file = this.files?.[0];
    if (!file) return;
    $('#permiso-comprobante-actual').hide();
    const reader = new FileReader();
    reader.onload = function (event) {
      $('#permiso-comprobante-preview-nuevo').attr('src', event.target.result);
      $('#permiso-comprobante-nuevo').show();
    };
    reader.readAsDataURL(file);
  });
  $('#btnNuevoPermiso').on('click', ()=>{ Handler.Limpiar(); Handler.EditarModal('registrar'); });
  $('#btn-ModalTipos').on('click', ()=>{ $('#modalTipoPermiso').modal('show'); });
  $(document).on('tipoPermisoActualizado', () => Handler.CargarSelects());

  $('#btnPermisoForm').on('click', async function(){
    const res = await Handler.EnviarFormulario($('#btnPermisoForm'));
    if (typeof res?.resultado === 'number' && res.resultado >= 200 && res.resultado <= 299) {
      $('#modalPermisoLaboral').modal('hide');
      await CargarPermisos();
    }
  });

  $('#tablaPermisoLaboral').on('click', '.btn-editar', async function(){
    const row = $('#tablaPermisoLaboral').DataTable().row($(this).closest('tr')).data();
    Handler.EditarForm(row, 'modificar');
  });

  $('#tablaPermisoLaboral').on('click', '.btn-eliminar', async function(){
    const row = $('#tablaPermisoLaboral').DataTable().row($(this).closest('tr')).data();
    Handler.EditarForm(row, 'eliminar');
  });

  $('#tablaPermisoLaboral').on('click', '.btn-aprobar', async function(){
    const row = $('#tablaPermisoLaboral').DataTable().row($(this).closest('tr')).data();
    const res = await Handler.ProcesarEstado(row, 'aprobar');
    if (typeof res?.resultado === 'number' && res.resultado >= 200 && res.resultado <= 299) {
      await CargarPermisos();
    }
  });

  $('#tablaPermisoLaboral').on('click', '.btn-rechazar', async function(){
    const row = $('#tablaPermisoLaboral').DataTable().row($(this).closest('tr')).data();
    const res = await Handler.ProcesarEstado(row, 'rechazar');
    if (typeof res?.resultado === 'number' && res.resultado >= 200 && res.resultado <= 299) {
      await CargarPermisos();
    }
  });
}

async function CargarPermisos(){
  const pet = new FormData();
  pet.append('peticion','consultar');
  pet.append('modulo','PermisoLaboral');
  pet.append('vista', vistaPermisosActiva);
  try{
    const json = await AjaxHelper.enviaAjax(pet, '?page=PermisoLaboral');
    if (json && Array.isArray(json.datos)) {
      Handler.DataTablePermisos(json.datos);
    }
  }catch(e){ console.error(e); }
}

function CambiarVistaPermisos(vista) {
  if (vista === 'todos' && !window.PERMISO_PUEDE_VER_TODOS) return;
  vistaPermisosActiva = vista;
  const verTodos = vista === 'todos';
  $('#btnMisPermisos')
    .toggleClass('btn-warning text-dark fw-semibold', !verTodos)
    .toggleClass('btn-outline-warning', verTodos);
  $('#btnTodosPermisos')
    .toggleClass('btn-warning text-dark fw-semibold', verTodos)
    .toggleClass('btn-outline-warning', !verTodos);
  CargarPermisos();
}
