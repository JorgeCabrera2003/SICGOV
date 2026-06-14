<?php

/*
MODELO DE PROMOCIONES

OPERACIONES A BASE DE DATOS:
    REGISTRAR
    CONSULTAR
    MODIFICAR
    ELIMINAR
    VALIDAR
*/

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Promocion extends Database 
{
    private $id_promocion;
    private $nombre;
    private $tipo_descuento;
    private $valor_descuento;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $hora_inicio;
    private $hora_fin;

    private $id_producto;
    private $productos;

    public function __construct() {

        $this->id_promocion = "";
        $this->nombre = "";
        $this->tipo_descuento = "";
        $this->valor_descuento = 0.0;
        $this->descripcion = "";
        $this->fecha_inicio = "";
        $this->fecha_fin = "";
        $this->hora_inicio = "";
        $this->hora_fin = "";
        $this->id_producto = "";
        $this->productos = [];

    }

    //SETTERS
     public function setIdPromocion(string $id_promocion) {
        $this->id_promocion = $id_promocion;
    }

    public function setNombre(string $nombre) {
        $this->nombre = $nombre;
    }

    public function setTipoDescuento(string $tipo_descuento) {
        $this->tipo_descuento = $tipo_descuento;
    }

    public function setValorDescuento(string $valor_descuento) {
        $valor_descuento = preg_replace('/[^0-9,\.]/', '', $valor_descuento);
        $valor_descuento = str_replace(',', '.', $valor_descuento);
        $this->valor_descuento = floatval($valor_descuento);
    }

    public function setDescripcion(string $descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setFechaInicio(string $fecha_inicio) {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function setFechaFin(string $fecha_fin) {
        $this->fecha_fin = $fecha_fin;
    }

    public function setHoraInicio(string $hora_inicio) {
        $this->hora_inicio = $hora_inicio;
    }

    public function setHoraFin(string $hora_fin) {
        $this->hora_fin = $hora_fin;
    }


    public function setIdProducto(string $id_producto) {
        $this->id_producto = $id_producto;
    }

    public function setProductos(array $productos) {
        $this->productos = $productos;
    }
    //FIN SETTERS

    //GETTERS 
    public function getIdPromocion() {
        return $this->id_promocion;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getTipoDescuento() {
        return $this->tipo_descuento;
    }

    public function getValorDescuento() {
        return $this->valor_descuento;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getFechaInicio() {
        return $this->fecha_inicio;
    }

    public function getFechaFin() {
        return $this->fecha_fin;
    }

    public function getHoraInicio() {
        return $this->hora_inicio;
    }

    public function getHoraFin() {
        return $this->hora_fin;
    }


    public function getIdProducto() {
        return $this->id_producto;
    }
    //FIN GETTERS

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion) {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarPromocion(),
                'consultar' => $this->ConsultarPromocion(),
                'actualizar', 'modificar' => $this->ModificarPromocion(),
                'eliminar' => $this->EliminarPromocion(),
                'validar' => $this->ValidarPromocion(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }
    //FIN DE MANEJADOR DE OPERACIONES

    //OPERACIONES A BASE DE DATOS
    private function ConsultarPromocion()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT pr.*,
                    GROUP_CONCAT(DISTINCT p.nombre_producto ORDER BY p.nombre_producto SEPARATOR ', ') AS producto_nombre,
                    GROUP_CONCAT(DISTINCT p.id_producto ORDER BY p.nombre_producto SEPARATOR ',') AS producto_ids,
                    GROUP_CONCAT(CONCAT(p.id_producto, ':::', REPLACE(p.nombre_producto, ',', ' '), ':::', pp.cantidad, ':::', COALESCE(p.precio, 0)) ORDER BY p.nombre_producto SEPARATOR '||') AS producto_list
                    FROM promocion pr
                    LEFT JOIN (
                        SELECT id_promocion, id_producto, COUNT(*) AS cantidad
                        FROM planificador_promocion
                        GROUP BY id_promocion, id_producto
                    ) pp ON pr.id_promocion = pp.id_promocion
                    LEFT JOIN producto p ON pp.id_producto = p.id_producto
                    GROUP BY pr.id_promocion";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarPromocion()
    {
        $dato = [];
        try {
            $sql = "INSERT INTO promocion (id_promocion, nombre, tipo_descuento, valor_descuento, descripcion, fecha_inicio, fecha_fin, hora_inicio, hora_fin)
                VALUES (:id_promocion, :nombre, :tipo_descuento, :valor_descuento, :descripcion, :fecha_inicio, :fecha_fin, :hora_inicio, :hora_fin)";

            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_promocion', $this->id_promocion);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':tipo_descuento', $this->tipo_descuento);
            $stm->bindParam(':valor_descuento', $this->valor_descuento);
            $stm->bindParam(':descripcion', $this->descripcion);
            $stm->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stm->bindParam(':fecha_fin', $this->fecha_fin);
            $stm->bindParam(':hora_inicio', $this->hora_inicio);
            $stm->bindParam(':hora_fin', $this->hora_fin);
            $stm->execute();

            if (!empty($this->productos)) {
                $sqlPlan = "INSERT INTO planificador_promocion (id_planificador, id_producto, id_promocion)\n                    VALUES (:id_planificador, :id_producto, :id_promocion)";
                $stmPlan = $this->LlamarConexion()->prepare($sqlPlan);
                $planCounter = 0;
                foreach ($this->productos as $producto) {
                    $idProducto = null;
                    $cantidad = 1;
                    if (is_array($producto)) {
                        $idProducto = $producto['id'] ?? $producto[0] ?? null;
                        $cantidad = intval($producto['cantidad'] ?? 1);
                    } else {
                        $idProducto = $producto;
                    }
                    if (!$idProducto) {
                        continue;
                    }
                    if ($cantidad < 1) {
                        $cantidad = 1;
                    }
                    for ($j = 0; $j < $cantidad; $j++) {
                        // Asegurar ID único pasando un contador incremental a generarId
                        $idPlanificador = Helper::generarId('PLAN', null, $planCounter++);
                        $stmPlan->execute([
                            ':id_planificador' => $idPlanificador,
                            ':id_producto' => $idProducto,
                            ':id_promocion' => $this->id_promocion
                        ]);
                    }
                }
            }

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Promoción registrada exitosamente", 'id_promocion' => $this->id_promocion];
            $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarPromocion()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE promocion SET nombre = :nombre, tipo_descuento = :tipo_descuento, valor_descuento = :valor_descuento, descripcion = :descripcion,
            fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, hora_inicio = :hora_inicio, hora_fin = :hora_fin
            WHERE id_promocion = :id_promocion";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_promocion', $this->id_promocion);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':tipo_descuento', $this->tipo_descuento);
            $stm->bindParam(':valor_descuento', $this->valor_descuento);
            $stm->bindParam(':descripcion', $this->descripcion);
            $stm->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stm->bindParam(':fecha_fin', $this->fecha_fin);
            $stm->bindParam(':hora_inicio', $this->hora_inicio);
            $stm->bindParam(':hora_fin', $this->hora_fin);
            $stm->execute();

            $deletePlan = "DELETE FROM planificador_promocion WHERE id_promocion = :id_promocion";
            $stmDelete = $this->LlamarConexion()->prepare($deletePlan);
            $stmDelete->bindParam(':id_promocion', $this->id_promocion);
            $stmDelete->execute();

            if (!empty($this->productos)) {
                $sqlPlan = "INSERT INTO planificador_promocion (id_planificador, id_producto, id_promocion)\n                    VALUES (:id_planificador, :id_producto, :id_promocion)";
                $stmPlan = $this->LlamarConexion()->prepare($sqlPlan);
                $planCounter = 0;
                foreach ($this->productos as $producto) {
                    $idProducto = null;
                    $cantidad = 1;
                    if (is_array($producto)) {
                        $idProducto = $producto['id'] ?? $producto[0] ?? null;
                        $cantidad = intval($producto['cantidad'] ?? 1);
                    } else {
                        $idProducto = $producto;
                    }
                    if (!$idProducto) {
                        continue;
                    }
                    if ($cantidad < 1) {
                        $cantidad = 1;
                    }
                    for ($i = 0; $i < $cantidad; $i++) {
                        $idPlanificador = Helper::generarId('PLAN', null, $planCounter++);
                        $stmPlan->execute([
                            ':id_planificador' => $idPlanificador,
                            ':id_producto' => $idProducto,
                            ':id_promocion' => $this->id_promocion
                        ]);
                    }
                }
            }

            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Promoción actualizada exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarPromocion()
    {
        $dato = [];
        $validacion = $this->ValidarPromocion();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "DELETE FROM promocion WHERE id_promocion = :id_promocion";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_promocion', $this->id_promocion);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Promoción eliminada exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } catch (\PDOException $e) {
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 404, 'icon' => 'error', 'mensaje' => "Registro no encontrado"];
            $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => "No encontrado"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarPromocion()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM promocion WHERE id_promocion = :id_promocion";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_promocion', $this->id_promocion);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;

            } else {
                $dato['bool'] = 0;
            }
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['bool'] = -1;
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

}
