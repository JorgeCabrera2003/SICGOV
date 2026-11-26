<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Aceptación / Interfaz para el Módulo de Categorías del Menú
 */
class CategoriaCest
{
    private string $cookieSession = '';
    private string $categoriaPrueba = '';

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
        $I->amOnPage('/?page=Categoria');
        $I->waitForElementVisible('body', 5);

        // Si la sesión no fue válida y redirigió a Login, autenticarse por formulario
        $urlActual = $I->grabFromCurrentUrl();
        if (str_contains($urlActual, 'Login')) {
            $I->fillField('input[name="CI"]', '00000000');
            $I->fillField('input[name="password"]', '1234');
            $I->click('button[name="peticion"][value="sesion"]');
            $I->waitForElementVisible('body', 5);
            $I->amOnPage('/?page=Categoria');
            $I->waitForElementVisible('body', 5);
        }
    }

    /**
     * Prueba la navegación al panel principal y apertura del modal.
     */
    public function probarPanelPrincipalYModalCategoria(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Categorías');
        $I->makeScreenshot('18_categoria_panel_principal');

        // Modal de Registro Categoría
        $I->waitForElementVisible('#btnNuevaCategoria', 5);
        $I->click('#btnNuevaCategoria');
        $I->waitForElementVisible('#modalCategoria', 5);
        $I->wait(1);
        $I->makeScreenshot('19_categoria_modal_registro');

        // Cerrar modal para no interferir con pruebas posteriores
        $I->click('#modalCategoria .btn-close');
        $I->waitForElementNotVisible('#modalCategoria', 5);
    }



    // /**
    //  * 1. REGISTRAR: Flujo completo y automatizado para registrar una nueva categoría con datos válidos.
    //  */
    // public function registrarCategoriaConDatosValidos(AcceptanceTester $I): void
    // {
    //     $this->autenticarSiEsNecesario($I);

    //     $I->see('Categorías');

    //     // Abrir el modal de registro
    //     $I->waitForElementVisible('#btnNuevaCategoria', 5);
    //     $I->click('#btnNuevaCategoria');
    //     $I->waitForElementVisible('#modalCategoria', 5);
    //     $I->wait(1);

    //     // Generar un nombre único válido (solo letras y espacios, primera mayúscula)
    //     $sufijo = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
    //     $nombreCategoria = 'Bebidas ' . ucfirst($sufijo);

    //     $this->categoriaPrueba = $nombreCategoria;

    //     // Llenar el campo de nombre de categoría
    //     $I->fillField('#nombre_categoria', $nombreCategoria);

    //     // Disparar eventos de validación y verificación de duplicados
    //     $I->executeJS("
    //         $('#nombre_categoria').trigger('input').trigger('blur');
    //     ");
    //     $I->wait(1); // Esperar resolución de debounce asíncrono para habilitar el botón

    //     // Hacer clic en Guardar Categoría
    //     $I->waitForElementClickable('#btnGuardarCategoria', 5);
    //     $I->click('#btnGuardarCategoria');

    //     // Confirmar en SweetAlert2
    //     $I->waitForElementVisible('.swal2-confirm', 5);
    //     $I->click('.swal2-confirm');

    //     // Esperar que el modal se oculte tras completar la petición AJAX
    //     $I->waitForElementNotVisible('#modalCategoria', 10);

    //     // Confirmar notificación de éxito tras la respuesta AJAX
    //     $I->waitForText('Éxito', 10);
    //     $I->makeScreenshot('20_categoria_registrada_exitoso');

    //     // Cerrar alerta si sigue visible
    //     $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
    //     $I->wait(1);

    //     // Verificar persistencia visual en la tabla DataTables
    //     $I->waitForElementVisible('#tablaCategoria tbody tr', 10);
    //     $I->fillField('input[type="search"]', $nombreCategoria);
    //     $I->wait(1);
    //     $I->waitForText($nombreCategoria, 5);
    // }










    // /**
    //  * 2. MODIFICAR: Flujo completo y automatizado para editar los datos de una categoría existente.
    //  */
    // public function modificarCategoriaConDatosValidos(AcceptanceTester $I): void
    // {
    //     $this->autenticarSiEsNecesario($I);

    //     $I->see('Categorías');
    //     $I->waitForElementVisible('#tablaCategoria tbody tr', 10);

    //     if (!empty($this->categoriaPrueba)) {
    //         $I->fillField('input[type="search"]', $this->categoriaPrueba);
    //         $I->wait(1);
    //     }

    //     // Disparar la acción 'Editar' mediante rellenar(pos, 0) sobre la fila filtrada
    //     $I->executeJS("
    //         var editBtn = document.querySelector('#tablaCategoria tbody tr a[onclick*=\"rellenar\"]');
    //         if (editBtn) {
    //             editBtn.click();
    //         } else {
    //             var row = document.querySelector('#tablaCategoria tbody tr');
    //             if (row && typeof window.rellenar === 'function') {
    //                 window.rellenar(row, 0);
    //             }
    //         }
    //     ");

    //     // Esperar que el modal se abra en modo edición
    //     $I->waitForElementVisible('#modalCategoria', 5);
    //     $I->wait(1);
    //     $I->see('Actualizar Categoría');

    //     // Modificar el nombre con datos válidos únicos (Title Case)
    //     $sufijo = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
    //     $nuevoNombre = 'Postres ' . ucfirst($sufijo);

    //     $I->fillField('#nombre_categoria', $nuevoNombre);

    //     // Disparar eventos de validación
    //     $I->executeJS("
    //         $('#nombre_categoria').trigger('input').trigger('blur');
    //     ");
    //     $I->wait(1);

    //     // Hacer clic en 'Actualizar Categoría'
    //     $I->waitForElementClickable('#btnGuardarCategoria', 5);
    //     $I->click('#btnGuardarCategoria');

    //     // Confirmar en SweetAlert2
    //     $I->waitForElementVisible('.swal2-confirm', 5);
    //     $I->click('.swal2-confirm');

    //     // Esperar que el modal se oculte
    //     $I->waitForElementNotVisible('#modalCategoria', 10);

    //     // Confirmar notificación de éxito tras la respuesta AJAX
    //     $I->waitForText('Éxito', 10);
    //     $I->makeScreenshot('21_categoria_modificada_exitoso');

    //     // Cerrar alerta
    //     $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
    //     $I->wait(1);

    //     // Verificar que el nombre modificado se refleje en la tabla
    //     $I->fillField('input[type="search"]', $nuevoNombre);
    //     $I->wait(1);
    //     $I->waitForText($nuevoNombre, 5);
    // }












    /**
     * 3. ELIMINAR: Flujo completo y automatizado para eliminar una categoría.
     */
    public function eliminarCategoria(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Categorías');

        // Primero registrar una categoría temporal exclusiva para eliminar (prueba 100% independiente)
        $I->waitForElementVisible('#btnNuevaCategoria', 5);
        $I->click('#btnNuevaCategoria');
        $I->waitForElementVisible('#modalCategoria', 5);
        $I->wait(1);

        $sufijo = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
        $nombreEliminar = 'Temporal ' . ucfirst($sufijo);

        $I->fillField('#nombre_categoria', $nombreEliminar);

        $I->executeJS("
            $('#nombre_categoria').trigger('input').trigger('blur');
        ");
        $I->wait(1);

        $I->waitForElementClickable('#btnGuardarCategoria', 5);
        $I->click('#btnGuardarCategoria');
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');
        $I->waitForElementNotVisible('#modalCategoria', 10);

        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Ahora buscar la categoría recién creada en la tabla
        $I->waitForElementVisible('#tablaCategoria tbody tr', 10);
        $I->fillField('input[type="search"]', $nombreEliminar);
        $I->wait(1);
        $I->waitForText($nombreEliminar, 5);

        // Disparar la acción directa de eliminar
        $I->executeJS("
            var delBtn = document.querySelector('#tablaCategoria tbody tr a[onclick*=\"eliminar\"]');
            if (delBtn) {
                delBtn.click();
            } else {
                var row = document.querySelector('#tablaCategoria tbody tr');
                if (row && typeof window.eliminar === 'function') {
                    window.eliminar(row);
                }
            }
        ");

        // Diálogo SweetAlert2 de confirmación ("Se eliminará la Categoría")
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar notificación de éxito tras completarse la petición AJAX
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('22_categoria_eliminada_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar que la categoría eliminada ya no exista en la tabla
        $I->fillField('input[type="search"]', $nombreEliminar);
        $I->wait(1);
        $I->dontSee($nombreEliminar, '#tablaCategoria tbody');
    }






}
