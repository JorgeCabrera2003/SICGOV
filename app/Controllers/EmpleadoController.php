<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Empleado;

Helper::verificarSesion();

$empleadoModel = new Empleado();
if (isset($_POST["peticion"])) {


    if ($_POST["peticion"] == "entrada") {
        $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
        $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    }










    
    if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
        $accion_permiso = true; 

        if ($accion_permiso) {
            try {
                $empleadoModel->set_cedula($_POST["cedula"] ?? '');
                $empleadoModel->setNombre($_POST["nombre"] ?? '');
                $empleadoModel->setApellido($_POST["apellido"] ?? '');
                $empleadoModel->setFechaNacimiento($_POST["fecha_nacimiento"] ?? '');
                $empleadoModel->setTelefono($_POST["telefono"] ?? '');
                $empleadoModel->setCorreo($_POST["correo"] ?? '');
                $empleadoModel->setDireccion($_POST["direccion"] ?? '');
                $empleadoModel->setSexo($_POST["sexo"] ?? '');
                $empleadoModel->setIdCargo($_POST["id_cargo"] ?? '');

                if ($_POST["peticion"] == "registrar") {
                    $msgN = "Se registró un nuevo empleado con la cédula " . ($_POST["cedula"] ?? '');
                } else if ($_POST["peticion"] == "modificar") {
                    $msgN = "Se modificó el empleado con la cédula: " . ($_POST["cedula"] ?? '');
                }

                $json = $empleadoModel->Transaccion(['peticion' => $_POST["peticion"]]);
                
                if (isset($json['estado']) && $json['estado'] == 1) {
                    Helper::Bitacora(strtoupper($_POST["peticion"]), "EMPLEADOS", $msgN);
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
    




  
    if ($_POST["peticion"] == "consultar") {
        $json = $empleadoModel->Transaccion(['peticion' => $_POST["peticion"]]);
    }
    







    
    if ($_POST["peticion"] == "verificar_cedula") {
        $cedula = trim($_POST["cedula"] ?? '');
        if (!empty($cedula)) {
            try {
                $empleadoModel->set_cedula($cedula);
                $resultado = $empleadoModel->Transaccion(['peticion' => 'verificar_cedula']);
                $json = $resultado;
            } catch (\Exception $e) {
                $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                $json['response']    = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
            $json['response']    = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
        }
    }











    if ($_POST["peticion"] == "cambiar_estatus") {
        $accion_permiso = true;

        if ($accion_permiso) {
            $bool_formulario = true;
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            
            
            if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0 || !isset($_POST["estatus"])) {
                $json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Cédula o Estatus no válida'];
                $bool_formulario = false;
            }

            if ($bool_formulario) {
                $empleadoModel->set_cedula($_POST["cedula"]);
                $empleadoModel->setEstatus($_POST["estatus"]);
                $json = $empleadoModel->Transaccion(['peticion' => $_POST["peticion"]]);
                if (isset($json['estado']) && $json['estado'] == 1) {
                    $accion_texto = ($_POST["estatus"] == 1) ? "activó" : "desactivó";
                    Helper::Bitacora("CAMBIAR_ESTATUS", "EMPLEADOS", "Se {$accion_texto} al empleado con cédula: " . $_POST["cedula"]);
                }
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para cambiar estatus'];
        }
    }

    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
    echo json_encode($json['response']);
    exit;
}



Helper::cargarVista(
    'empleado/index',
    'Empleados - Good Vibes'
);
