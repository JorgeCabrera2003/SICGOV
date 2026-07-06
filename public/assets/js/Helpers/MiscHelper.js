import { mensajes } from './UIHelper.js';

export function generarCodigoAleatorio(longitud = 8) {
  const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let resultado = '';
  for (let i = 0; i < longitud; i++) {
    resultado += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
  }
  return resultado;
}

export function copiarAlPortapapeles(texto) {
  navigator.clipboard.writeText(texto).then(() => {
    mensajes('success', 2000, 'Copiado', 'Texto copiado al portapapeles');
  }).catch(err => {
    console.error('Error al copiar: ', err);
    mensajes('error', 3000, 'Error', 'No se pudo copiar al portapapeles');
  });
}

export function descargarArchivo(contenido, nombreArchivo, tipo = 'text/plain') {
  const blob = new Blob([contenido], { type: tipo });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = nombreArchivo;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

export function obtenerParametrosURL() {
  const params = new URLSearchParams(window.location.search);
  const resultado = {};
  for (const [key, value] of params) {
    resultado[key] = value;
  }
  return resultado;
}

export function establecerParametrosURL(parametros) {
  const url = new URL(window.location);
  Object.keys(parametros).forEach(key => {
    url.searchParams.set(key, parametros[key]);
  });
  window.history.replaceState({}, '', url);
}

export function debounce(func, wait, immediate) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      timeout = null;
      if (!immediate) func(...args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func(...args);
  };
}

export function throttle(func, limit) {
  let inThrottle;
  return function (...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}
