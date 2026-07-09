import * as MensajeriaHelper from "./MensajeriaHelper.js";

export async function BuscarValor(id_select, valor, opcion) {
  if (!$(id_select).length) {
    console.error("El selector " + id_select + " no existe");
    return false;
  }

  const $select = $(id_select);

  if (opcion === 'text') {
    let bool = false;

    $select.find('option').each(function () {
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
      return false;
    }

  } else if (opcion === 'value') {
    const $option = $select.find(`option[value="${valor}"]`);
    
    if ($option.length) {
      $select.val(valor).trigger('change');
      return true;
    } else {
      console.error(`El valor ${valor} no se encuentra en el campo select.`);
      return false;
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
    $(span).removeClass("valid-feedback invalid-feedback");
    $(span).text("");
  } else {
    $(input).addClass("is-invalid");
    $(span).addClass("invalid-tooltip");
    $(span).text(mensaje);
  }
}

export function RenderizarSelect(etiqueta, arreglo, mensajes) {
    etiqueta.empty();
    if (Array.isArray(arreglo) && arreglo.length > 0) {
        // Agregar opción placeholder no seleccionable
        const placeholder = new Option(mensajes, "default");
        $(placeholder).prop('disabled', true).prop('selected', true).attr('hidden', true);
        etiqueta.append(placeholder);
        arreglo.forEach(item => {
            etiqueta.append(
                new Option(item.nombre, item.valor)
            );
        });
    } else {
        const noHay = new Option('Sin datos', "default");
        $(noHay).prop('disabled', true).prop('selected', true);
        etiqueta.append(noHay);
    }
}