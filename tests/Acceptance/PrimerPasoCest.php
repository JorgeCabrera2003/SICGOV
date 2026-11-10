<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class PrimerPasoCest
{
    public function verificarPaginaPrincipal(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->seeInTitle('Good Vibes');
    }
}

