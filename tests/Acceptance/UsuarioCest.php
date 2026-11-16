<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Aceptación / Interfaz para el Módulo de Usuario e Inicio de Sesión
 */
class UsuarioCest
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

    public function probarLoginYRegistroPublico(AcceptanceTester $I): void
    {
        // 1. Pantalla de Login sin sesión
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/?page=Login');
        $I->waitForElementVisible('body', 5);
        $I->see('Ingresar al Sistema');
        $I->makeScreenshot('12_auth_login_panel');

        // 2. Registro Público
        $I->amOnPage('/?page=crear-cuenta');
        $I->waitForElementVisible('body', 5);
        $I->see('Crear Cuenta');
        $I->makeScreenshot('13_auth_registro_publico');
    }

    public function probarPanelGestionUsuarios(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=usuario');
        $I->waitForElementVisible('body', 5);
        $I->see('Usuarios');
    }
}
