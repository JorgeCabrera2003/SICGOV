<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class SecurityModulesCest
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

    public function capturarModuloUsuarios(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Usuario');
        $I->waitForElementVisible('body', 5);
        $I->makeScreenshot('01_usuario_panel_principal');
        
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalUsuario\"]'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('02_usuario_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloRoles(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Rol');
        $I->waitForElementVisible('body', 5);
        $I->makeScreenshot('03_rol_panel_principal');

        $I->executeJS("var b = document.querySelector('#btnNuevoRol, button[data-bs-target=\"#modalRol\"], .btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('04_rol_modal_registro_matriz');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloBitacora(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Bitacora');
        $I->waitForElementVisible('body', 5);
        $I->wait(2); // esperar a que cargue DataTables por AJAX
        $I->makeScreenshot('05_bitacora_panel_filtros');

        $I->executeJS("var b = document.querySelector('.btn-ver-detalle'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('06_bitacora_modal_detalle');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloSistema(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=ModuloSistema');
        $I->waitForElementVisible('body', 5);
        $I->makeScreenshot('07_modulos_sistema_lista');

        $I->executeJS("var b = document.getElementById('btnComprobarModulo'); if(b) b.click();");
        $I->wait(2);
        $I->makeScreenshot('08_modulos_sistema_comprobar_alerta');
    }

    public function capturarModuloBackup(AcceptanceTester $I): void
    {
        $I->amOnPage('/?page=Backup&type=backups');
        $I->waitForElementVisible('body', 5);
        $I->wait(1);
        $I->makeScreenshot('09_backup_panel_gestion');

        // Modal Nuevo Respaldo
        $I->executeJS("var b = document.querySelector('button[data-bs-target=\"#modalRespaldo\"]'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('10_backup_modal_nuevo');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);

        // Modal Programación
        $I->executeJS("var b = document.querySelector('button[data-bs-target=\"#modalProgramacion\"]'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('11_backup_modal_programacion');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }
}
