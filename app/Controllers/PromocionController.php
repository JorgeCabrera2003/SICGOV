<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Producto;
use App\Models\System\Promocion;
use Exception;

Helper::verificarSesion();

$promocionModel = new Promocion();
$productoModel = new Producto();
$permisosPromocion = Helper::TraerPermisos('promocion');
$tienePermisoPromocion = static function (string $accion) use ($permisosPromocion): bool {
    return ($permisosPromocion['promocion'][$accion] ?? 0) == 1;
};
$productos = $productoModel->Transaccion(['peticion' => 'listar']) ?: [];

if (isset($_POST["peticion"])) {

    //Entrada
    if ($_POST["peticion"] == "entrada") {
        $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
        $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    }

    //Registrar y Modificar
    if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {

        $accion_permiso = $tienePermisoPromocion($_POST["peticion"]);

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
                $promocionModel->setImagen($_POST["imagen_galeria"] ?? '');

                $json = $promocionModel->Transaccion(['peticion' => $_POST["peticion"]]);
                if (isset($json['estado']) && $json['estado'] == 1) {
                    $msg = "(" . $_SESSION['user']['cedula'] . "), Se " . ($str_mensaje ?? '') . " una promoción: " . $promocionModel->getNombre();
                    Helper::Bitacora(
                        $_POST["peticion"] == "registrar" ? 'REGISTRAR' : 'MODIFICAR',
                        'PROMOCION',
                        $msg
                    );
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
        if ($tienePermisoPromocion('ver')) {
            $json = $promocionModel->Transaccion(['peticion' => $_POST["peticion"]]);
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para consultar promociones', 'datos' => []];
        }
    }

    //Eliminar
    if ($_POST["peticion"] == "eliminar") {
        $accion_permiso = $tienePermisoPromocion('eliminar');

        if ($accion_permiso) {
            $bool_formulario = true;
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
            try {
                if ($bool_formulario) {
                    $promocionModel->setIdPromocion($_POST["id_promocion"] ?? '');
                    $json = $promocionModel->Transaccion(['peticion' => $_POST["peticion"]]);
                    if (isset($json['estado']) && $json['estado'] == 1) {
                        Helper::Bitacora('ELIMINAR', 'PROMOCION', "Se eliminó la promoción ID: " . ($_POST["id_promocion"] ?? ''));
                    }
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

    // Crear Noticia a partir de Promoción
    if ($_POST["peticion"] == "crear_noticia") {
        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
        try {
            $idPromocion = $_POST["id_promocion"] ?? '';
            if (empty($idPromocion)) {
                throw new Exception('ID de promoción requerido.');
            }

            $promocionModel->setIdPromocion($idPromocion);
            $valPromo = $promocionModel->Transaccion(['peticion' => 'validar']);
            if (($valPromo['bool'] ?? 0) != 1) {
                throw new Exception('La promoción indicada no existe.');
            }
            $promoData = $valPromo['response']['registro'];

            // Obtener productos asociados a la promoción
            $dbBusiness = \App\Core\Database::getConnection('business');
            $sqlProd = "SELECT p.nombre_producto, p.precio, pp.cantidad
                        FROM (
                            SELECT id_producto, COUNT(*) as cantidad 
                            FROM planificador_promocion 
                            WHERE id_promocion = :id_promocion 
                            GROUP BY id_producto
                        ) pp
                        JOIN producto p ON pp.id_producto = p.id_producto";
            $stmProd = $dbBusiness->prepare($sqlProd);
            $stmProd->execute([':id_promocion' => $idPromocion]);
            $listaProductos = $stmProd->fetchAll(\PDO::FETCH_ASSOC);

            // Armar texto de descuento
            $descuentoTexto = ($promoData['tipo_descuento'] === 'PORCENTAJE')
                ? number_format((float)$promoData['valor_descuento'], 2, ',', '.') . "%"
                : "$" . number_format((float)$promoData['valor_descuento'], 2, ',', '.');

            // Armar lista de productos
            $prodListadoTexto = "";
            $subtotalOriginal = 0;
            foreach ($listaProductos as $lp) {
                $subtotalOriginal += ((float)$lp['precio'] * (int)$lp['cantidad']);
                $prodListadoTexto .= "• " . $lp['nombre_producto'] . " (Cant: " . $lp['cantidad'] . " - Ref: $" . number_format((float)$lp['precio'], 2, ',', '.') . ")\n";
            }

            // Calcular ahorro
            if ($promoData['tipo_descuento'] === 'PORCENTAJE') {
                $montoDesc = $subtotalOriginal * ((float)$promoData['valor_descuento'] / 100);
            } else {
                $montoDesc = min($subtotalOriginal, (float)$promoData['valor_descuento']);
            }
            $totalPromocional = max(0, $subtotalOriginal - $montoDesc);

            // Formatear fechas
            $fechaIni = date('d/m/Y', strtotime($promoData['fecha_inicio']));
            $fechaFin = !empty($promoData['fecha_fin']) ? date('d/m/Y', strtotime($promoData['fecha_fin'])) : 'Hasta agotar existencia';

            // Armar contenido redactado
            $contenidoNoticia = "¡Aprovecha nuestra súper promoción especial en Good Vibes!\n\n"
                . "🏷️ Oferta: " . $promoData['nombre'] . "\n"
                . "💥 Descuento exclusivo: " . $descuentoTexto . " OFF\n"
                . "📅 Vigencia: Desde " . $fechaIni . " hasta " . $fechaFin . "\n\n";

            if (!empty($promoData['descripcion'])) {
                $contenidoNoticia .= "📝 Detalle:\n" . $promoData['descripcion'] . "\n\n";
            }

            if (!empty($prodListadoTexto)) {
                $contenidoNoticia .= "🍽️ Productos incluidos en la promoción:\n" . $prodListadoTexto . "\n";
                $contenidoNoticia .= "💵 Subtotal referencial: $" . number_format($subtotalOriginal, 2, ',', '.') . "\n";
                $contenidoNoticia .= "🔥 Precio especial con descuento: $" . number_format($totalPromocional, 2, ',', '.') . "\n\n";
            }

            $contenidoNoticia .= "¡No te quedes sin disfrutarla! Visítanos en nuestro local o solicita tu pedido en línea.";

            // Título limpio para validación Regex (Titulo: 3-150 caracteres)
            $tituloNoticia = "¡Promoción: " . trim($promoData['nombre']) . "!";
            if (mb_strlen($tituloNoticia) > 95) {
                $tituloNoticia = mb_substr($tituloNoticia, 0, 92) . "...!";
            }

            // Subtítulo
            $subtituloNoticia = "Aprovecha " . $descuentoTexto . " de descuento disponible por tiempo limitado.";
            if (mb_strlen($subtituloNoticia) > 145) {
                $subtituloNoticia = mb_substr($subtituloNoticia, 0, 142) . "...";
            }

            // Instanciar modelo Noticia
            $cedulaUser = $_SESSION['user']['cedula'] ?? '';
            if (empty($cedulaUser)) {
                $dbSec = \App\Core\Database::getConnection('security');
                $stmtAdmin = $dbSec->query("SELECT cedula FROM usuario LIMIT 1");
                $cedulaUser = $stmtAdmin ? $stmtAdmin->fetchColumn() : '';
            }

            $noticiaModel = new \App\Models\Security\Noticia();
            $idNoticia = Helper::generarId("NOTC");
            $noticiaModel->setId($idNoticia);
            $noticiaModel->setCedula($cedulaUser);
            $noticiaModel->setTitulo($tituloNoticia);
            $noticiaModel->setSubtitulo($subtituloNoticia);
            $noticiaModel->setContenido($contenidoNoticia);
            $noticiaModel->setTipo('INFO');
            $noticiaModel->setFechaPublicacion(date('Y-m-d H:i:s'));

            // Si la promoción tiene imagen, asignarla a la noticia
            if (!empty($promoData['imagen'])) {
                $noticiaModel->setImagenesGaleria([$promoData['imagen']]);
            }

            $resNoticia = $noticiaModel->Transaccion(['peticion' => 'registrar']);

            if (isset($resNoticia['estado']) && $resNoticia['estado'] == 1) {
                Helper::Bitacora('REGISTRAR', 'NOTICIA', "Se creó noticia automática {$idNoticia} a partir de la promoción {$promoData['nombre']}");
                $json['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'OK'];
                $json['response'] = [
                    'resultado' => 201,
                    'icon' => 'success',
                    'mensaje' => "¡Noticia creada y publicada exitosamente en el Blog!",
                    'id_noticia' => $idNoticia
                ];
            } else {
                throw new Exception($resNoticia['response']['mensaje'] ?? "No se pudo registrar la noticia");
            }
        } catch (Exception $e) {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error al crear noticia'];
            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    //Enviar respuesta al navegador usando un encabezado HTTP
    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
    echo json_encode($json['response']);
    exit;
}

if (!$tienePermisoPromocion('ver')) {
    header('Location: ' . BASE_URL . '?page=Dashboard');
    exit;
}

Helper::cargarVista(
    'promocion/index',
    'Promociones - Good Vibes',
    [
        'productos' => $productos,
        'ver' => $permisosPromocion['promocion']['ver'] ?? 0,
        'permisosPromocion' => $permisosPromocion
    ]
);
