<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class BusinessModulesCest
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
    }

    public function capturarModuloAuthYLogin(AcceptanceTester $I): void
    {
        // 1. Pantalla de Login sin sesión
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/?page=Login');
        $I->waitForElementVisible('body', 5);
        $I->wait(1);
        $I->makeScreenshot('12_auth_login_panel');

        // 2. Slide de Registro Público
        $I->amOnPage('/?page=crear-cuenta');
        $I->waitForElementVisible('body', 5);
        $I->wait(1);
        $I->makeScreenshot('13_auth_registro_publico');
    }

    public function capturarModuloCliente(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=Cliente');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('14_cliente_panel_principal');

        // Modal de Registro
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalCliente\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('15_cliente_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloEmpleado(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=Empleado');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('16_empleado_panel_principal');

        // Modal de Registro Empleado
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalEmpleado\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('17_empleado_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloCategoria(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=Categoria');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('18_categoria_panel_principal');

        // Modal de Registro Categoría
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalCategoria\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('19_categoria_modal_registro');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);
    }

    public function capturarModuloMenuYPedidos(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        
        // 1. Panel Admin de Menú / Productos
        $I->amOnPage('/?page=Menu');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('20_menu_panel_admin');

        // Modal Nuevo Plato / Producto
        $I->executeJS("var b = document.getElementById('btn-nuevo') || document.querySelector('button[data-bs-target=\"#modalMenu\"]') || document.querySelector('.btn-nuevo'); if(b) b.click();");
        $I->wait(1);
        $I->makeScreenshot('21_menu_modal_producto');
        $I->executeJS("var m = document.querySelector('.modal.show'); if(m) { var inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide(); }");
        $I->wait(1);

        // 2. Catálogo Público del Menú
        $I->amOnPage('/?page=Menu&type=publico');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('22_menu_catalogo_publico');

        // 3. Panel de Pedidos Admin / POS
        $I->amOnPage('/?page=pedidos');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('23_pedidos_admin_pos_panel');
    }

    public function capturarModuloPapelera(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }

        // Panel de Papelera de Reciclaje
        $I->amOnPage('/?page=papelera');
        $I->waitForElementVisible('body', 5);
        $I->wait(2);
        $I->makeScreenshot('24_papelera_panel_principal');
    }
}
