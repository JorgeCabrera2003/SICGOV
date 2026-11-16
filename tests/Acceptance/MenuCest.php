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

    public function probarPanelAdminMenu(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=menu');
        $I->waitForElementVisible('body', 5);
        $I->see('Menú');
        $I->makeScreenshot('20_menu_panel_admin');

        // Modal de Registro Producto
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalProducto\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('21_menu_modal_producto');
    }

    public function probarCatalogoPublicoMenu(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=menu&type=publico');
        $I->waitForElementVisible('body', 5);
        $I->makeScreenshot('22_menu_catalogo_publico');
    }
}
