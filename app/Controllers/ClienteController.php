<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Cliente;

Helper::verificarSesion();

$clienteModel = new Cliente();
if (isset($_POST["peticion"])) {



    //Entrada
    if ($_POST["peticion"] == "entrada") {
        $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
        $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    }










    //Registrar y Modificar
    if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar" || $_POST["peticion"] == "eliminar") {
        $accion_permiso = true;

        if ($accion_permiso) {
            try {
                $clienteModel->setCedula($_POST["cedula"] ?? '');
                if ($_POST["peticion"] != "eliminar") {
                    $clienteModel->setNombre($_POST["nombre"] ?? '');
                    $clienteModel->setApellido($_POST["apellido"] ?? '');
                    $clienteModel->setFechaNacimiento($_POST["fecha_nacimiento"] ?? '');
                    $clienteModel->setTelefono($_POST["telefono"] ?? '');
                    $clienteModel->setCorreo($_POST["correo"] ?? '');
                    $clienteModel->setDireccion($_POST["direccion"] ?? '');
                    $clienteModel->setSexo($_POST["sexo"] ?? '');
                }

                if ($_POST["peticion"] == "registrar") {
                    $msgN = "Se registró un nuevo cliente con la cédula " . ($_POST["cedula"] ?? '');
                } else if ($_POST["peticion"] == "modificar") {
                    $msgN = "Se modificó el cliente con la cédula: " . ($_POST["cedula"] ?? '');
                } else {
                    $msgN = "Se eliminó el cliente con la cédula: " . ($_POST["cedula"] ?? '');
                }

                $json = $clienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
                
                if (isset($json['estado']) && $json['estado'] == 1) {
                    Helper::Bitacora(strtoupper($_POST["peticion"]), "CLIENTES", $msgN);
                }

            } catch (\Exception $e) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response']    = ['resultado' => 400, 'mensaje' => $e->getMessage()];
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response']    = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para realizar esta acción'];
        }
    }
    //Fin del Registrar o Modificar
        





    //Consultar
    if ($_POST["peticion"] == "consultar") {
        $json = $clienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
    }
    //Fin del Consultar









    // Verificar cédula duplicada (validación async desde frontend)
    if ($_POST["peticion"] == "verificar_cedula") {
        $cedula = trim($_POST["cedula"] ?? '');
        if (!empty($cedula)) {
            try {
                $clienteModel->setCedula($cedula);
                $resultado = $clienteModel->Transaccion(['peticion' => 'verificar_cedula']);
                $json = $resultado;
            } catch (\Exception $e) {
                // Si el formato de la cédula no es válido, simplemente no existe
                $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                $json['response']    = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
            $json['response']    = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
        }
    }
    // Fin de Verificar cédula duplicada













    if ($_POST["peticion"] == "cambiar_estatus") {
        $accion_permiso = true;

        if ($accion_permiso) {
            $bool_formulario = true;
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            
            //Validar Cédula del formulario
            if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0 || !isset($_POST["estatus"])) {
                $json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Cédula o Estatus no válida'];
                $bool_formulario = false;
            }
            //Fin de la Validación

            if ($bool_formulario) {
                $clienteModel->setCedula($_POST["cedula"]);
                $clienteModel->setEstatus($_POST["estatus"]);
                $json = $clienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
                if (isset($json['estado']) && $json['estado'] == 1) {
                    $accion_texto = ($_POST["estatus"] == 1) ? "activó" : "desactivó";
                    Helper::Bitacora("CAMBIAR_ESTATUS", "CLIENTES", "Se {$accion_texto} al cliente con cédula: " . $_POST["cedula"]);
                }
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' un cliente'];
        }
    }
    //Fin del Cambiar Estatus


    
    //Enviar respuesta al navegador usando un encabezado HTTP
    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
    echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
    exit;
} //Fin de Operaciones

Helper::cargarVista(
    'cliente/index',
    'Clientes - Good Vibes'
);
