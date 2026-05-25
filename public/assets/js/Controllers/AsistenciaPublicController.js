import * as AjaxHelper from "../Helpers/AjaxHelper.js";

const ENDPOINT = BASE_URL + '?page=asistencia-publica';
const SELECTOR = {
    tipoDoc: '#tipo_doc',
    cedula: '#cedula_empleado',
    btnVerify: '#btnVerifyEmployee',
    tipoMarcacion: '#tipo_marcacion',
    observacion: '#observacion',
    btnRegister: '#btnRegisterAttendance',
    employeeInfo: '#employeeInfo',
    employeeName: '#employeeName',
    employeeRole: '#employeeRole',
};

let empleadoVerificado = false;
let empleadoDatos = null;

$(document).ready(function () {
    $(SELECTOR.btnVerify).on('click', verificarEmpleado);
    $(SELECTOR.btnRegister).on('click', registrarAsistenciaPublica);
    $(SELECTOR.tipoDoc).on('change', limpiarVerificacion);
    $(SELECTOR.cedula).on('input', limpiarVerificacion);
});

function limpiarVerificacion() {
    empleadoVerificado = false;
    empleadoDatos = null;
    $(SELECTOR.employeeInfo).addClass('d-none');
    $(SELECTOR.employeeName).text('');
    $(SELECTOR.tipoMarcacion).prop('disabled', true).val('default');
    $(SELECTOR.btnRegister).prop('disabled', true);
    SistemaValidacion.limpiarEstilosCampo($(SELECTOR.tipoDoc));
    SistemaValidacion.limpiarEstilosCampo($(SELECTOR.cedula));
}

function validarCedula() {
    const valor = $(SELECTOR.cedula).val().trim();
    const valido = /^\d{7,9}$/.test(valor);
    if (!valido) {
        SistemaValidacion.aplicarEstilos($(SELECTOR.cedula), false, 'La cédula debe contener entre 7 y 9 dígitos.');
    } else {
        SistemaValidacion.aplicarEstilos($(SELECTOR.cedula), true);
    }
    return valido;
}

function validarTipoDoc() {
    const valor = $(SELECTOR.tipoDoc).val();
    const valido = valor && valor !== 'default';
    if (!valido) {
        SistemaValidacion.aplicarEstilos($(SELECTOR.tipoDoc), false, 'Selecciona el tipo de documento.');
    } else {
        SistemaValidacion.aplicarEstilos($(SELECTOR.tipoDoc), true);
    }
    return valido;
}

function validarTipoMarcacion() {
    const valor = $(SELECTOR.tipoMarcacion).val();
    const valido = valor && valor !== 'default';
    if (!valido) {
        SistemaValidacion.aplicarEstilos($(SELECTOR.tipoMarcacion), false, 'Selecciona el tipo de marcación.');
    } else {
        SistemaValidacion.aplicarEstilos($(SELECTOR.tipoMarcacion), true);
    }
    return valido;
}

async function verificarEmpleado() {
    const tipoDoc = $(SELECTOR.tipoDoc).val();
    const cedula = $(SELECTOR.cedula).val().trim();

    if (!validarTipoDoc() || !validarCedula()) {
        mensajes('error', 5000, 'Verifique los datos antes de buscar al empleado.');
        return;
    }

    const peticion = new FormData();
    peticion.append('peticion', 'verificar_empleado');
    peticion.append('tipo_doc', tipoDoc);
    peticion.append('cedula_empleado', cedula);

    const json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);

    if (json && json.resultado === 200 && json.datos) {
        empleadoVerificado = true;
        empleadoDatos = json.datos;
        const nombreEmpleado = empleadoDatos.nombre || empleadoDatos.nombre_personal || empleadoDatos.cedula_personal || 'Empleado';
        const apellidoEmpleado = empleadoDatos.apellido || empleadoDatos.apellido_personal || '';
        $(SELECTOR.employeeName).text(`${nombreEmpleado}${apellidoEmpleado ? ' ' + apellidoEmpleado : ''}`);
        $(SELECTOR.employeeInfo).removeClass('d-none');
        $(SELECTOR.tipoMarcacion).prop('disabled', false);
        $(SELECTOR.btnRegister).prop('disabled', false);
    } else {
        empleadoVerificado = false;
        empleadoDatos = null;
        $(SELECTOR.employeeInfo).addClass('d-none');
        $(SELECTOR.tipoMarcacion).prop('disabled', true).val('default');
        $(SELECTOR.btnRegister).prop('disabled', true);
        mensajes('error', 5000, json?.mensaje || 'No se encontró el empleado.');
    }
}

async function registrarAsistenciaPublica() {
    if (!empleadoVerificado) {
        mensajes('warning', 5000, 'Primero verifica que la cédula pertenece a un empleado.');
        return;
    }

    if (!validarTipoMarcacion()) {
        mensajes('error', 5000, 'Selecciona el tipo de marcación.');
        return;
    }

    const peticion = new FormData();
    peticion.append('peticion', 'registrar');
    peticion.append('tipo_doc', $(SELECTOR.tipoDoc).val());
    peticion.append('cedula_empleado', $(SELECTOR.cedula).val().trim());
    peticion.append('tipo_marcacion', $(SELECTOR.tipoMarcacion).val());
    peticion.append('observacion', $(SELECTOR.observacion).val().trim());

    const json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);

    if (json && json.resultado === 200) {
        mensajes('success', 5000, json.mensaje || 'Asistencia registrada correctamente.');
        limpiarForm();
    } else {
        mensajes('error', 5000, json?.mensaje || 'No se pudo registrar la asistencia.');
    }
}

function limpiarForm() {
    $(SELECTOR.tipoDoc).val('default');
    $(SELECTOR.cedula).val('');
    $(SELECTOR.tipoMarcacion).val('default').prop('disabled', true);
    $(SELECTOR.observacion).val('');
    $(SELECTOR.btnRegister).prop('disabled', true);
    $(SELECTOR.employeeInfo).addClass('d-none');
    $(SELECTOR.employeeName).text('');
    SistemaValidacion.limpiarEstilosCampo($(SELECTOR.tipoDoc));
    SistemaValidacion.limpiarEstilosCampo($(SELECTOR.cedula));
    SistemaValidacion.limpiarEstilosCampo($(SELECTOR.tipoMarcacion));
    empleadoVerificado = false;
    empleadoDatos = null;
}
