<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Aceptación / Interfaz para el Módulo de Clientes
 */
class ClienteCest
{
    private string $cookieSession = '';
    private string $cedulaPrueba = '';

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

    /**
     * Asegura que la sesión esté activa, navegando a la página de login si es necesario.
     */
    private function autenticarSiEsNecesario(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=Cliente');
        $I->waitForElementVisible('body', 5);

        // Si la sesión no fue válida y redirigió a Login, autenticarse por formulario
        $urlActual = $I->grabFromCurrentUrl();
        if (str_contains($urlActual, 'Login')) {
            $I->fillField('input[name="CI"]', '00000000');
            $I->fillField('input[name="password"]', '1234');
            $I->click('button[name="peticion"][value="sesion"]');
            $I->waitForElementVisible('body', 5);
            $I->amOnPage('/?page=Cliente');
            $I->waitForElementVisible('body', 5);
        }
    }

    /**
     * Prueba la navegación al panel principal y apertura del modal.
     */
    public function probarPanelPrincipalYModalCliente(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Clientes');
        $I->makeScreenshot('14_cliente_panel_principal');

        // Modal de Registro
        $I->waitForElementVisible('#btnNuevoCliente', 5);
        $I->click('#btnNuevoCliente');
        $I->waitForElementVisible('#modalCliente', 5);
        $I->wait(1);
        $I->makeScreenshot('15_cliente_modal_registro');

        // Cerrar modal
        $I->click('#modalCliente .btn-close');
        $I->waitForElementNotVisible('#modalCliente', 5);
    }

