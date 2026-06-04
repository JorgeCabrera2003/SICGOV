import * as MensajeriaHelper from "./MensajeriaHelper.js";

export function ValidarCampo(patron, etiqueta, span) {
    let regex = null;
    let bool = false;
    etiqueta.removeClass("is-valid is-invalid");
    span.removeClass("valid-tooltip invalid-tooltip")

    const REGEX = {
        'Cedula': /^[0-9]{7,15}$/,
        'DocumentoLegal': /^[0-9]{7,12}$/,
        'FormatoDocumentoLegal': /^[VEJPGvejpg]{1}[-][0-9]{7,15}$/,
        'FormatoCedula': /^[VEJPGvejpg]{1}[-][0-9]{7,15}$/,
        'ID': /^[A-Z0-9]{3,5}[A-Z0-9]{3,5}[0-9]{8}[0-9]{0,6}[0-9]{0,6}$/,
        'NombrePersona': /^[a-z A-ZÁÉÍÓÚÜáéíóúüñÑçÇ]{3,65}$/,
        'NombreUsuario': /^[0-9a-zA-Z_]{4,20}$/,
        'NombreObjeto': /^[0-9 a-zA-ZÁÉÍÓÚÜáéíóúüñÑçÇ\-]{3,65}$/,
        'Telefono': /^[0-9]{4}[-][0-9]{7}$/,
        'Telefono-Segmento': /^[0-9]{7}$/,
        'Correo': /^[a-zA-Z0-9][a-zA-Z0-9._%+-]{1,63}@[a-zA-Z0-9][a-zA-Z0-9.-]{1,50}\.(com|es|mx|co\.uk|org|net)$/,
        'Titulo': /^[0-9a-zA-ZÁÉÍÓÚÜáéíóúüñÑçÇ\s\-.,()!?\"\'%:;]{3,150}$/,
        'Direccion': /^[0-9a-zA-ZÁÉÍÓÚÜáéíóúüñÑçÇ\s\-.,()!?\"\'%:;\/]{10,200}$/,
        'NumeroDecimal': /^[0-9]+[.]{1}[0-9]{2,8}$/
    };
    const DEFAULT = '';
    regex = REGEX[patron] || DEFAULT;

    try {
        if (etiqueta.val() != "") {
            if (regex.test(etiqueta.val())) {
                bool = true;
                etiqueta.addClass("is-valid");
                span.text("");
            } else {
                etiqueta.addClass("is-invalid");
                span.addClass("invalid-tooltip");
                span.text(MensajeriaHelper.DiccionarioValidacion(patron));
            }
        } else {
            etiqueta.removeClass("is-valid is-invalid");
            span.removeClass("valid-tooltip-tooltip invalid-tooltip");
            span.text("");
        }

    } catch (e) {
        console.error("Error en la validación: ", e)
        etiqueta.addClass("is-invalid");
        span.text("Error en el campo");
        span.addClass("invalid-feedback");
    }

    return bool
};

export function ValidarTecla(patron, etiqueta) {
    let regex = null;
    let bool = null;
    let caracter = null;
    let tecla = null;

    const REGEX = {
        'Cedula': /^[0-9]*$/,
        'NombrePersona': /^[A-Za-zÁÉÍÓÚÜáéíóú\b\s\u00f1\u00d1\u00E0-\u00FC]*$/,
        'NombreUsuario': /^[0-9a-zA-Z_]*$/,
        'Objeto': /^[0-9a-zA-ZÁÉÍÓÚÜáéíóúüñÑçÇ\s\-]*$/,
        'NombreObjeto': /^[0-9a-zA-ZÁÉÍÓÚÜáéíóúüñÑçÇ\s\-.,()\-]*$/,
        'Telefono': /^[0-9]*$/,
        'Correo': /^[a-zA-Z0-9._%+-@]*$/,
        'Titulo': /^[0-9a-zA-ZÁÉÍÓÚÜáéíóúñÑçÇ\s\-.,()!?\"\'%:;]*$/,
        'Direccion': /^[0-9a-zA-ZÁÉÍÓÚÜáéíóúñÑçÇ\s\-.,()!?\"\'%:;\/]*$/,
        'NumeroDecimal': /^[0-9,]*$/
    };
    const DEFAULT = '';

    try {
        regex = REGEX[patron] || DEFAULT;

        caracter = etiqueta.keyCode;
        tecla = String.fromCharCode(caracter);

        bool = regex.test(tecla);

        if (!bool) {
            etiqueta.preventDefault();
        }

    } catch (e) {
        etiqueta.preventDefault();
        console.error("Error en la validación: ", e)
    }
};

export function ValidarCodigoTelefono(etiqueta, span) {
    let bool = null;
    let caracter = null;

    const CODIGOS = {
        '0412': true,
        '0422': true,
        '0414': true,
        '0424': true,
        '0416': true,
        '0426': true
    };
    const DEFAULT = false;

    try {
        bool = CODIGOS[etiqueta.val()] || DEFAULT;

        if (bool) {
            bool = true;
            etiqueta.addClass("is-valid");
            span.text("");
        } else {
            etiqueta.addClass("is-invalid");
            span.addClass("invalid-tooltip");
            span.text("Código de Teléfono no válido");
        }

    } catch (e) {
        bool = false;
        console.error("Error en la validación: ", e)
    }
    return bool;
};

export function AgregarGuion(patron, etiqueta) {
    let regex = null;
    bool = null;

    const REGEX = {
        'Cedula': /^[0-9]*$/,
        'Telefono': /^[0-9]*$/,
    };
    const DEFAULT = '';

    try {
        regex = REGEX[patron] || DEFAULT;

        caracter = etiqueta.keyCode;
        tecla = String.fromCharCode(caracter);

        bool = regex.test(tecla);

        if (!bool) {
            etiqueta.preventDefault();
        }

    } catch (e) {
        etiqueta.preventDefault();
        console.error("Error en la validación: ", e)
    }
};

export function FormatoNumeroDecimal(etiqueta, formato = null) {
    let valor = $(etiqueta).val();
    let cantidad_decimales = 0;

    if (formato == "medida"){
        cantidad_decimales = 3;
    } else {
        cantidad_decimales = 2;
    }
    
    if (valor === '' || valor === null || valor === undefined) {
        return '';
    }
    
    let numeros = valor
    
    if (numeros === '') {
        return '';
    }
    
    // Reemplazar coma por punto temporalmente para trabajar
    let conPunto = numeros.replace(',', '.');
    let numero = parseFloat(conPunto);
    
    if (isNaN(numero)) {
        return '';
    }
    
    // Asegurar 3 decimales
    let resultado = numero.toFixed(cantidad_decimales);
    
    // Separar parte entera y decimal
    let partes = resultado.split('.');
    let parteEntera = partes[0];
    let parteDecimal = partes[1];
    
    // Formatear parte entera con separador de miles
    parteEntera = parseInt(parteEntera, 10).toLocaleString('es-ES');
    
    let final = parteEntera + '.' + parteDecimal;

    $(etiqueta).val(final);
    
    return final;
}