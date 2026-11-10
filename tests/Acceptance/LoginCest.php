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
}
