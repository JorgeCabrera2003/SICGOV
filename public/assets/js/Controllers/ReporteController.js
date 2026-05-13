/**
 * CONTROLADOR: Reportes - SICGOV
 * Maneja la interacción del panel de reportes y configuración de parámetros.
 */

document.addEventListener('DOMContentLoaded', function() {
    const btns = document.querySelectorAll('.btn-config-report');
    const modalElement = document.getElementById('modalConfigReporte');
    
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    const inputTipo = document.getElementById('reportTipo');
    const tituloModal = document.getElementById('tituloConfigModal');

    // Manejar clics en los botones de reporte para abrir configuración
    btns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.getAttribute('data-tipo');
            const titulo = this.closest('.card-body').querySelector('h5').innerText;
            
            inputTipo.value = tipo;
            if (tituloModal) {
                tituloModal.innerText = 'Configurar Reporte: ' + titulo;
            }
            
            modal.show();
        });
    });
});