    /**
     * 1. REGISTRAR: Flujo completo y automatizado para registrar un nuevo cliente con datos válidos.
     */
    public function registrarClienteConDatosValidos(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Clientes');

        // Abrir el modal de registro
        $I->waitForElementVisible('#btnNuevoCliente', 5);
        $I->click('#btnNuevoCliente');
        $I->waitForElementVisible('#modalCliente', 5);
        $I->wait(1);

        // Generar datos únicos y válidos
        $cedulaNum = (string) rand(20000000, 29999999);
        $nombre = 'Maria';
        $apellido = 'Gonzalez';
        $telefono = (string) rand(1000000, 9999999);
        $correo = 'maria.' . $cedulaNum . '@test.com';
        $direccion = 'Urbanización del Este Calle 12 Casa 88';

        $this->cedulaPrueba = $cedulaNum;

        // Llenar campos requeridos
        $I->selectOption('#tipo_doc', 'V');
        $I->fillField('#cedula', $cedulaNum);
        $I->fillField('#fecha_nacimiento', '1998-08-25');
        $I->fillField('#nombre', $nombre);
        $I->fillField('#apellido', $apellido);
        $I->selectOption('#sexo', 'F');
        $I->selectOption('#prefijo_telefono', '0424');
        $I->fillField('#telefono', $telefono);
        $I->fillField('#correo', $correo);
        $I->fillField('#direccion', $direccion);

        // Disparar eventos de validación
        $I->executeJS("
            $('#cedula, #nombre, #apellido, #telefono, #correo, #direccion').trigger('input').trigger('blur');
            $('#tipo_doc, #sexo, #prefijo_telefono, #fecha_nacimiento').trigger('change');
        ");
        $I->wait(1);

        // Hacer clic en Guardar Cliente
        $I->waitForElementClickable('#btnClienteForm', 5);
        $I->click('#btnClienteForm');

        // Confirmar en SweetAlert2
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar cierre del modal
        $I->waitForElementNotVisible('#modalCliente', 10);

        // Confirmar alerta de éxito
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('19_cliente_registrado_exitoso');

        // Cerrar alerta si sigue visible
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar persistencia en DataTables
        $I->fillField('input[type="search"]', $cedulaNum);
        $I->wait(1);
        $I->waitForText($nombre, 5);
        $I->see($apellido);
    }

    /**
     * 2. MODIFICAR: Flujo completo y automatizado para editar los datos de un cliente existente.
     */
    public function modificarClienteConDatosValidos(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Clientes');
        $I->waitForElementVisible('#tablaCliente tbody tr', 10);

        if (!empty($this->cedulaPrueba)) {
            $I->fillField('input[type="search"]', $this->cedulaPrueba);
            $I->wait(1);
        }

        // Disparar la acción 'Editar' mediante rellenar(pos, 0)
        $I->executeJS("
            var editBtn = document.querySelector('#tablaCliente tbody tr a[onclick*=\"rellenar\"]');
            if (editBtn) {
                editBtn.click();
            } else {
                var row = document.querySelector('#tablaCliente tbody tr');
                if (row && typeof window.rellenar === 'function') {
                    window.rellenar(row, 0);
                }
            }
        ");

        // Esperar modal de edición
        $I->waitForElementVisible('#modalCliente', 5);
        $I->wait(1);
        $I->see('Actualizar Cliente');

        // Modificar valores (usando formato Title Case natural sin CamelCase para coincidir con capitalizarTexto)
        $nuevoNombre = 'Maria Elena';
        $nuevoApellido = 'Gonzalez Rojas';
        $nuevaDireccion = 'Calle 50 con Carrera 19 Modificada';

        $I->fillField('#nombre', $nuevoNombre);
        $I->fillField('#apellido', $nuevoApellido);
        $I->fillField('#direccion', $nuevaDireccion);

        // Disparar eventos de validación
        $I->executeJS("
            $('#nombre, #apellido, #direccion').trigger('input').trigger('blur');
        ");
        $I->wait(1);

        // Guardar cambios
        $I->waitForElementClickable('#btnClienteForm', 5);
        $I->click('#btnClienteForm');

        // Confirmar en SweetAlert2
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar que se cierre el modal
        $I->waitForElementNotVisible('#modalCliente', 10);

        // Confirmar notificación de éxito tras completarse la petición AJAX
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('22_cliente_modificado_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar datos modificados en la tabla
        $I->fillField('input[type="search"]', $nuevoNombre);
        $I->wait(1);
        $I->waitForText($nuevoNombre, 5);
        $I->see($nuevoApellido);
    }

    /**
     * 3. ELIMINAR: Flujo completo y automatizado para eliminar un cliente.
     */
    public function eliminarCliente(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Clientes');

        // Primero registrar un cliente temporal exclusivo para eliminar (asegura prueba 100% independiente)
        $I->waitForElementVisible('#btnNuevoCliente', 5);
        $I->click('#btnNuevoCliente');
        $I->waitForElementVisible('#modalCliente', 5);

        $cedulaEliminar = (string) rand(30000000, 39999999);
        $I->selectOption('#tipo_doc', 'V');
        $I->fillField('#cedula', $cedulaEliminar);
        $I->fillField('#fecha_nacimiento', '1995-05-20');
        $I->fillField('#nombre', 'Eliminar');
        $I->fillField('#apellido', 'Temporal');
        $I->selectOption('#sexo', 'M');
        $I->selectOption('#prefijo_telefono', '0414');
        $I->fillField('#telefono', '8887766');
        $I->fillField('#correo', 'eliminar.' . $cedulaEliminar . '@test.com');
        $I->fillField('#direccion', 'Direccion Temporal Para Borrar');

        $I->executeJS("
            $('#cedula, #nombre, #apellido, #telefono, #correo, #direccion').trigger('input').trigger('blur');
            $('#tipo_doc, #sexo, #prefijo_telefono, #fecha_nacimiento').trigger('change');
        ");
        $I->wait(1);

        $I->waitForElementClickable('#btnClienteForm', 5);
        $I->click('#btnClienteForm');
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');
        $I->waitForElementNotVisible('#modalCliente', 10);

        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Ahora buscar al cliente recién creado en la tabla
        $I->waitForElementVisible('#tablaCliente tbody tr', 10);
        $I->fillField('input[type="search"]', $cedulaEliminar);
        $I->wait(1);
        $I->waitForText($cedulaEliminar, 5);

        // Disparar acción directa de eliminar
        $I->executeJS("
            var delBtn = document.querySelector('#tablaCliente tbody tr a[onclick*=\"eliminarClienteDirecto\"]');
            if (delBtn) {
                delBtn.click();
            } else {
                var row = document.querySelector('#tablaCliente tbody tr');
                if (row && typeof window.eliminarClienteDirecto === 'function') {
                    window.eliminarClienteDirecto(row);
                }
            }
        ");

        // Diálogo SweetAlert2 de confirmación ("Se eliminará al Cliente")
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar notificación de éxito tras completarse la petición AJAX
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('23_cliente_eliminado_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar que ya no exista en la tabla
        $I->fillField('input[type="search"]', $cedulaEliminar);
        $I->wait(1);
        $I->dontSee($cedulaEliminar, '#tablaCliente tbody');
    }
}
