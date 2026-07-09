import * as MensajeriaHelper from "./MensajeriaHelper.js";

export function FormatearDecimalMedida(valor) {
        let valor = $(etiqueta).val();
    let cantidad_decimales = 0,

    if(formato == "medida"){
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
    
    let conPunto = numeros.replace(',', '.');
    let numero = parseFloat(conPunto);
    
    if (isNaN(numero)) {
        return '';
    }
    

    let resultado = numero.toFixed(cantidad_decimales);
    let partes = resultado.split('.');
    let parteEntera = partes[0];
    let parteDecimal = partes[1];
    
    parteEntera = parseInt(parteEntera, 10).toLocaleString('es-ES');
    
    let final = parteEntera + '.' + parteDecimal;

    $(etiqueta).val(final);
    
    return final;
};
