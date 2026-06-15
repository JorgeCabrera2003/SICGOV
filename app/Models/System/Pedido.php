<?php

namespace App\Models\System;

use App\Core\Database;
use Exception;
use PDO;

class Pedido
{
    private $dbBusiness;
    private $dbSecurity;

    public function __construct()
    {
        $this->dbBusiness = Database::getConnection('business');
        $this->dbSecurity = Database::getConnection('security');
    }

    public function Transaccion($data)
    {
        $peticion = $data['peticion'] ?? '';

        switch ($peticion) {
            case 'listar':
                return $this->listarPedidos();
            case 'buscar':
                return $this->buscarPedido($data['id_pedido']);
            case 'cambiar_estado':
                return $this->cambiarEstado($data['id_pedido'], $data['estado']);
            case 'obtener_comprobante':
                return $this->obtenerComprobante($data['id_pedido']);
            case 'crear_pos':
                return $this->crearPedidoPOS($data['datosCliente'], $data['carrito'], $data['datosPago']);
            default:
                throw new Exception("Petición no válida.");
        }
    }

    private function listarPedidos()
    {
        $sql = "SELECT p.id_pedido, p.fecha_pedido, p.tipo_pedido, p.estado, p.total, p.observacion,
                       c.cedula AS cedula_cliente, per.nombre, per.apellido,
                       pag.id_pago, pag.referencia, mp.nombre AS metodo_pago
                FROM pedido p
                LEFT JOIN cliente c ON p.cedula_cliente = c.cedula
                LEFT JOIN persona per ON c.cedula = per.cedula
                LEFT JOIN pago pag ON p.id_pedido = pag.id_pedido
                LEFT JOIN metodo_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
                ORDER BY p.fecha_pedido DESC";
        $stmt = $this->dbBusiness->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buscarPedido($id_pedido)
    {
        $sql = "SELECT p.*, per.nombre, per.apellido, per.telefono, pag.id_pago, pag.referencia, mp.nombre AS metodo_pago
                FROM pedido p
                LEFT JOIN cliente c ON p.cedula_cliente = c.cedula
                LEFT JOIN persona per ON c.cedula = per.cedula
                LEFT JOIN pago pag ON p.id_pedido = pag.id_pedido
                LEFT JOIN metodo_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
                WHERE p.id_pedido = ?";
        $stmt = $this->dbBusiness->prepare($sql);
        $stmt->execute([$id_pedido]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            $sqlDetalle = "SELECT dp.*, pr.nombre_producto 
                           FROM detalle_pedido dp
                           JOIN producto pr ON dp.id_producto = pr.id_producto
                           WHERE dp.id_pedido = ?";
            $stmtDet = $this->dbBusiness->prepare($sqlDetalle);
            $stmtDet->execute([$id_pedido]);
            $pedido['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        }

        return $pedido;
    }

    private function cambiarEstado($id_pedido, $estado)
    {
        $sql = "UPDATE pedido SET estado = ? WHERE id_pedido = ?";
        $stmt = $this->dbBusiness->prepare($sql);
        $res = $stmt->execute([$estado, $id_pedido]);
        return ['success' => $res, 'message' => $res ? 'Estado actualizado' : 'Error al actualizar estado'];
    }

    private function obtenerComprobante($id_pedido)
    {
        
        $stmt = $this->dbBusiness->prepare("SELECT id_pago FROM pago WHERE id_pedido = ?");
        $stmt->execute([$id_pedido]);
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pago) {
            return ['success' => false, 'message' => 'No hay pago registrado para este pedido.'];
        }

        
        $stmtImg = $this->dbSecurity->prepare("SELECT direccion FROM imagen WHERE entidad_tipo = 'PAGO' AND entidad_id = ?");
        $stmtImg->execute([$pago['id_pago']]);
        $imagen = $stmtImg->fetch(PDO::FETCH_ASSOC);

        if ($imagen) {
            return ['success' => true, 'url' => $imagen['direccion']];
        }

        return ['success' => false, 'message' => 'Comprobante no encontrado.'];
    }

private function crearPedidoPOS($datosCliente, $carrito, $datosPago)
{
    try {
        $this->dbBusiness->beginTransaction();
        
        
        $cedulaEmpleado = $_SESSION['user']['cedula'] ?? null;
        
        if (!$cedulaEmpleado) {
            return [
                'success' => false,
                'message' => '❌ No se pudo identificar al empleado. Inicia sesión nuevamente.'
            ];
        }
        
        
        $stmt = $this->dbBusiness->prepare("SELECT cedula FROM empleado WHERE cedula = ? AND estatus = 1");
        $stmt->execute([$cedulaEmpleado]);
        if ($stmt->rowCount() === 0) {
            return [
                'success' => false,
                'message' => '❌ <strong>No tienes permisos para registrar pedidos</strong><br><br>
                              Tu usuario no está registrado como empleado o está inactivo.<br><br>
                              <strong>Contacta al administrador del sistema</strong><br>
                              Cédula: ' . htmlspecialchars($cedulaEmpleado)
            ];
        }
        
        
        $cedula = !empty($datosCliente['cedula']) ? $datosCliente['cedula'] : null;

        if ($cedula) {
            
            $stmt = $this->dbBusiness->prepare("SELECT cedula FROM persona WHERE cedula = ?");
            $stmt->execute([$cedula]);
            if ($stmt->rowCount() == 0) {
                $nombresArray = explode(' ', $datosCliente['nombre'] ?? 'Cliente', 2);
                $sql = "INSERT INTO persona (cedula, nombre, apellido, telefono, direccion) VALUES (?, ?, ?, ?, ?)";
                $this->dbBusiness->prepare($sql)->execute([
                    $cedula,
                    $nombresArray[0] ?? 'Cliente',
                    $nombresArray[1] ?? '',
                    $datosCliente['telefono'] ?? null,
                    $datosCliente['direccion'] ?? null
                ]);
            }
            
            
            $stmt = $this->dbBusiness->prepare("SELECT cedula FROM cliente WHERE cedula = ?");
            $stmt->execute([$cedula]);
            if ($stmt->rowCount() == 0) {
                $this->dbBusiness->prepare("INSERT INTO cliente (cedula) VALUES (?)")->execute([$cedula]);
            }
        }

        
        $idPedido = 'PED' . date('YmdHis') . rand(100, 999);
        $totalPedido = $carrito['total'];
        $observacion = $datosCliente['observacion'] ?? 'Pedido POS';
        $tipoPedido = $datosCliente['tipo_pedido'] ?? 'LLEVAR';
        $idMesa = !empty($datosCliente['id_mesa']) ? $datosCliente['id_mesa'] : null;

        $sqlPedido = "INSERT INTO pedido (id_pedido, cedula_cliente, cedula_empleado, id_mesa, tipo_pedido, total, observacion, estado) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')";
        $this->dbBusiness->prepare($sqlPedido)->execute([
            $idPedido,
            $cedula,
            $cedulaEmpleado,
            $idMesa,
            $tipoPedido,
            $totalPedido,
            $observacion
        ]);

        
        $sqlDetalle = "INSERT INTO detalle_pedido (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, indicacion) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmtDet = $this->dbBusiness->prepare($sqlDetalle);

        foreach ($carrito['items'] as $item) {
            $idDetalle = 'DET' . date('YmdHis') . rand(1000, 9999);
            
            
            $indicacion = '';
            
            
            if (!empty($item['indicacion'])) {
                $indicacion = $item['indicacion'];
            } 
            
            elseif (!empty($item['extras']) && is_array($item['extras'])) {
                $extrasNombres = array_column($item['extras'], 'nombre');
                if (!empty($extrasNombres)) {
                    $indicacion = 'Extras: ' . implode(', ', $extrasNombres);
                }
            }
            
            elseif (!empty($item['addedAdicionales']) && is_array($item['addedAdicionales'])) {
                $extrasNombres = array_column($item['addedAdicionales'], 'nombre');
                if (!empty($extrasNombres)) {
                    $indicacion = 'Extras: ' . implode(', ', $extrasNombres);
                }
            }
            
            
            if (!empty($item['removedPrincipales']) && is_array($item['removedPrincipales'])) {
                $removidosNombres = array_column($item['removedPrincipales'], 'nombre_insumo');
                if (!empty($removidosNombres)) {
                    if ($indicacion) $indicacion .= ' | ';
                    $indicacion .= 'Sin: ' . implode(', ', $removidosNombres);
                }
            }
            
            
            if (empty($indicacion)) {
                $indicacion = null;
            }
            
            
            error_log("Guardando detalle - Pedido: $idPedido, Producto: {$item['id_producto']}, Cantidad: {$item['cantidad']}, Indicación: " . ($indicacion ?? 'SIN EXTRAS'));
            
            $stmtDet->execute([
                $idDetalle,
                $idPedido,
                $item['id_producto'],
                $item['cantidad'],
                $item['precio_unitario'],
                $indicacion
            ]);
        }

        
        $idMetodoPago = $datosPago['id_metodo_pago'] ?? null;
        if (!$idMetodoPago) {
            
            $stmt = $this->dbBusiness->prepare("SELECT id_metodo_pago FROM metodo_pago WHERE nombre = 'Efectivo' AND estatus = 1 LIMIT 1");
            $stmt->execute();
            $metodoDefault = $stmt->fetch(PDO::FETCH_ASSOC);
            $idMetodoPago = $metodoDefault['id_metodo_pago'] ?? null;
        }

        $idPago = 'PAG' . date('YmdHis') . rand(100, 999);
        
        $sqlPago = "INSERT INTO pago (id_pago, id_pedido, id_metodo_pago, monto, referencia) 
                    VALUES (?, ?, ?, ?, ?)";
        $this->dbBusiness->prepare($sqlPago)->execute([
            $idPago,
            $idPedido,
            $idMetodoPago,
            $totalPedido,
            $datosPago['referencia'] ?? null
        ]);

        $this->dbBusiness->commit();
        
        return [
            'success' => true, 
            'message' => 'Pedido registrado exitosamente.', 
            'id_pedido' => $idPedido
        ];

    } catch (Exception $e) {
        $this->dbBusiness->rollBack();
        error_log("Error en crearPedidoPOS: " . $e->getMessage());
        return [
            'success' => false, 
            'message' => 'Error al registrar pedido: ' . $e->getMessage()
        ];
    }
}

}
