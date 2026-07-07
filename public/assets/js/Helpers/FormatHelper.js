export function formatearTelefono($input) {
  if (!$input.length) return;
  let numeros = $input.val().replace(/\D/g, '');
  numeros = numeros.substring(0, 10);
  if (numeros.length >= 6) {
    $input.val('(' + numeros.substring(0, 3) + ') ' + numeros.substring(3, 6) + '-' + numeros.substring(6));
  } else if (numeros.length >= 3) {
    $input.val('(' + numeros.substring(0, 3) + ') ' + numeros.substring(3));
  } else {
    $input.val(numeros);
  }
}

export function formatearTelefonoSimple($input) {
  if (!$input.length) return;
  let numeros = $input.val().replace(/\D/g, '');
  if (numeros.length > 4) {
    $input.val(numeros.substring(0, 4) + '-' + numeros.substring(4));
  } else {
    $input.val(numeros);
  }
}

export function capitalizarTexto(texto) {
  if (!texto || typeof texto !== 'string') return texto;
  return texto
    .toLowerCase()
    .split(/(\s+)/)
    .map(segmento => {
      if (/^\s+$/.test(segmento)) return segmento;
      if (segmento.length > 0) {
        return segmento.charAt(0).toUpperCase() + segmento.slice(1);
      }
      return segmento;
    })
    .join('');
}

export function formatearFecha(fecha, formato = 'dd/mm/yyyy') {
  if (!fecha) return '';
  const date = new Date(fecha);
  if (isNaN(date.getTime())) return fecha;
  const dia = date.getDate().toString().padStart(2, '0');
  const mes = (date.getMonth() + 1).toString().padStart(2, '0');
  const anio = date.getFullYear();
  switch (formato) {
    case 'dd/mm/yyyy': return `${dia}/${mes}/${anio}`;
    case 'yyyy-mm-dd': return `${anio}-${mes}-${dia}`;
    case 'mm/dd/yyyy': return `${mes}/${dia}/${anio}`;
    default: return `${dia}/${mes}/${anio}`;
  }
}
