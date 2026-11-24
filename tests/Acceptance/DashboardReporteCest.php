<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class DashboardReporteCest
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

    public function capturarDashboardPrincipal(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Dashboard');
        $I->waitForElementVisible('body', 5);
        $I->wait(3); // esperar a que rendericen gráficos de Chart.js
        $I->makeScreenshot('48_dashboard_kpis_graficos');
    }

    public function capturarModuloReportes(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Reporte');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('49_reportes_panel_generacion');
    }

    public function capturarModuloNotificaciones(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Notification');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('50_notificaciones_centro');
    }

    public function capturarModuloPerfil(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Perfil');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('51_perfil_usuario_seguridad');
    }

    public function capturarModuloAyuda(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Ayuda');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('52_ayuda_sistema_manual');
    }
}
