<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class ReservacionCest
{
    public function verificarInterfazReservarMesa(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Reservacion&type=publico');
        $I->see('Reservar Mesa');
        $I->see('Pendiente');
        $I->see('Confirmada');
        $I->see('Ocupado');
        $I->see('Iniciar Sesión para Reservar');
        $I->seeElement('#calendarPublico');
        $I->seeElementInDOM('#calendarLoader');
    }

    public function navegarDesdeReservacionesHaciaLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Reservacion&type=publico');
        $I->waitForElementVisible('a[href*="page=login"]', 5);
        $I->click('a[href*="page=login"]');
        $I->seeInCurrentUrl('page=login');
        $I->see('Cédula de identidad');
        $I->see('Contraseña');
    }
}
