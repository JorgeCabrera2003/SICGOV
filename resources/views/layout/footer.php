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
                        <a href="#" class="text-decoration-none fw-semibold">J. Cabrera</a>, 
                        <a href="#" class="text-decoration-none fw-semibold">L. Torrealba</a>, 
                        <a href="#" class="text-decoration-none fw-semibold">M. Bokor</a>,
                        <a href="#" class="text-decoration-none fw-semibold">S. Coello</a>,
                        <a href="#" class="text-decoration-none fw-semibold">A. Rodriguez</a>
                    </span>
                </div>
            </div>
        </div>
    </footer>

</main>

<!-- volver arriba -->
<button class="back-to-top" aria-label="Volver arriba">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Scripts base -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Chart.js (si se necesita) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Scripts personalizados -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/utils.js"></script>

<!-- Script de la página -->
<?php if (isset($page) && file_exists(__DIR__ . "/../../public/assets/js/{$page}.js")): ?>
<script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $page; ?>.js"></script>
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