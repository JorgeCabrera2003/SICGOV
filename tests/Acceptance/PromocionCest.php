<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class PromocionCest
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

    public function capturarModuloPromociones(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Promocion');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('46_promocion_panel_principal');

        // Modal de Registro Promoción
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalPromocion\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('47_promocion_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }
}
