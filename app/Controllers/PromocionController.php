<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Producto;
use App\Models\System\Promocion;
use Exception;

Helper::verificarSesion();

$promocionModel = new Promocion();
$productoModel = new Producto();
$productos = $productoModel->Transaccion(['peticion' => 'listar']) ?: [];

if (isset($_POST["peticion"])) {

    //Entrada
    if ($_POST["peticion"] == "entrada") {
        $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
        $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    }

    //Registrar y Modificar
    if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {

        $accion_permiso = true;

        if ($accion_permiso) {
            $bool_formulario = true;
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

            try {
                $str_mensaje = NULL;

                if ($_POST["peticion"] == "registrar") {
                    $str_mensaje = "registró";
                    $promocionModel->setIdPromocion(Helper::generarId('PROM'));
                }

                if ($_POST["peticion"] == "modificar") {
                    $str_mensaje = "modificó";
                    $promocionModel->setIdPromocion($_POST["id_promocion"] ?? '');
                }

                $productoIds = [];
                if (!empty($_POST["productos"])) {
                    $decoded = json_decode($_POST["productos"], true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $producto) {
                            if (is_array($producto) && !empty($producto['id'])) {
                                $cantidad = intval($producto['cantidad'] ?? 1);
                                if ($cantidad < 1) {
                                    $cantidad = 1;
                                }
                                $productoIds[] = ['id' => (string)$producto['id'], 'cantidad' => $cantidad];
                            } elseif (is_string($producto) || is_numeric($producto)) {
                                $productoIds[] = ['id' => (string)$producto, 'cantidad' => 1];
                            }
                        }
                    }
                } elseif (!empty($_POST["id_producto"])) {
                    $productoIds[] = ['id' => $_POST["id_producto"], 'cantidad' => 1];
                }

                if (empty($productoIds)) {
                    throw new Exception('Debe seleccionar al menos un producto para la promoción.');
                }

                $promocionModel->setProductos($productoIds);
                $promocionModel->setNombre($_POST["nombre"] ?? '');
                $promocionModel->setTipoDescuento($_POST["tipo_descuento"] ?? '');
                $promocionModel->setValorDescuento($_POST["valor_descuento"] ?? '');
                $promocionModel->setDescripcion($_POST["descripcion"] ?? '');
                $promocionModel->setFechaInicio($_POST["fecha_inicio"] ?? '');
                $promocionModel->setFechaFin($_POST["fecha_fin"] ?? '');
                $promocionModel->setHoraInicio($_POST["hora_inicio"] ?? '');
                $promocionModel->setHoraFin($_POST["hora_fin"] ?? '');

                $json = $promocionModel->Transaccion(['peticion' => $_POST["peticion"]]);
                if (isset($json['estado']) && $json['estado'] == 1) {
                    $msg = "(" . $_SESSION['user']['cedula'] . "), Se " . ($str_mensaje ?? '') . " una promoción: " . $promocionModel->getNombre();
                } else {
                    $msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " una promoción";
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' una promoción'];
            $msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
        }
    }

    //Consultar
    if ($_POST["peticion"] == "consultar") {
        $json = $promocionModel->Transaccion(['peticion' => $_POST["peticion"]]);
    }

    //Eliminar
    if ($_POST["peticion"] == "eliminar") {
        $accion_permiso = true;

        if ($accion_permiso) {
            $bool_formulario = true;
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
            try {
                if ($bool_formulario) {
                    $promocionModel->setIdPromocion($_POST["id_promocion"] ?? '');
                    $json = $promocionModel->Transaccion(['peticion' => $_POST["peticion"]]);
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' una promoción'];
            $msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
        }
    }

    //Enviar respuesta al navegador usando un encabezado HTTP
    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
    echo json_encode($json['response']);
    exit;
}

Helper::cargarVista(
    'promocion/index',
    'Promociones - Good Vibes',
    compact('productos')
);
