<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Pruebas de Aceptación / Interfaz de Usuario para el Módulo de Papelera de Reciclaje
 * Verifica la renderización de la interfaz web, componentes DataTables, badges de módulos,
 * diálogos SweetAlert de confirmación y la discrepancia del botón de eliminación permanente.
 */
class PapeleraCest
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

    public function probarInterfazPanelPapelera(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }

        $I->amOnPage('/?page=papelera');
        $I->waitForElementVisible('body', 5);
        $I->waitForElementVisible('#tablaPapelera', 5);
        $I->wait(2); // Esperar carga de datos asíncronos vía DataTables

        // Captura de pantalla para la documentación y reportes
        $I->makeScreenshot('24_papelera_panel_principal');

        // Validaciones estructurales de la interfaz
        $I->see('Papelera de Reciclaje');
        $I->see('Aquí puedes recuperar registros que fueron eliminados de otros módulos.');
        $I->seeElement('#tablaPapelera');
        $I->see('Módulo', 'thead');
        $I->see('Descripción / Nombre', 'thead');
        $I->see('Acciones', 'thead');
    }

    public function auditarDiscrepanciaBotonEliminarPermanente(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }

        $I->amOnPage('/?page=papelera');
        $I->waitForElementVisible('#tablaPapelera', 5);
        $I->wait(1);

        // Confirmar que el frontend deshabilitó/comentó el botón de eliminación física
        $I->dontSeeElement('.btn-eliminar-permanente');

        // Confirmar que el botón restaurar sí se encuentra presente
        $I->seeElement('.btn-restaurar');
    }

    public function probarModalConfirmacionRestaurar(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }

        $I->amOnPage('/?page=papelera');
        $I->waitForElementVisible('#tablaPapelera', 5);
        $I->wait(2);

        // Hacer click en el primer botón restaurar disponible
        $I->click('.btn-restaurar');
        $I->wait(1);

        // Verificar que el diálogo modal de SweetAlert2 aparece
        $I->see('¿Restaurar registro?');
        
        // Cancelar el diálogo para no alterar datos de producción
        $I->click('.swal2-cancel');
        $I->wait(1);
    }
}
