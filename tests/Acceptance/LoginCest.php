<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Pruebas de Interfaz y Aceptación para el Inicio de Sesión (Login) y Cierre de Sesión (Logout)
 */
class LoginCest
{
    
    
    
    
    /**
     * Inyecta el token de bypass de reCAPTCHA para entorno de pruebas automatizadas.
     */
    private function inyectarBypassRecaptcha(AcceptanceTester $I): void
    {
        $I->executeJS("
            var form = document.getElementById('login-form');
            if (form) {
                var recaptchaInput = form.querySelector('textarea[name=\"g-recaptcha-response\"]');
                if (!recaptchaInput) {
                    recaptchaInput = document.createElement('textarea');
                    recaptchaInput.name = 'g-recaptcha-response';
                    recaptchaInput.style.display = 'none';
                    form.appendChild(recaptchaInput);
                }
                recaptchaInput.value = 'bypass-test';
            }
        ");
    }






    /**
     * 1. VERIFICACIÓN VISUAL: Comprueba que la interfaz de login contenga todos sus elementos requeridos.
     */
    public function verificarInterfazLogin(AcceptanceTester $I): void
    {
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/?page=Login');
        $I->waitForElementVisible('body', 5);

        $I->seeInTitle('Login - Good Vibes');
        $I->see('Bienvenido');
        $I->see('Tu Portal');
        $I->see('Good Vibes');
        $I->see('Cédula de identidad');
        $I->see('Contraseña');

        $I->seeElement('select[name="particle"]');
        $I->seeElement('input[name="CI"]');
        $I->seeElement('input[name="password"]');
        $I->seeElement('button[name="peticion"][value="sesion"]');
        $I->seeElement('a[href*="recuperar-password"]');

        $I->makeScreenshot('01_login_interfaz_visual');
    }








    // /**
    //  * 2. VALIDACIÓN CLIENTE: Intento de envío con campos vacíos activa alerta visual de advertencia.
    //  */
    // public function probarValidacionDeCamposVacios(AcceptanceTester $I): void
    // {
    //     $I->amOnPage('/?page=Login');
    //     $I->waitForElementVisible('#login-form', 5);

    //     // Dejar campos limpios y hacer clic en Ingresar
    //     $I->fillField('input[name="CI"]', '');
    //     $I->fillField('input[name="password"]', '');

    //     // Quitar atributo 'required' nativo de HTML5 temporalmente para evaluar la validación de JS (login.js)
    //     $I->executeJS("
    //         $('input[name=\"CI\"], input[name=\"password\"]').removeAttr('required');
    //     ");

    //     $I->click('button[name="peticion"][value="sesion"]');

    //     // login.js dispara SweetAlert con título 'Campos vacíos'
    //     $I->waitForElementVisible('.swal2-popup', 5);
    //     $I->see('Campos vacíos');
    //     $I->see('Por favor completa todos los campos');
    //     $I->makeScreenshot('02_login_alerta_campos_vacios');

    //     // Cerrar la alerta SweetAlert
    //     $I->click('.swal2-confirm');
    //     $I->waitForElementNotVisible('.swal2-popup', 5);
    // }







    // /**
    //  * 3. CREDENCIALES INVÁLIDAS: El sistema rechaza datos erróneos y muestra mensaje de error.
    //  */
    // public function probarInicioSesionConCredencialesInvalidas(AcceptanceTester $I): void
    // {
    //     $I->resetCookie('PHPSESSID');
    //     $I->amOnPage('/?page=Login');
    //     $I->waitForElementVisible('#login-form', 5);

    //     // Ingresar credenciales que no coinciden
    //     $I->selectOption('select[name="particle"]', 'V');
    //     $I->fillField('input[name="CI"]', '00000000');
    //     $I->fillField('input[name="password"]', 'clave_totalmente_invalida_9999');

    //     // Inyectar bypass de reCAPTCHA para pruebas
    //     $this->inyectarBypassRecaptcha($I);

    //     $I->click('button[name="peticion"][value="sesion"]');

    //     // Debe recargar en la vista de Login con alerta de error
    //     $I->waitForElementVisible('.alert-danger', 5);
    //     $I->seeElement('.alert-danger');
    //     $I->makeScreenshot('03_login_credenciales_invalidas');
    // }





    /**
     * 4. LOGIN EXITOSO: El sistema autentica las credenciales válidas y redirige al Dashboard.
     */
    public function probarInicioSesionExitosoConCredencialesValidas(AcceptanceTester $I): void
    {
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/?page=Login');
        $I->waitForElementVisible('#login-form', 5);

        // Ingresar credenciales válidas del SuperUsuario base del sistema
        $I->selectOption('select[name="particle"]', 'V');
        $I->fillField('input[name="CI"]', '00000000');
        $I->fillField('input[name="password"]', '1234');

        // Inyectar bypass de reCAPTCHA
        $this->inyectarBypassRecaptcha($I);

        $I->click('button[name="peticion"][value="sesion"]');

        // Esperar que el navegador procese la sesión y redirija al Dashboard
        $I->waitForElementVisible('body', 10);
        $I->seeInCurrentUrl('Dashboard');

        // Verificar elementos característicos de la sesión administrativa activa
        $I->see('Dashboard Principal');
        $I->see('admin_root');

        $I->makeScreenshot('04_login_exitoso_dashboard');
    }




    // /**
    //  * 5. CIERRE DE SESIÓN: Desde el panel autenticado se puede cerrar sesión y volver a Login.
    //  */
    // public function probarCierreDeSesion(AcceptanceTester $I): void
    // {
    //     // Asegurar que estamos en el Dashboard autenticados
    //     $I->amOnPage('/?page=Dashboard');
    //     $urlActual = $I->grabFromCurrentUrl();

    //     // Si no hay sesión previa, iniciarla
    //     if (str_contains($urlActual, 'Login') || str_contains($urlActual, 'login')) {
    //         $I->selectOption('select[name="particle"]', 'V');
    //         $I->fillField('input[name="CI"]', '00000000');
    //         $I->fillField('input[name="password"]', '1234');
    //         $this->inyectarBypassRecaptcha($I);
    //         $I->click('button[name="peticion"][value="sesion"]');
    //         $I->waitForElementVisible('body', 10);
    //     }

    //     $I->see('Dashboard Principal');

    //     // Hacer clic en el botón de Cerrar Sesión del menú lateral
    //     $I->waitForElementVisible('a[href*="logout"]', 5);
    //     $I->click('a[href*="logout"]');

    //     // Verificar que el usuario sea redirigido de vuelta al Login
    //     $I->waitForElementVisible('#login-form', 5);
    //     $I->seeInCurrentUrl('login');
    //     $I->see('Bienvenido');
    //     $I->see('Cédula de identidad');

    //     $I->makeScreenshot('05_logout_exitoso');
    // }



    // /**
    //  * 6. INTERACCIÓN UI: Alternar al slide de registro de cuenta pública.
    //  */
    // public function alternarSlideHaciaRegistro(AcceptanceTester $I): void
    // {
    //     $I->amOnPage('/?page=Login');
    //     $I->waitForElementVisible('#authCarousel', 5);

    //     // Hacer clic en "¿No tienes cuenta? Regístrate"
    //     $I->click('button[data-bs-slide-to="1"]');
    //     $I->waitForElementVisible('div[data-auth-slide="register"]', 5);
    //     $I->see('Crear Cuenta');

    //     $I->makeScreenshot('06_slide_crear_cuenta');
    // }



}
