    </div> <!-- Cierra content-wrapper -->

    <footer class="footer mt-auto py-3 bg-body-tertiary border-top">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-muted small">
                        © <?php echo date('Y'); ?> <strong>SICGOV</strong> - Sistema de Información Complementario Good Vibes
                    </span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="text-muted small">
                        Desarrollado por 
                        <a href="https://github.com/JorgeCabrera2003" class="text-decoration-none fw-semibold">J. Cabrera</a>, 
                        <a href="https://github.com/lz2712" class="text-decoration-none fw-semibold">L. Torrealba</a>, 
                        <a href="https://github.com/AbrahanMulder" class="text-decoration-none fw-semibold">A. Rodriguez</a>
                        <a href="#" class="text-decoration-none fw-semibold">M. Bokor</a>,
                        <a href="#" class="text-decoration-none fw-semibold">S. Coello</a>,
                    </span>
                </div>
            </div>
        </div>
    </footer>

</main> <!-- Cierra main-content -->

<!-- Botón volver arriba -->
<button class="back-to-top" aria-label="Volver arriba">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- ===== SCRIPTS - ORDEN CORRECTO ===== -->

<!-- 1. jQuery SIEMPRE primero -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- 2. Bootstrap JS (depende de jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. DataTables (depende de jQuery) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- 4. Select2 (depende de jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- 5. SweetAlert2 (no depende de jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- 6. Chart.js (no depende de jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- 7. Scripts personalizados (dependen de jQuery) -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/utils.js"></script>
<?php if (isset($_SESSION['user'])): ?>
    <script type="module" src="<?php echo BASE_URL; ?>/assets/js/modulo_notificaciones.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

<!-- 8. Scripts dinámicos clásicos pasados desde el controlador -->
<?php if (!empty($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $scriptPath): ?>
        <script src="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 9. Script específico de la página (solo si NO se usan ES Modules para esta página) -->
<?php if (empty($extra_js_modules) && isset($page) && file_exists(__DIR__ . "/../../../public/assets/js/{$page}.js")): ?>
<script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $page; ?>.js"></script>
<?php endif; ?>

<!-- 10. ES Modules — scripts con import/export (requieren type="module") -->
<?php if (!empty($extra_js_modules) && is_array($extra_js_modules)): ?>
    <?php foreach ($extra_js_modules as $modulePath): ?>
        <script type="module" src="<?php echo htmlspecialchars($modulePath, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($extra_js_inline_module)): ?>
    <script type="module">
        <?php echo $extra_js_inline_module; ?>
    </script>
<?php endif; ?>

<!-- Script para quitar el loader -->
<script>
    (function() {
        function removeLoader() {
            document.documentElement.classList.add('page-ready');
        }
        if (document.readyState === 'complete') {
            removeLoader();
        } else {
            window.addEventListener('load', removeLoader);
            document.addEventListener('DOMContentLoaded', removeLoader);
        }
        setTimeout(removeLoader, 3000);
    })();
</script>

</body>
</html>