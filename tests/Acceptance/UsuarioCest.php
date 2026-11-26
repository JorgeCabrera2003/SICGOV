<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Aceptación / Interfaz para el Módulo de Usuarios
 */
class UsuarioCest
{
    private string $cookieSession = '';
    private string $usernamePrueba = '';

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
        $I->amOnPage('/?page=usuario');
        $I->waitForElementVisible('body', 5);

        // Si la sesión no fue válida y redirigió a Login, autenticarse por formulario
        $urlActual = $I->grabFromCurrentUrl();
        if (str_contains($urlActual, 'Login') || str_contains($urlActual, 'login')) {
            $I->fillField('input[name="CI"]', '00000000');
            $I->fillField('input[name="password"]', '1234');
            $I->click('button[name="peticion"][value="sesion"]');
            $I->waitForElementVisible('body', 5);
            $I->amOnPage('/?page=usuario');
            $I->waitForElementVisible('body', 5);
        }
    }

    /**
     * Prueba la navegación al panel de usuarios y apertura del modal.
     */
    public function probarPanelGestionUsuarios(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Gestión de Usuarios');
        $I->makeScreenshot('10_usuario_panel_principal');

        // Modal de Registro de Usuario
        $I->waitForElementVisible('#btn-nuevo', 5);
        $I->click('#btn-nuevo');
        $I->waitForElementVisible('#modalUsuario', 5);
        $I->wait(1);
        $I->makeScreenshot('11_usuario_modal_registro');

        // Cerrar modal
        $I->click('#modalUsuario .btn-close');
        $I->waitForElementNotVisible('#modalUsuario', 5);
    }



    // /**
    //  * 1. REGISTRAR: Flujo completo y automatizado para registrar un nuevo usuario en el sistema.
    //  */
    // public function registrarUsuarioConDatosValidos(AcceptanceTester $I): void
    // {
    //     $this->autenticarSiEsNecesario($I);

    //     $I->see('Gestión de Usuarios');

    //     // Abrir el modal de registro
    //     $I->waitForElementVisible('#btn-nuevo', 5);
    //     $I->click('#btn-nuevo');
    //     $I->waitForElementVisible('#modalUsuario', 5);
    //     $I->wait(1); // Esperar que la animación del modal termine

    //     // Esperar a que la petición AJAX 'cargarEmpleadosSinUsuario' termine y llene el select de empleados
    //     $I->waitForElementVisible('#cedula option:nth-child(2)', 5);

    //     // Generar un username único en Title Case natural (solo letras /^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ]+$/)
    //     $sufijoLetras = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5));
    //     $username = 'User' . $sufijoLetras;
    //     $clave = '1234';

    //     $this->usernamePrueba = $username;

    //     // Seleccionar un empleado disponible de la lista desplegable
    //     $I->executeJS("
    //         var selectEmp = document.getElementById('cedula');
    //         if (selectEmp && selectEmp.options.length > 1) {
    //             for (var i = 1; i < selectEmp.options.length; i++) {
    //                 if (selectEmp.options[i].value) {
    //                     selectEmp.selectedIndex = i;
    //                     $(selectEmp).trigger('change');
    //                     break;
    //                 }
    //             }
    //         }
    //         var selectRol = document.getElementById('rol');
    //         if (selectRol && selectRol.options.length > 1) {
    //             for (var i = 1; i < selectRol.options.length; i++) {
    //                 if (selectRol.options[i].value) {
    //                     selectRol.selectedIndex = i;
    //                     $(selectRol).trigger('change');
    //                     break;
    //                 }
    //             }
    //         }
    //     ");

    //     // Llenar campos de nombre de usuario y contraseña
    //     $I->fillField('#username', $username);
    //     $I->fillField('#clave', $clave);
    //     $I->fillField('#rclave', $clave);

    //     // Disparar eventos de validación en tiempo real para activar el botón
    //     $I->executeJS("
    //         $('#username, #clave, #rclave').trigger('input').trigger('blur');
    //         $('#cedula, #rol').trigger('change');
    //     ");
    //     $I->wait(1);

    //     // Hacer clic en Guardar Usuario
    //     $I->waitForElementClickable('#btnUsuarioForm', 5);
    //     $I->click('#btnUsuarioForm');

    //     // Confirmar en SweetAlert2
    //     $I->waitForElementVisible('.swal2-confirm', 5);
    //     $I->click('.swal2-confirm');

    //     // El modal debe ocultarse al completarse la petición AJAX
    //     $I->waitForElementNotVisible('#modalUsuario', 10);

    //     // Confirmar la notificación de éxito tras completarse la petición AJAX
    //     $I->waitForText('Éxito', 10);
    //     $I->makeScreenshot('12_usuario_registrado_exitoso');

    //     // Cerrar alerta
    //     $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
    //     $I->wait(1);

    //     // Verificar persistencia visual en la tabla DataTables
    //     $I->waitForElementVisible('#tabla-usuario tbody tr', 10);
    //     $I->fillField('input[type="search"]', $username);
    //     $I->wait(1);
    //     $I->waitForText($username, 5);
    // }










    // /**
    //  * 2. MODIFICAR: Flujo completo y automatizado para editar el usuario existente.
    //  */
    // public function modificarUsuarioConDatosValidos(AcceptanceTester $I): void
    // {
    //     $this->autenticarSiEsNecesario($I);

    //     $I->see('Gestión de Usuarios');
    //     $I->waitForElementVisible('#tabla-usuario tbody tr', 10);

    //     if (!empty($this->usernamePrueba)) {
    //         $I->fillField('input[type="search"]', $this->usernamePrueba);
    //         $I->wait(1);
    //     }

    //     // Disparar la acción 'Editar' mediante rellenar(pos, 0) sobre la primera fila
    //     $I->executeJS("
    //         var editBtn = document.querySelector('#tabla-usuario tbody tr a[onclick*=\"rellenar\"]');
    //         if (editBtn) {
    //             editBtn.click();
    //         } else {
    //             var row = document.querySelector('#tabla-usuario tbody tr');
    //             if (row && typeof window.rellenar === 'function') {
    //                 window.rellenar(row, 0);
    //             }
    //         }
    //     ");

    //     // Esperar que el modal se muestre en modo edición
    //     $I->waitForElementVisible('#modalUsuario', 5);
    //     $I->wait(1);
    //     $I->see('Actualizar Usuario');

    //     // Modificar el nombre de usuario (solo letras, Title Case)
    //     $sufijo = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4));
    //     $nuevoUsername = 'Useredit' . $sufijo;

    //     $I->fillField('#username', $nuevoUsername);

    //     // Disparar eventos de validación
    //     $I->executeJS("
    //         $('#username').trigger('input').trigger('blur');
    //     ");
    //     $I->wait(1);

    //     // Guardar cambios
    //     $I->waitForElementClickable('#btnUsuarioForm', 5);
    //     $I->click('#btnUsuarioForm');

    //     // Confirmar en SweetAlert2
    //     $I->waitForElementVisible('.swal2-confirm', 5);
    //     $I->click('.swal2-confirm');

    //     // Esperar que el modal se oculte
    //     $I->waitForElementNotVisible('#modalUsuario', 10);

    //     // Confirmar notificación de éxito tras la respuesta AJAX
    //     $I->waitForText('Éxito', 10);
    //     $I->makeScreenshot('13_usuario_modificado_exitoso');

    //     // Cerrar alerta
    //     $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
    //     $I->wait(1);

    //     // Verificar que el nuevo nombre de usuario aparezca en la tabla
    //     $I->waitForElementVisible('#tabla-usuario tbody tr', 10);
    //     $I->fillField('input[type="search"]', $nuevoUsername);
    //     $I->wait(1);
    //     $I->waitForText($nuevoUsername, 5);
    // }












    /**
     * 3. INACTIVAR/ACTIVAR: Flujo completo y automatizado para cambiar el estatus del usuario.
     */
    public function inactivarYActivarUsuario(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Gestión de Usuarios');
        $I->waitForElementVisible('#tabla-usuario tbody tr', 10);

        // Localizar una fila con acción de toggle de estatus
        $I->executeJS("
            var toggleBtn = document.querySelector('#tabla-usuario tbody tr a[onclick*=\"toggleEstatus\"]');
            if (toggleBtn) {
                toggleBtn.click();
            } else {
                var row = document.querySelector('#tabla-usuario tbody tr');
                if (row && typeof window.toggleEstatus === 'function') {
                    window.toggleEstatus(row, 0);
                }
            }
        ");

        // Responder afirmativamente al cuadro de diálogo de SweetAlert2
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Validar notificación de éxito tras completarse la petición AJAX
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('14_usuario_estatus_cambiado_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);
    }





}
