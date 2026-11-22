<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class InsumoProveedorCest
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

        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
    }

    public function capturarModuloInsumo(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Insumo');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('25_insumo_panel_principal');

        // Modal de Registro Insumo
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalInsumo\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('26_insumo_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloCategoriaInsumo(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=CategoriaInsumo');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('27_categoria_insumo_panel');

        // Modal de Categoría Insumo
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalCategoriaInsumo\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('28_categoria_insumo_modal');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloProveedor(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Proveedor');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('29_proveedor_panel_principal');

        // Modal de Proveedor
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalProveedor\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('30_proveedor_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }
}
