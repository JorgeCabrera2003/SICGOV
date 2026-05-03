export function GenerarMensaje(icono, tiempo, titulo, mensaje) {
    Swal.fire({
        icon: icono,
        timer: tiempo,
        title: titulo,
        text: mensaje,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
    });
};

export function MensajeHTTP(codigo = null) {
    let mensaje = "";
    const CODIGOS = {
        '400': 'Datos del Formulario no Válidos',
        '403': 'No tienes permiso para realizar esta acción',
        '409': 'Registro duplicado',
        '500': 'Ups, intente de nuevo más tarde'
    }
    const DEFAULT = "Algo no a salido bien..."

    mensaje = CODIGOS[codigo] || DEFAULT

    return mensaje;
}
