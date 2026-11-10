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
}
