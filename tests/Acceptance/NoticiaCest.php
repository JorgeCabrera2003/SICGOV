<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class NoticiaCest
{
    public function verificarPortalPublicoNoticias(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Noticia&type=publico');
        $I->see('Todas las Noticias');
        $I->seeElement('.news-top-bar');
    }
}
