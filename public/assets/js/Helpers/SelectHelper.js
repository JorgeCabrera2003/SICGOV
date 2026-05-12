import * as MensajeriaHelper from "./MensajeriaHelper.js";

export async function BuscarValor(id_select, valor, opcion) {
  if (!$(id_select).length) {
    console.error("El selector " + id_select + " no existe");
    return false;
  }

  if (opcion === 'text') {
    let bool = false;

    $(`${id_select} option`).each(function () {
      if ($(this).text().trim() === valor.trim()) {
        $(this).prop('selected', true);
        $(id_select).trigger('change');
        bool = true;
        return false; // break the loop
      }
    })

    if (bool) {
      return true;
    } else {
      console.error("El valor '" + valor + "' no se encuentra en el campo select.")
    }

  } else if (opcion === 'value') {
    if ($(`${id_select} option[value="${valor}"]`).length > 0) {
      $(`${id_select}`).val(`${valor}`).trigger('change');
      return true;
    } else {
      console.error("El valor " + valor + " no se encuentra en el campo select.");
    }

  } else {
    console.error("Opcion no Válida: " + opcion + "")
  }
  return false;
}

export function FeedbackSelect(input, span, mensaje, estado) {
  $(input).removeClass("is-valid is-invalid");
  $(span).removeClass("valid-feedback invalid-feedback");
  
  if (estado === 1) {
    $(input).addClass("is-valid");
    $(span).text();
  } else {
    $(input).addClass("is-invalid");
    $(span).addClass("invalid-toltip");
    $(span).text(mensaje);
  }
}