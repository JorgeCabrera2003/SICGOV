<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class RRHHCest
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

    public function capturarModuloCargo(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Cargo');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('35_cargo_panel_principal');

        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalCargo\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('36_cargo_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloTurno(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Turno');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('37_turno_panel_principal');

        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalTurno\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('38_turno_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloHorario(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Horario');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('39_horario_panel_principal');

        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalHorario\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('40_horario_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloAsistencia(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Asistencia');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('41_asistencia_panel_control');
    }

    public function capturarModuloTipoPermiso(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=TipoPermiso');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('42_tipo_permiso_panel');

        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalTipoPermiso\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('43_tipo_permiso_modal');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloPermisoLaboral(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=PermisoLaboral');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('44_permiso_laboral_panel');

        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalPermisoLaboral\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('45_permiso_laboral_modal');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }
}
