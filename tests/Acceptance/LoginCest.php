<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class LoginCest
{
    public function verificarInterfazLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Login');
        $I->seeInTitle('Login - Good Vibes');
        $I->see('Tu Portal');
        $I->see('Good Vibes');
        $I->see('Cédula de identidad');
        $I->see('Contraseña');
        $I->seeElement('input[name="CI"]');
        $I->seeElement('input[name="password"]');
        $I->seeElement('button[name="peticion"][value="sesion"]');
    }

    public function interactuarConCamposDeLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Login');
        $I->fillField('input[name="CI"]', '00000000');
        $I->fillField('input[name="password"]', 'clave_demo_123');
        $I->seeInField('input[name="CI"]', '00000000');
        $I->seeInField('input[name="password"]', 'clave_demo_123');
        $I->scrollTo('a[href*="recuperar-password"]');
        $I->see('¿Olvidaste tu contraseña?');
    }

    public function alternarSlideHaciaRegistro(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Login');
        $I->scrollTo('button[data-bs-slide-to="1"]');
        $I->click('button[data-bs-slide-to="1"]');
        $I->waitForElementVisible('div[data-auth-slide="register"]', 5);
        $I->see('Crear Cuenta');
    }
}

