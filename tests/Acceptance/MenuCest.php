<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Aceptación / Interfaz para el Módulo de Menú y Catálogo de Productos
 */
class MenuCest
{
    private string $cookieSession = '';

    public function _before(AcceptanceTester $I): void
    {
        if (empty($this->cookieSession)) {
            $ch = curl_init('http://localhost/SICGOV/public/?page=Login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'peticion' => 'sesion',
                'particle' => 'V',
                'CI' => '00000000',
                'password' => '1234',
                'g-recaptcha-response' => 'bypass-test'
            ]);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $res = curl_exec($ch);
            preg_match('/PHPSESSID=([^;]+)/', $res, $m);
            $this->cookieSession = $m[1] ?? '';
        }
    }

    /**
     * Asegura que la sesión esté activa, navegando a la página de login si es necesario.
     */
    private function autenticarSiEsNecesario(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=menu');
        $I->waitForElementVisible('body', 5);

        // Si la sesión no fue válida y redirigió a Login, autenticarse por formulario
        $urlActual = $I->grabFromCurrentUrl();
        if (str_contains($urlActual, 'Login')) {
            $I->fillField('input[name="CI"]', '00000000');
            $I->fillField('input[name="password"]', '1234');
            $I->click('button[name="peticion"][value="sesion"]');
            $I->waitForElementVisible('body', 5);
            $I->amOnPage('/?page=menu');
            $I->waitForElementVisible('body', 5);
        }
    }

    /**
     * Prueba la navegación al panel de administración del menú y apertura del modal.
     */
    public function probarPanelAdminMenu(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Menú');
        $I->makeScreenshot('20_menu_panel_admin');

        // Modal de Registro Producto
        $I->waitForElementVisible('#btnNuevoMenu', 5);
        $I->click('#btnNuevoMenu');
        $I->waitForElementVisible('#modalMenu', 5);
        $I->wait(1);
        $I->makeScreenshot('21_menu_modal_producto');

        // Cerrar modal
        $I->click('#modalMenu .btn-close');
        $I->waitForElementNotVisible('#modalMenu', 5);
    }





    // /**
    //  * 1. REGISTRAR: Flujo completo y automatizado para agregar un nuevo producto al menú.
    //  */
    // public function registrarProductoEnMenuConDatosValidos(AcceptanceTester $I): void
    // {
    //     $this->autenticarSiEsNecesario($I);

    //     $I->see('Menú');

    //     // Abrir el modal de nuevo producto
    //     $I->waitForElementVisible('#btnNuevoMenu', 5);
    //     $I->click('#btnNuevoMenu');
    //     $I->waitForElementVisible('#modalMenu', 5);

    //     // Generar datos únicos válidos (solo letras y espacios según la regla /[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g de menu.js)
    //     $sufijo = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4);
    //     $nombreProducto = 'Especial Bbq ' . ucfirst($sufijo);
    //     $precio = '9.50';
    //     $descripcion = 'Platillo preparado con ingredientes selectos de la casa.';

    //     $I->fillField('#nombre', $nombreProducto);
    //     $I->fillField('#precio', $precio);
    //     $I->fillField('#descripcion', $descripcion);

    //     // Seleccionar una categoría válida del catálogo
    //     $I->executeJS("
    //         var catSelect = document.getElementById('id_categoria');
    //         if (catSelect && catSelect.options.length > 1) {
    //             for (var i = 1; i < catSelect.options.length; i++) {
    //                 if (catSelect.options[i].value) {
    //                     catSelect.selectedIndex = i;
    //                     $(catSelect).trigger('change');
    //                     break;
    //                 }
    //             }
    //         }
    //     ");

    //     // Seleccionar tipo de producto (BARRA o COCINA)
    //     $I->selectOption('#tipo_producto', 'BARRA');

    //     // Establecer imagen desde la galería/assets para cumplir la regla de imagen
    //     $I->executeJS("
    //         var imgGaleria = document.getElementById('imagen_galeria');
    //         if (imgGaleria) imgGaleria.value = 'assets/img/productos/default-product.png';
    //         var prevCont = document.getElementById('previewImagenContainer');
    //         if (prevCont) prevCont.style.display = 'block';
    //     ");

    //     // Agregar un insumo principal a la receta desde el catálogo
    //     $I->executeJS("
    //         var firstInsumo = document.querySelector('#listaInsumosUI .btn-add-principal');
    //         if (firstInsumo) {
    //             firstInsumo.click();
    //         } else if (typeof insumosDB !== 'undefined' && insumosDB.length > 0) {
    //             addInsumoTo(insumosDB[0], 'principal');
    //         }
    //         $('#nombre, #precio, #descripcion').trigger('input').trigger('blur');
    //         $('#tipo_producto, #id_categoria').trigger('change');
    //     ");
    //     $I->wait(1);

    //     // Hacer clic en Guardar Producto
    //     $I->waitForElementClickable('#btnGuardarMenu', 5);
    //     $I->click('#btnGuardarMenu');

    //     // Esperar que el modal se oculte tras guardar
    //     $I->waitForElementNotVisible('#modalMenu', 10);

    //     // Validar mensaje de confirmación SweetAlert2
    //     $I->waitForElementVisible('.swal2-popup', 10);
    //     $I->see('Guardado');
    //     $I->makeScreenshot('22_menu_producto_guardado_exitoso');

    //     // Cerrar alerta
    //     $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
    //     $I->wait(1);

    //     // Verificar que el nuevo producto aparezca en la galería de tarjetas
    //     $I->waitForElementVisible('#galleryContainer', 10);
    //     $I->waitForText($nombreProducto, 5);
    // }








    // /**
    //  * 2. MODIFICAR: Flujo completo y automatizado para editar un producto existente en el menú.
    //  */
    // public function modificarProductoEnMenuConDatosValidos(AcceptanceTester $I): void
    // {
    //     $this->autenticarSiEsNecesario($I);

    //     $I->see('Menú');
    //     $I->waitForElementVisible('#galleryContainer', 10);
    //     $I->waitForElementVisible('#galleryContainer .btn-editar', 10);

    //     // Disparar clic en el botón de edición de la primera tarjeta disponible
    //     $I->executeJS("
    //         var editBtn = document.querySelector('#galleryContainer .btn-editar');
    //         if (editBtn) editBtn.click();
    //     ");

    //     // Esperar apertura del modal en modo edición
    //     $I->waitForElementVisible('#modalMenu', 5);
    //     $I->see('Editar Producto del Menú');

    //     // Modificar valores (letras y espacios respetando formato Title Case)
    //     $sufijo = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4);
    //     $nuevoNombre = 'Platillo Gourmet ' . ucfirst($sufijo);
    //     $nuevoPrecio = '14.75';
    //     $nuevaDescripcion = 'Receta renovada con presentación gourmet y especias.';

    //     $I->fillField('#nombre', $nuevoNombre);
    //     $I->fillField('#precio', $nuevoPrecio);
    //     $I->fillField('#descripcion', $nuevaDescripcion);

    //     // Disparar eventos de validación
    //     $I->executeJS("
    //         $('#nombre, #precio, #descripcion').trigger('input').trigger('blur');
    //     ");
    //     $I->wait(1);

    //     // Guardar cambios
    //     $I->waitForElementClickable('#btnGuardarMenu', 5);
    //     $I->click('#btnGuardarMenu');

    //     // Esperar que el modal se oculte
    //     $I->waitForElementNotVisible('#modalMenu', 10);

    //     // Validar mensaje SweetAlert2 de éxito
    //     $I->waitForElementVisible('.swal2-popup', 10);
    //     $I->see('Actualizado');
    //     $I->makeScreenshot('23_menu_producto_modificado_exitoso');

    //     // Cerrar alerta
    //     $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
    //     $I->wait(1);

    //     // Verificar que el producto modificado aparezca en la galería
    //     $I->waitForElementVisible('#galleryContainer', 10);
    //     $I->waitForText($nuevoNombre, 5);
    // }









    /**
     * 3. ELIMINAR: Flujo completo y automatizado para remover un producto del menú.
     */
    public function eliminarProductoDelMenu(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Menú');
        $I->waitForElementVisible('#galleryContainer', 10);

        // Primero crear un producto temporal exclusivo para eliminar
        $I->waitForElementVisible('#btnNuevoMenu', 5);
        $I->click('#btnNuevoMenu');
        $I->waitForElementVisible('#modalMenu', 5);

        $sufijo = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4);
        $nombreBorrar = 'Para Borrar ' . ucfirst($sufijo);
        $I->fillField('#nombre', $nombreBorrar);
        $I->fillField('#precio', '4.00');
        $I->fillField('#descripcion', 'Producto creado temporalmente para probar su eliminación.');

        $I->executeJS("
            var catSelect = document.getElementById('id_categoria');
            if (catSelect && catSelect.options.length > 1) {
                for (var i = 1; i < catSelect.options.length; i++) {
                    if (catSelect.options[i].value) {
                        catSelect.selectedIndex = i;
                        $(catSelect).trigger('change');
                        break;
                    }
                }
            }
        ");

        $I->selectOption('#tipo_producto', 'BARRA');

        $I->executeJS("
            var imgGaleria = document.getElementById('imagen_galeria');
            if (imgGaleria) imgGaleria.value = 'assets/img/productos/default-product.png';
            var prevCont = document.getElementById('previewImagenContainer');
            if (prevCont) prevCont.style.display = 'block';

            var firstInsumo = document.querySelector('#listaInsumosUI .btn-add-principal');
            if (firstInsumo) {
                firstInsumo.click();
            } else if (typeof insumosDB !== 'undefined' && insumosDB.length > 0) {
                addInsumoTo(insumosDB[0], 'principal');
            }
            $('#nombre, #precio, #descripcion').trigger('input').trigger('blur');
            $('#tipo_producto, #id_categoria').trigger('change');
        ");
        $I->wait(1);

        $I->waitForElementClickable('#btnGuardarMenu', 5);
        $I->click('#btnGuardarMenu');
        $I->waitForElementNotVisible('#modalMenu', 10);

        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Ubicar la tarjeta del producto recién creado y hacer clic en su botón de eliminar
        $I->waitForElementVisible('#galleryContainer', 10);
        $I->waitForText($nombreBorrar, 5);

        $I->executeJS("
            var cards = document.querySelectorAll('#galleryContainer .card');
            for (var i = 0; i < cards.length; i++) {
                if (cards[i].innerText.indexOf('" . $nombreBorrar . "') !== -1) {
                    var delBtn = cards[i].querySelector('.btn-eliminar');
                    if (delBtn) delBtn.click();
                    break;
                }
            }
        ");

        // Responder afirmativamente al diálogo de advertencia de SweetAlert2
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar notificación de éxito tras completarse la eliminación AJAX
        $I->waitForText('Eliminado', 10);
        $I->makeScreenshot('24_menu_producto_eliminado_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar que el producto ya no se encuentre visible en el contenedor de galería
        $I->dontSee($nombreBorrar, '#galleryContainer');
    }



    // /**
    //  * Prueba el catálogo público de comensales.
    //  */
    // public function probarCatalogoPublicoMenu(AcceptanceTester $I): void
    // {
    //     $I->amOnPage('/?page=menu&type=publico');
    //     $I->waitForElementVisible('body', 5);
    //     $I->makeScreenshot('25_menu_catalogo_publico');
    // }




}
