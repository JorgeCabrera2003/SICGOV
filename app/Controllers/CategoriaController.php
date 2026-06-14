<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\CategoriaProducto;

$peticion = $_POST['peticion'] ?? $_POST['action'] ?? $_GET['action'] ?? '';


Helper::verificarSesion();

if (empty($peticion) && !isset($_POST["peticion"])) {
    Helper::cargarVista(
        'categoria/index',
        'Categorías - Good Vibes'
    );
    exit;
}

$permisosCategoria = Helper::TraerPermisos("categoria_menu");

$categoriaModel = new CategoriaProducto();


if ($peticion == "entrada") {
    $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
    $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    header("Content-Type: application/json");
    header("HTTP/1.1 204 No Content");
    echo json_encode($json['response']);
    exit;
}







if ($peticion == "listar" || $peticion == "consultar") {
    header('Content-Type: application/json');
    
    $accion_permiso = false;
    if (isset($permisosCategoria['categoria_menu']['ver']) && $permisosCategoria['categoria_menu']['ver'] == 1) {
        $accion_permiso = true;
    }
    
    if (!$accion_permiso) {
        if ($peticion == "listar") {
            echo json_encode([]);
            exit;
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'datos' => []];
            header("HTTP/1.1 403 Acción no autorizada");
            echo json_encode($json['response']);
            exit;
        }
    }

    if ($peticion == "listar") {
        try {
            if (!Helper::verificarSesion()) {
                echo json_encode([]);
                exit();
            }
            $categorias = $categoriaModel->Transaccion(['peticion' => 'listar']);
            echo json_encode($categorias ?: []);
        } catch (\Exception $e) {
            error_log("Error en listar categorías: " . $e->getMessage());
            echo json_encode([]);
        }
    } else {
        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => ''];
        $json['response'] = $categoriaModel->Transaccion(['peticion' => 'consultar']);
        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje']);
        echo json_encode($json['response']);
    }
    exit;
}








if ($peticion == "verificar") {
    try {
        $nombre = trim($_POST["nombre_categoria"] ?? '');
        $id_excluir = trim($_POST["id_categoria"] ?? '');

        if (empty($nombre)) {
            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
            $json['response'] = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
        } else {
            $categoriaModel->setNombreCategoria($nombre);
            $jsonResult = $categoriaModel->Transaccion([
                'peticion'   => 'verificar',
                'id_excluir' => $id_excluir ?: null
            ]);
            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
            $json['response'] = [
                'resultado' => 200,
                'existe'    => $jsonResult['existe'],
                'mensaje'   => $jsonResult['message']
            ];
        }
    } catch (\Exception $e) {
       
        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        $json['response'] = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
    }
    header("Content-Type: application/json");
    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje']);
    echo json_encode($json['response']);
    exit;
}









if ($peticion == "guardar" || $peticion == "registrar" || $peticion == "modificar") {
    header('Content-Type: application/json');
    $accion_permiso = false; 
    
    if (($peticion == "guardar" || $peticion == "registrar") && isset($permisosCategoria['categoria_menu']['registrar']) && $permisosCategoria['categoria_menu']['registrar'] == 1) {
        $accion_permiso = true;
    } elseif ($peticion == "modificar" && isset($permisosCategoria['categoria_menu']['modificar']) && $permisosCategoria['categoria_menu']['modificar'] == 1) {
        $accion_permiso = true;
    }

    if ($accion_permiso) {
        try {
            $nombre_cat = $_POST["nombre_categoria"] ?? $_POST['nombre'] ?? '';
            $categoriaModel->setNombreCategoria($nombre_cat);
            
            if ($peticion == "modificar") {
                $categoriaModel->setIdCategoria($_POST["id_categoria"] ?? '');
                $categoriaModel->setEstatus(1); 
                $jsonResult = $categoriaModel->Transaccion(['peticion' => 'actualizar']);
            } else {
                $categoriaModel->setEstatus(1);
                $jsonResult = $categoriaModel->Transaccion(['peticion' => 'guardar']);
            }
            
            $success = (isset($jsonResult['success']) && $jsonResult['success']) || (isset($jsonResult['estado']) && $jsonResult['estado'] == 1);
            
            if ($success) {
                if ($peticion == "modificar") {
                    Helper::Bitacora("MODIFICAR", "CATEGORIAS", "Se modificó la categoría ID: " . ($_POST['id_categoria'] ?? ''));
                } else {
                    Helper::Bitacora("REGISTRAR", "CATEGORIAS", "Se registró la categoría: " . $nombre_cat);
                }
            }

            if ($peticion == "guardar") {
                
                echo json_encode($jsonResult);
            } else {
                if ($success) {
                    $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                    $json['response'] = ['resultado' => 200, 'mensaje' => $jsonResult['message'] ?? ''];
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'OK'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $jsonResult['message'] ?? 'Error desconocido'];
                }
                header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje']);
                echo json_encode($json['response']);
            }
        } catch (\Exception $e) {
            if ($peticion == "guardar") {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            } else {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                header("HTTP/1.1 400 Datos no válidos");
                echo json_encode($json['response']);
            }
        }
    } else {
        $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
        $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para esto'];
        header("HTTP/1.1 403 Acción no autorizada");
        echo json_encode($json['response']);
    }
    exit;
}












if ($peticion == "eliminar") {
    header('Content-Type: application/json');
    $accion_permiso = false;
    if (isset($permisosCategoria['categoria_menu']['eliminar']) && $permisosCategoria['categoria_menu']['eliminar'] == 1) {
        $accion_permiso = true;
    }

    if ($accion_permiso) {
        try {
            $id_categoria = $_POST["id_categoria"] ?? $_POST["id"] ?? '';
            $categoriaModel->setIdCategoria($id_categoria);
            $jsonResult = $categoriaModel->Transaccion(['peticion' => 'eliminar']);
            
            $success = (isset($jsonResult['success']) && $jsonResult['success']) || (isset($jsonResult['estado']) && $jsonResult['estado'] == 1);
            
            if ($success) {
                Helper::Bitacora("ELIMINAR", "CATEGORIAS", "Se eliminó la categoría con ID: " . $id_categoria);
            }

            if (isset($_POST['id']) && !isset($_POST['id_categoria']) && !isset($_POST['peticion'])) {
                
                echo json_encode($jsonResult);
            } else {
                if ($success) {
                    $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                    $json['response'] = ['resultado' => 200, 'mensaje' => $jsonResult['message'] ?? ''];
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'OK'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $jsonResult['message'] ?? 'Error al eliminar'];
                }
                header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje']);
                echo json_encode($json['response']);
            }
        } catch (\Exception $e) {
            if (isset($_POST['id']) && !isset($_POST['id_categoria'])) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            } else {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos mínimos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                header("HTTP/1.1 400 Datos mínimos no válidos");
                echo json_encode($json['response']);
            }
        }
    } else {
        $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
        $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para esto'];
        header("HTTP/1.1 403 Acción no autorizada");
        echo json_encode($json['response']);
    }
    exit;
}





// por si la petición no coincide con ninguna
$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Peticion no valida'];
$json['response'] = ['resultado' => 400, 'mensaje' => 'Petición no válida o no especificada'];
header("Content-Type: application/json");
header("HTTP/1.1 400 Bad Request");
echo json_encode($json['response']);
exit;