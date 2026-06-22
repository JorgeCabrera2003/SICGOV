/**
 * BackupController.js — Módulo JS para el Centro de Respaldos (SICGOV)
 */

'use strict';

const BackupManager = (() => {

    const BASE_URL = window.BASE_URL ?? '/';
    const ENDPOINT = `${BASE_URL}?page=Backup&type=backups`;

    let modalRespaldo     = null;
    let modalProgramacion = null;

    async function postJSON(peticion, extraData = {}) {
        const payload = new FormData();
        payload.append('peticion', peticion);
        Object.entries(extraData).forEach(([k, v]) => payload.append(k, v));
        const resp = await fetch(ENDPOINT, { method: 'POST', body: payload });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        return resp.json();
    }

    function buildCron(frecuencia, hora, minuto, diaSemana, diaMes) {
        hora      = parseInt(hora)      || 0;
        minuto    = parseInt(minuto)    || 0;
        diaSemana = parseInt(diaSemana) || 0;
        diaMes    = parseInt(diaMes)    || 1;

        switch (frecuencia) {
            case 'diario':  return `${minuto} ${hora} * * *`;
            case 'semanal': return `${minuto} ${hora} * * ${diaSemana}`;
            case 'mensual': return `${minuto} ${hora} ${diaMes} * *`;
            default:        return `${minuto} ${hora} * * *`;
        }
    }

    function format12h(h24, min) {
        h24 = parseInt(h24) || 0;
        const ampm = h24 >= 12 ? 'PM' : 'AM';
        let h12 = h24 % 12;
        if (h12 === 0) h12 = 12;
        return `${String(h12).padStart(2, '0')}:${String(min).padStart(2, '0')} ${ampm}`;
    }

    function to24h(h12, ampm) {
        h12 = parseInt(h12) || 0;
        if (ampm === 'PM' && h12 !== 12) return h12 + 12;
        if (ampm === 'AM' && h12 === 12) return 0;
        return h12;
    }

    const DIAS = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];

    function descProgramacion(cfg) {
        const horaFormateada = format12h(cfg.hora ?? 3, cfg.minuto ?? 0);

        switch (cfg.frecuencia) {
            case 'diario':
                return `<strong>Diario</strong> a las ${horaFormateada}`;
            case 'semanal': {
                const dia = DIAS[cfg.dia_semana ?? 0];
                return `<strong>Semanal</strong> · cada ${dia} a las ${horaFormateada}`;
            }
            case 'mensual':
                return `<strong>Mensual</strong> · día ${cfg.dia_mes ?? 1} a las ${horaFormateada}`;
            default:
                return 'Programación no configurada';
        }
    }

    function initModalRespaldo() {
        const btnGen  = document.getElementById('btn-generar-respaldo');
        const select  = document.getElementById('select-database');
        const spinner = document.getElementById('backup-spinner');
        const modalEl = document.getElementById('modalRespaldo');
        if (!btnGen || !modalEl) return;

        modalRespaldo = new bootstrap.Modal(modalEl);
        modalEl.addEventListener('show.bs.modal', () => { select.value = ''; select.classList.remove('is-invalid'); });

        btnGen.addEventListener('click', async () => {
            const database = select.value;
            if (!database) { select.classList.add('is-invalid'); select.focus(); return; }
            select.classList.remove('is-invalid');

            btnGen.disabled = true;
            spinner?.classList.remove('d-none');

            try {
                const data = await postJSON('generar-respaldo', { database });
                modalRespaldo.hide();

                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Respaldo generado!', text: data.mensaje,
                        confirmButtonText: '<i class="bi bi-download me-1"></i> Descargar ahora',
                        confirmButtonColor: '#0d6efd', showCancelButton: true, cancelButtonText: 'Cerrar',
                    }).then(r => { if (r.isConfirmed && data.download_url) window.location.href = data.download_url; listarRespaldos(); });
                } else {
                    Swal.fire({ icon: data.icon ?? 'error', title: 'Error', text: data.mensaje, confirmButtonColor: '#dc3545' });
                }
            } catch {
                modalRespaldo.hide();
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo comunicar con el servidor.' });
            } finally {
                btnGen.disabled = false;
                spinner?.classList.add('d-none');
            }
        });
    }

    function initModalProgramacion() {
        const modalEl       = document.getElementById('modalProgramacion');
        const btnGuardar    = document.getElementById('btn-guardar-programacion');
        const progSpinner   = document.getElementById('prog-spinner');
        const selFrecuencia = document.getElementById('prog-frecuencia');
        const selDiaSemana  = document.getElementById('prog-dia-semana');
        const inpDiaMes     = document.getElementById('prog-dia-mes');
        const inpHora       = document.getElementById('prog-hora');
        const inpMinuto     = document.getElementById('prog-minuto');
        const selAmpm       = document.getElementById('prog-ampm');
        const cronPreview   = document.getElementById('cron-preview');
        const grupoDiaSem   = document.getElementById('grupo-dia-semana');
        const grupoDiaMes   = document.getElementById('grupo-dia-mes');

        if (!modalEl) return;

        modalProgramacion = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('show.bs.modal', async () => {
            try {
                const data = await postJSON('get-config-backup');
                if (data.success && data.config) {
                    const c = data.config;
                    selFrecuencia.value = c.frecuencia  ?? 'semanal';
                    selDiaSemana.value  = c.dia_semana  ?? 0;
                    inpDiaMes.value     = c.dia_mes     ?? 1;
                    inpMinuto.value     = c.minuto      ?? 0;

                    const h24 = parseInt(c.hora ?? 3);
                    selAmpm.value = h24 >= 12 ? 'PM' : 'AM';
                    let h12 = h24 % 12;
                    if (h12 === 0) h12 = 12;
                    inpHora.value = h12;
                }
            } catch { /* usar valores por defecto */ }
            actualizarVistaCampos();
            actualizarCronPreview();
        });

        function actualizarVistaCampos() {
            const f = selFrecuencia.value;
            grupoDiaSem.classList.toggle('d-none', f !== 'semanal');
            grupoDiaMes.classList.toggle('d-none', f !== 'mensual');
        }

        function actualizarCronPreview() {
            if (!cronPreview) return;
            const h24 = to24h(inpHora.value, selAmpm.value);
            cronPreview.textContent = buildCron(
                selFrecuencia.value, h24, inpMinuto.value,
                selDiaSemana.value, inpDiaMes.value
            );
        }

        [selFrecuencia, selDiaSemana, inpDiaMes, inpHora, inpMinuto, selAmpm].forEach(el => {
            el?.addEventListener('change', () => { actualizarVistaCampos(); actualizarCronPreview(); });
            el?.addEventListener('input',  actualizarCronPreview);
        });

        btnGuardar?.addEventListener('click', async () => {
            btnGuardar.disabled = true;
            progSpinner?.classList.remove('d-none');

            try {
                const h24 = to24h(inpHora.value, selAmpm.value);
                const data = await postJSON('guardar-config-backup', {
                    frecuencia: selFrecuencia.value,
                    hora:       h24,
                    minuto:     inpMinuto.value,
                    dia_semana: selDiaSemana.value,
                    dia_mes:    inpDiaMes.value,
                });

                modalProgramacion.hide();

                Swal.fire({
                    icon:  data.success ? 'success' : 'error',
                    title: data.success ? '¡Configuración guardada!' : 'Error',
                    html:  data.success
                        ? `<span class="text-success fw-bold">Programación actualizada correctamente.</span><br><br><small class="text-muted">El servidor ha sido configurado y ejecutará los respaldos de forma automática.</small>`
                        : data.mensaje,
                    confirmButtonColor: data.success ? '#198754' : '#dc3545',
                });

                if (data.success) actualizarInfoBar(data.cron_expr, selFrecuencia.value, h24, inpMinuto.value, selDiaSemana.value, inpDiaMes.value);

            } catch {
                modalProgramacion.hide();
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo guardar la configuración.' });
            } finally {
                btnGuardar.disabled = false;
                progSpinner?.classList.add('d-none');
            }
        });
    }

    function actualizarInfoBar(cronExpr, frecuencia, hora, minuto, diaSemana, diaMes) {
        const el = document.getElementById('info-programacion');
        if (!el) return;
        const cfg = { frecuencia, hora: parseInt(hora), minuto: parseInt(minuto), dia_semana: parseInt(diaSemana), dia_mes: parseInt(diaMes) };
        el.innerHTML = `<strong>Respaldo Automático:</strong> <span class="text-success">${descProgramacion(cfg)}</span>`;
    }

    async function cargarInfoBar() {
        try {
            const data = await postJSON('get-config-backup');
            if (data.success && data.config) {
                const c = data.config;
                const cronExpr = buildCron(c.frecuencia, c.hora, c.minuto, c.dia_semana, c.dia_mes);
                actualizarInfoBar(cronExpr, c.frecuencia, c.hora, c.minuto, c.dia_semana, c.dia_mes);
            }
        } catch {
            const el = document.getElementById('info-programacion');
            if (el) el.textContent = 'No se pudo cargar la configuración de programación.';
        }
    }

    async function listarRespaldos() {
        const tableId = '#tabla-backups';
        const tbody   = document.querySelector(`${tableId} tbody`);
        const counter = document.getElementById('backup-count-badge');
        if (!tbody) return;

        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }

        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-5">
            <div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando…</td></tr>`;

        try {
            const data = await postJSON('listar-respaldos');
            if (!data.success) throw new Error(data.mensaje);
            const backups = data.backups ?? [];
            if (counter) counter.textContent = backups.length;

            if (!backups.length) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                    No hay respaldos almacenados aún.</td></tr>`;
                return;
            }

            tbody.innerHTML = backups.map(b => `
                <tr>
                    <td><i class="bi bi-file-earmark-zip text-success me-2"></i><span class="fw-semibold">${escapeHtml(b.filename)}</span></td>
                    <td class="text-muted" data-sort="${b.timestamp}">${escapeHtml(b.fecha)}</td>
                    <td data-sort="${b.size_raw}"><span class="badge bg-light text-dark border">${escapeHtml(b.size)}</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle fw-medium shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical text-secondary"></i> Acciones
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 140px;">
                                <li>
                                    <a class="dropdown-item py-2" href="${ENDPOINT}&action=descargar-respaldo&file=${encodeURIComponent(b.filename)}">
                                        <i class="bi bi-download text-primary me-2"></i> Descargar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider m-0"></li>
                                <li>
                                    <button class="dropdown-item py-2 btn-restaurar-backup" type="button" data-filename="${escapeHtml(b.filename)}">
                                        <i class="bi bi-arrow-counterclockwise text-warning me-2"></i> Restaurar
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item py-2 text-danger btn-eliminar-backup" type="button" data-filename="${escapeHtml(b.filename)}">
                                        <i class="bi bi-trash3 text-danger me-2"></i> Eliminar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>`).join('');

            tbody.querySelectorAll('.btn-restaurar-backup').forEach(btn =>
                btn.addEventListener('click', () => restaurarRespaldo(btn.dataset.filename)));

            tbody.querySelectorAll('.btn-eliminar-backup').forEach(btn =>
                btn.addEventListener('click', () => eliminarRespaldo(btn.dataset.filename)));

            $(tableId).DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                pageLength: 5,
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "Todos"]],
                order: [[1, 'desc']], // Ordenar por fecha desc
                columnDefs: [
                    { orderable: false, targets: 3 } // Deshabilitar orden en Acciones
                ]
            });

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle me-1"></i>Error al cargar los respaldos.</td></tr>`;
            console.error('[BackupManager]', err);
        }
    }

    async function eliminarRespaldo(filename) {
        const { isConfirmed } = await Swal.fire({
            icon: 'warning', title: '¿Eliminar respaldo?',
            html: `Acción <strong>irreversible</strong>.<br><code>${escapeHtml(filename)}</code>`,
            showCancelButton: true, confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d'
        });
        if (!isConfirmed) return;

        try {
            const data = await postJSON('eliminar-respaldo', { filename });
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Eliminado' : 'Error',
                text: data.mensaje,
                confirmButtonColor: data.success ? '#198754' : '#dc3545',
                timer: data.success ? 2500 : undefined, timerProgressBar: data.success
            });
            if (data.success) listarRespaldos();
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo eliminar.' });
        }
    }

    async function restaurarRespaldo(filename) {
        const { isConfirmed } = await Swal.fire({
            icon: 'warning',
            title: '¡PELIGRO! ¿Restaurar base de datos?',
            html: `Esta acción <strong>sobrescribirá los datos actuales</strong> de la base de datos con los datos del respaldo:<br><br><code class="text-danger fw-bold fs-6">${escapeHtml(filename)}</code><br><br>Se perderán todos los cambios realizados después de la fecha de este respaldo. ¿Está completamente seguro?`,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-exclamation-triangle me-1"></i> Sí, entiendo el riesgo y quiero restaurar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        });

        if (!isConfirmed) return;

        Swal.fire({
            title: 'Restaurando...',
            text: 'Por favor no cierre ni recargue esta ventana.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const data = await postJSON('restaurar-respaldo', { filename });
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? '¡Restauración Completa!' : 'Error',
                text: data.mensaje,
                confirmButtonColor: data.success ? '#198754' : '#dc3545',
            }).then(() => {
                if (data.success) {

                    window.location.reload();
                }
            });
        } catch {
            Swal.fire({ icon: 'error', title: 'Error crítico', text: 'No se pudo completar la restauración debido a un error de conexión.' });
        }
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function init() {
        initModalRespaldo();
        initModalProgramacion();
        listarRespaldos();
        cargarInfoBar();
        document.getElementById('btn-refresh-backups')
            ?.addEventListener('click', listarRespaldos);
    }

    return { init };

})();

document.addEventListener('DOMContentLoaded', () => BackupManager.init());
