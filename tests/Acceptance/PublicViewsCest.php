<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class PublicViewsCest
{
    public function capturarLandingPage(AcceptanceTester $I): void
    {
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('53_landing_home_publico');
    }

    public function capturarMenuPublico(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=nuestro-menu');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('54_nuestro_menu_catalogo_cliente');
    }

    public function capturarReservacionPublica(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=reservar');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('55_reservar_formulario_publico');
    }

    public function capturarAsistenciaPublica(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=asistencia-publica');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('56_asistencia_reloj_biometrico');
    }

    public function capturarRecuperarPassword(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=recuperar-password');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('57_recuperar_password_formulario');
    }
}
