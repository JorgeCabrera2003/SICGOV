<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

/**
 * Suite de Aceptación / Interfaz para el Módulo de Empleados
 */
class EmpleadoCest
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

    /**
     * Asegura que la sesión esté activa, navegando a la página de login si es necesario.
     */
    private function autenticarSiEsNecesario(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        if (!empty($this->cookieSession)) {
            $I->setCookie('PHPSESSID', $this->cookieSession);
        }
        $I->amOnPage('/?page=Empleado');
        $I->waitForElementVisible('body', 5);

        // Si la sesión no fue válida y redirigió a Login, autenticarse por formulario
        $urlActual = $I->grabFromCurrentUrl();
        if (str_contains($urlActual, 'Login')) {
            $I->fillField('input[name="CI"]', '00000000');
            $I->fillField('input[name="password"]', '1234');
            $I->click('button[name="peticion"][value="sesion"]');
            $I->waitForElementVisible('body', 5);
            $I->amOnPage('/?page=Empleado');
            $I->waitForElementVisible('body', 5);
        }
    }

    /**
     * Prueba la navegación al panel principal y apertura del modal.
     */
    public function probarPanelPrincipalYModalEmpleado(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Empleados');
        $I->makeScreenshot('16_empleado_panel_principal');

        // Modal de Registro Empleado
        $I->waitForElementVisible('#btnNuevoEmpleado', 5);
        $I->click('#btnNuevoEmpleado');
        $I->waitForElementVisible('#modalEmpleado', 5);
        $I->wait(1);
        $I->makeScreenshot('17_empleado_modal_registro');

        // Cerrar modal para no interferir con pruebas posteriores
        $I->click('#modalEmpleado .btn-close');
        $I->waitForElementNotVisible('#modalEmpleado', 5);
    }

    /**
     * 1. REGISTRAR: Flujo completo y automatizado para registrar un nuevo empleado.
     */
    public function registrarEmpleadoConDatosValidos(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Empleados');

        // Abrir el modal de registro
        $I->waitForElementVisible('#btnNuevoEmpleado', 5);
        $I->click('#btnNuevoEmpleado');
        $I->waitForElementVisible('#modalEmpleado', 5);
        $I->wait(1);

        // Generar datos únicos y válidos para la prueba
        $cedulaNum = (string) rand(20000000, 29999999);
        $nombre = 'Carlos';
        $apellido = 'Perez';
        $telefono = (string) rand(1000000, 9999999);
        $correo = 'carlos.' . $cedulaNum . '@test.com';
        $direccion = 'Avenida Libertador con Calle 42';

        // Llenar campos requeridos
        $I->selectOption('#tipo_doc', 'V');
        $I->fillField('#cedula', $cedulaNum);
        $I->fillField('#fecha_nacimiento', '1995-06-15');
        $I->fillField('#nombre', $nombre);
        $I->fillField('#apellido', $apellido);

        // Seleccionar primer cargo disponible del sistema
        $I->executeJS("
            var selectCargo = document.getElementById('id_cargo');
            if (selectCargo && selectCargo.options.length > 1) {
                for (var i = 1; i < selectCargo.options.length; i++) {
                    if (selectCargo.options[i].value && selectCargo.options[i].value !== 'default') {
                        selectCargo.selectedIndex = i;
                        $(selectCargo).data('touched', true).trigger('change');
                        break;
                    }
                }
            }
        ");

        $I->selectOption('#sexo', 'M');
        $I->selectOption('#prefijo_telefono', '0414');
        $I->fillField('#telefono', $telefono);
        $I->fillField('#correo', $correo);
        $I->fillField('#direccion', $direccion);

        // Disparar eventos de validación en tiempo real para activar el botón
        $I->executeJS("
            $('#cedula, #nombre, #apellido, #telefono, #correo, #direccion').trigger('input').trigger('blur');
            $('#tipo_doc, #sexo, #prefijo_telefono, #id_cargo, #fecha_nacimiento').trigger('change');
        ");
        $I->wait(1);

        // Hacer clic en Guardar Empleado
        $I->waitForElementClickable('#btnEmpleadoForm', 5);
        $I->click('#btnEmpleadoForm');

        // Confirmar la acción en el diálogo de SweetAlert2
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // El modal debe ocultarse al completarse la petición AJAX
        $I->waitForElementNotVisible('#modalEmpleado', 10);

        // Confirmar la notificación de éxito tras la respuesta AJAX
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('18_empleado_registrado_exitoso');

        // Cerrar alerta si sigue visible
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar persistencia visual en la tabla DataTables
        $I->fillField('input[type="search"]', $cedulaNum);
        $I->wait(1);
        $I->waitForText($nombre, 5);
        $I->see($apellido);
    }

    /**
     * 2. MODIFICAR: Flujo completo y automatizado para editar los datos de un empleado.
     */
    public function modificarEmpleadoConDatosValidos(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Empleados');
        $I->waitForElementVisible('#tablaEmpleado tbody tr', 5);

        // Disparar la acción 'Editar' mediante rellenar(pos, 0) sobre la primera fila
        $I->executeJS("
            var editBtn = document.querySelector('#tablaEmpleado tbody tr a[onclick*=\"rellenar\"]');
            if (editBtn) {
                editBtn.click();
            } else {
                var row = document.querySelector('#tablaEmpleado tbody tr');
                if (row && typeof window.rellenar === 'function') {
                    window.rellenar(row, 0);
                }
            }
        ");

        // Esperar que el modal se muestre en modo edición
        $I->waitForElementVisible('#modalEmpleado', 5);
        $I->see('Actualizar Empleado');

        // Modificar valores con formato de título normal (capitalizado)
        $nuevoNombre = 'Alberto';
        $nuevoApellido = 'Rojas';
        $nuevaDireccion = 'Avenida 20 con Calle 33 Sector Centro';

        $I->fillField('#nombre', $nuevoNombre);
        $I->fillField('#apellido', $nuevoApellido);
        $I->fillField('#direccion', $nuevaDireccion);

        // Disparar eventos de validación
        $I->executeJS("
            $('#nombre, #apellido, #direccion').trigger('input').trigger('blur');
        ");
        $I->wait(1);

        // Hacer clic en 'Actualizar Empleado'
        $I->waitForElementClickable('#btnEmpleadoForm', 5);
        $I->click('#btnEmpleadoForm');

        // Confirmar la acción en SweetAlert2
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar que el modal se cierre
        $I->waitForElementNotVisible('#modalEmpleado', 10);

        // Confirmar notificación de éxito
        $I->waitForElementVisible('.swal2-popup', 10);
        $I->see('Éxito');
        $I->makeScreenshot('20_empleado_modificado_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar que los datos modificados se reflejen en la tabla
        $I->fillField('input[type="search"]', $nuevoNombre);
        $I->wait(1);
        $I->waitForText($nuevoNombre, 5);
        $I->see($nuevoApellido);
    }

    /**
     * 3. ELIMINAR: Flujo completo y automatizado para eliminar un empleado del sistema.
     */
    public function eliminarEmpleado(AcceptanceTester $I): void
    {
        $this->autenticarSiEsNecesario($I);

        $I->see('Empleados');

        // Primero registrar un empleado exclusivo para eliminar (asegura prueba 100% independiente)
        $I->waitForElementVisible('#btnNuevoEmpleado', 5);
        $I->click('#btnNuevoEmpleado');
        $I->waitForElementVisible('#modalEmpleado', 5);

        $cedulaEliminar = (string) rand(30000000, 39999999);
        $I->selectOption('#tipo_doc', 'V');
        $I->fillField('#cedula', $cedulaEliminar);
        $I->fillField('#fecha_nacimiento', '1992-04-10');
        $I->fillField('#nombre', 'Eliminar');
        $I->fillField('#apellido', 'Temporal');

        $I->executeJS("
            var selectCargo = document.getElementById('id_cargo');
            if (selectCargo && selectCargo.options.length > 1) {
                for (var i = 1; i < selectCargo.options.length; i++) {
                    if (selectCargo.options[i].value && selectCargo.options[i].value !== 'default') {
                        selectCargo.selectedIndex = i;
                        $(selectCargo).data('touched', true).trigger('change');
                        break;
                    }
                }
            }
        ");

        $I->selectOption('#sexo', 'M');
        $I->selectOption('#prefijo_telefono', '0414');
        $I->fillField('#telefono', '9998877');
        $I->fillField('#correo', 'eliminar.' . $cedulaEliminar . '@test.com');
        $I->fillField('#direccion', 'Direccion Temporal Para Borrar');

        $I->executeJS("
            $('#cedula, #nombre, #apellido, #telefono, #correo, #direccion').trigger('input').trigger('blur');
            $('#tipo_doc, #sexo, #prefijo_telefono, #id_cargo, #fecha_nacimiento').trigger('change');
        ");
        $I->wait(1);

        $I->waitForElementClickable('#btnEmpleadoForm', 5);
        $I->click('#btnEmpleadoForm');
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');
        $I->waitForElementNotVisible('#modalEmpleado', 10);

        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Ahora buscar al empleado recién creado en la tabla
        $I->fillField('input[type="search"]', $cedulaEliminar);
        $I->wait(1);
        $I->waitForText($cedulaEliminar, 5);

        // Disparar la acción directa de eliminar
        $I->executeJS("
            var delBtn = document.querySelector('#tablaEmpleado tbody tr a[onclick*=\"eliminarEmpleadoDirecto\"]');
            if (delBtn) {
                delBtn.click();
            } else {
                var row = document.querySelector('#tablaEmpleado tbody tr');
                if (row && typeof window.eliminarEmpleadoDirecto === 'function') {
                    window.eliminarEmpleadoDirecto(row);
                }
            }
        ");

        // Diálogo SweetAlert2 de confirmación ("Se eliminará al Empleado")
        $I->waitForElementVisible('.swal2-confirm', 5);
        $I->click('.swal2-confirm');

        // Esperar notificación de éxito tras completarse la petición AJAX
        $I->waitForText('Éxito', 10);
        $I->makeScreenshot('21_empleado_eliminado_exitoso');

        // Cerrar alerta
        $I->executeJS("if (typeof Swal !== 'undefined' && Swal.isVisible()) { Swal.close(); }");
        $I->wait(1);

        // Verificar que el empleado eliminado ya no exista en la tabla
        $I->fillField('input[type="search"]', $cedulaEliminar);
        $I->wait(1);
        $I->dontSee($cedulaEliminar, '#tablaEmpleado tbody');
    }
}
