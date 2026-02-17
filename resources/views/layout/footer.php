</main> <!-- Cierra page-content -->
</div> <!-- Cierra main-content -->

<!-- Script para inicializar tooltips y otros componentes -->
<script>
    $(document).ready(function() {
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        console.log('Footer cargado correctamente');
    });
</script>

<script>
    // Quitar la pantalla de carga
    (function(){
        function setPageReady(){
            try{
                document.documentElement.classList.add('page-ready');
            }catch(e){}
        }

        if (document.readyState === 'complete'){
            setPageReady();
        } else {
            window.addEventListener('load', setPageReady);
            document.addEventListener('DOMContentLoaded', setPageReady);
            window.addEventListener('pageshow', setPageReady);
        }

        setTimeout(function(){
            if (!document.documentElement.classList.contains('page-ready')){
                setPageReady();
            }
        }, 5000);
    })();
</script>

<!-- Footer -->
<footer id="footer" class="footer bottom">
    <div class="copyright">
        &copy; Copyright <strong><span>GOOD VIBES</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
        Designed by <a href="#">Tu Equipo</a>
    </div>
</footer>