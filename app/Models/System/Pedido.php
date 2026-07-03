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
            case 'listar_mesas_disponibles':  
                return $this->listarMesasDisponibles();
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
        $sql = "SELECT p.*, 
                per.nombre, per.apellido, per.telefono, 
                pag.id_pago, pag.referencia, mp.nombre AS metodo_pago,
                m.numero_mesa
                FROM pedido p
                LEFT JOIN cliente c ON p.cedula_cliente = c.cedula
                LEFT JOIN persona per ON c.cedula = per.cedula
                LEFT JOIN pago pag ON p.id_pedido = pag.id_pedido
                LEFT JOIN metodo_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
                LEFT JOIN mesa m ON p.id_mesa = m.id_mesa
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
        try {
            $this->dbBusiness->beginTransaction();
            
            // Obtener estado actual
            $sqlEstado = "SELECT estado FROM pedido WHERE id_pedido = ?";
            $stmtEstado = $this->dbBusiness->prepare($sqlEstado);
            $stmtEstado->execute([$id_pedido]);
            $estadoActual = $stmtEstado->fetch(PDO::FETCH_ASSOC)['estado'];
            
            // LOG: Verificar estados
            error_log("=== cambiarEstado ===");
            error_log("ID Pedido: $id_pedido");
            error_log("Estado actual: $estadoActual");
            error_log("Nuevo estado: $estado");
            
            // Actualizar estado del pedido
            $sql = "UPDATE pedido SET estado = ? WHERE id_pedido = ?";
            $stmt = $this->dbBusiness->prepare($sql);
            $res = $stmt->execute([$estado, $id_pedido]);
            
            if (!$res) {
                $this->dbBusiness->rollBack();
                return ['success' => false, 'message' => 'Error al actualizar estado'];
            }
            
            // ==============================================
            // DESCONTAR INSUMOS (si corresponde)
            // ==============================================
            $insumosDescontados = false;
            
            // 1. Si pasa a PREPARACION y no se habían descontado
            if ($estado === 'PREPARANDO' && $estadoActual !== 'PREPARANDO') {
                error_log("Entrando a PREPARACION - Verificando si requiere preparación");
                $requierePreparacion = $this->pedidoRequierePreparacion($id_pedido);
                
                if ($requierePreparacion) {
                    error_log("Pedido con productos de cocina - Descontando insumos");
                    $resultado = $this->descontarInsumosPedido($id_pedido);
                    
                    if (!$resultado['success']) {
                        $this->dbBusiness->rollBack();
                        return ['success' => false, 'message' => $resultado['message']];
                    }
                    $insumosDescontados = true;
                } else {
                    error_log("Pedido sin productos de cocina - Ya se descontaron al crear");
                }
            }
            
            // 2. Si pasa a ENTREGADO y no se descontaron antes (por si saltaron PREPARACION)
            if ($estado === 'ENTREGADO' && !$insumosDescontados && $estadoActual !== 'ENTREGADO') {
                error_log("Entrando a ENTREGADO - Verificando si requiere preparación");
                $requierePreparacion = $this->pedidoRequierePreparacion($id_pedido);
                
                if ($requierePreparacion) {
                    // Verificar si ya se descontaron (consultar si hay un registro de descuento)
                    // Por ahora, descontar directamente
                    error_log("Pedido con productos de cocina - Descontando insumos al entregar");
                    $resultado = $this->descontarInsumosPedido($id_pedido);
                    
                    if (!$resultado['success']) {
                        $this->dbBusiness->rollBack();
                        return ['success' => false, 'message' => $resultado['message']];
                    }
                }
            }
            
            // ==============================================
            // RESTAURAR INSUMOS (si se cancela)
            // ==============================================
            // Si el pedido se cancela y estaba en PREPARACION o ENTREGADO, restaurar insumos
            if ($estado === 'CANCELADO' && in_array($estadoActual, ['PREPARANDO', 'ENTREGADO'])) {
                error_log("Cancelando pedido - Restaurando insumos");
                $this->restaurarInsumosPedido($id_pedido);
            }
            
            // ==============================================
            // LIBERAR MESA (si aplica)
            // ==============================================
            if (in_array($estado, ['ENTREGADO', 'CANCELADO'])) {
                $sqlInfo = "SELECT tipo_pedido, id_mesa FROM pedido WHERE id_pedido = ?";
                $stmtInfo = $this->dbBusiness->prepare($sqlInfo);
                $stmtInfo->execute([$id_pedido]);
                $pedidoInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);
                
                if ($pedidoInfo && $pedidoInfo['tipo_pedido'] === 'MESA' && $pedidoInfo['id_mesa']) {
                    $sqlLiberar = "UPDATE mesa SET estado = 'DISPONIBLE' WHERE id_mesa = ?";
                    $stmtLiberar = $this->dbBusiness->prepare($sqlLiberar);
                    $stmtLiberar->execute([$pedidoInfo['id_mesa']]);
                    error_log("Mesa liberada: " . $pedidoInfo['id_mesa']);
                }
            }
            
            $this->dbBusiness->commit();
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
            
        } catch (\PDOException $e) {
            $this->dbBusiness->rollBack();
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()];
        }
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
            
            // ==============================================
            // 1. VALIDAR EMPLEADO (LOGEADO)
            // ==============================================
            $cedulaEmpleado = $_SESSION['user']['cedula'] ?? null;
            
            if (!$cedulaEmpleado) {
                return [
                    'success' => false,
                    'message' => '❌ No se pudo identificar al empleado. Inicia sesión nuevamente.'
                ];
            }
            
            // Verificar que el empleado existe y está activo
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
            
            // ==============================================
            // 2. CLIENTE (si tiene cédula)
            // ==============================================
            $cedula = !empty($datosCliente['cedula']) ? $datosCliente['cedula'] : null;

            if ($cedula) {
                // Buscar o crear persona
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
                
                // Buscar o crear cliente
                $stmt = $this->dbBusiness->prepare("SELECT cedula FROM cliente WHERE cedula = ?");
                $stmt->execute([$cedula]);
                if ($stmt->rowCount() == 0) {
                    $this->dbBusiness->prepare("INSERT INTO cliente (cedula) VALUES (?)")->execute([$cedula]);
                }
            }

            // ==============================================
            // 3. CREAR PEDIDO
            // ==============================================
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

            // ==============================================
            // 4. DETALLES DEL PEDIDO (CON INDICACION)
            // ==============================================
            $sqlDetalle = "INSERT INTO detalle_pedido (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, indicacion) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmtDet = $this->dbBusiness->prepare($sqlDetalle);

            // Variable para saber si hay productos de cocina
            $hayProductosCocina = false;

            foreach ($carrito['items'] as $item) {
                $idDetalle = 'DET' . date('YmdHis') . rand(1000, 9999);
                
                // ==========================================
                // CONSTRUIR LA INDICACIÓN CON LOS EXTRAS
                // ==========================================
                $indicacion = '';
                
                // Opción 1: Si ya viene una indicación desde el frontend
                if (!empty($item['indicacion'])) {
                    $indicacion = $item['indicacion'];
                } 
                // Opción 2: Si tiene extras en el objeto
                elseif (!empty($item['extras']) && is_array($item['extras'])) {
                    $extrasNombres = array_column($item['extras'], 'nombre');
                    if (!empty($extrasNombres)) {
                        $indicacion = 'Extras: ' . implode(', ', $extrasNombres);
                    }
                }
                // Opción 3: Si tiene addedAdicionales (la estructura que usas en el carrito)
                elseif (!empty($item['addedAdicionales']) && is_array($item['addedAdicionales'])) {
                    $extrasNombres = array_column($item['addedAdicionales'], 'nombre');
                    if (!empty($extrasNombres)) {
                        $indicacion = 'Extras: ' . implode(', ', $extrasNombres);
                    }
                }
                
                // Agregar información de "sin" (productos removidos)
                if (!empty($item['removedPrincipales']) && is_array($item['removedPrincipales'])) {
                    $removidosNombres = array_column($item['removedPrincipales'], 'nombre_insumo');
                    if (!empty($removidosNombres)) {
                        if ($indicacion) $indicacion .= ' | ';
                        $indicacion .= 'Sin: ' . implode(', ', $removidosNombres);
                    }
                }
                
                // Limpiar la indicación (evitar null o vacío)
                if (empty($indicacion)) {
                    $indicacion = null;
                }
                
                // Debug: registrar en log para verificar
                error_log("Guardando detalle - Pedido: $idPedido, Producto: {$item['id_producto']}, Cantidad: {$item['cantidad']}, Indicación: " . ($indicacion ?? 'SIN EXTRAS'));
                
                $stmtDet->execute([
                    $idDetalle,
                    $idPedido,
                    $item['id_producto'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $indicacion
                ]);

                // Verificar si el producto es de COCINA
                if (!empty($item['tipo_producto']) && $item['tipo_producto'] === 'COCINA') {
                    $hayProductosCocina = true;
                }
            }

            // ==============================================
            // 5. PAGO
            // ==============================================
            $idMetodoPago = $datosPago['id_metodo_pago'] ?? null;
            if (!$idMetodoPago) {
                // Buscar método de pago por defecto (Efectivo)
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

            // ==============================================
            // 6. DESCONTAR INSUMOS SEGÚN TIPO DE PRODUCTO
            // ==============================================
            // Si NO hay productos de cocina (solo BARRA y/o POSTRE),
            // descontar insumos inmediatamente
            if (!$hayProductosCocina) {
                error_log("Pedido sin productos de cocina - Descontando insumos inmediatamente");
                $resultadoDescuento = $this->descontarInsumosPedido($idPedido);
                
                if (!$resultadoDescuento['success']) {
                    $this->dbBusiness->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Error al descontar insumos: ' . $resultadoDescuento['message']
                    ];
                }
                
                // Actualizar estado a "LISTO" (no requiere preparación)
                $sqlEstado = "UPDATE pedido SET estado = 'LISTO' WHERE id_pedido = ?";
                $stmtEstado = $this->dbBusiness->prepare($sqlEstado);
                $stmtEstado->execute([$idPedido]);
                
                error_log("Pedido sin cocina - Estado actualizado a LISTO");
            }

            $this->dbBusiness->commit();
            
            return [
                'success' => true, 
                'message' => $hayProductosCocina 
                    ? 'Pedido registrado exitosamente. Pasará a preparación.' 
                    : 'Pedido registrado exitosamente. Productos listos para entregar.',
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

    private function listarMesasDisponibles()
    {
        try {
            $sql = "SELECT id_mesa, numero_mesa, capacidad, id_area 
                    FROM mesa 
                    WHERE estado = 'DISPONIBLE' AND estatus = 1 
                    ORDER BY numero_mesa ASC";
            $stmt = $this->dbBusiness->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarMesasDisponibles: " . $e->getMessage());
            return [];
        }
    }

/**
 * Descontar insumos de un pedido
 * @param string $id_pedido
 * @return array ['success' => bool, 'message' => string]
 */

private function descontarInsumosPedido($id_pedido)
{
    try {
        error_log("=== descontarInsumosPedido ===");
        error_log("ID Pedido: $id_pedido");
        
        // Obtener todos los productos del pedido con sus detalles
        $sql = "SELECT dp.id_producto, dp.cantidad, dp.indicacion,
                       p.id_preparacion, p.id_insumo, p.cantidad as cantidad_insumo,
                       i.stock_actual, i.nombre_insumo
                FROM detalle_pedido dp
                JOIN producto pr ON dp.id_producto = pr.id_producto
                JOIN preparacion p ON pr.id_producto = p.id_producto
                JOIN insumo i ON p.id_insumo = i.id_insumo
                WHERE dp.id_pedido = ? AND p.prioridad_insumo = 1";
        
        $stmt = $this->dbBusiness->prepare($sql);
        $stmt->execute([$id_pedido]);
        $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Insumos encontrados: " . count($insumos));
        
        if (empty($insumos)) {
            error_log("No hay insumos para descontar (prioridad=1)");
            
            // Si no hay insumos con prioridad=1, intentar con todos los insumos
            $sql2 = "SELECT dp.id_producto, dp.cantidad,
                            p.id_insumo, p.cantidad as cantidad_insumo,
                            i.stock_actual, i.nombre_insumo
                     FROM detalle_pedido dp
                     JOIN producto pr ON dp.id_producto = pr.id_producto
                     JOIN preparacion p ON pr.id_producto = p.id_producto
                     JOIN insumo i ON p.id_insumo = i.id_insumo
                     WHERE dp.id_pedido = ?";
            
            $stmt2 = $this->dbBusiness->prepare($sql2);
            $stmt2->execute([$id_pedido]);
            $insumos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Insumos encontrados (sin filtro): " . count($insumos));
            
            if (empty($insumos)) {
                return ['success' => true, 'message' => 'No hay insumos para descontar'];
            }
        }
        
        // Verificar stock suficiente
        foreach ($insumos as $item) {
            $stockNecesario = $item['cantidad'] * $item['cantidad_insumo'];
            error_log("Insumo: {$item['nombre_insumo']}, Stock actual: {$item['stock_actual']}, Necesario: $stockNecesario");
            
            if ($item['stock_actual'] < $stockNecesario) {
                return [
                    'success' => false, 
                    'message' => "Stock insuficiente para el insumo: {$item['nombre_insumo']}"
                ];
            }
        }
        
        // Descontar insumos
        $sqlUpdate = "UPDATE insumo SET stock_actual = stock_actual - ? WHERE id_insumo = ?";
        $stmtUpdate = $this->dbBusiness->prepare($sqlUpdate);
        
        foreach ($insumos as $item) {
            $stockNecesario = $item['cantidad'] * $item['cantidad_insumo'];
            $stmtUpdate->execute([$stockNecesario, $item['id_insumo']]);
            error_log("Descontado: {$item['nombre_insumo']} - $stockNecesario unidades");
        }
        
        return ['success' => true, 'message' => 'Insumos descontados correctamente'];
        
    } catch (\PDOException $e) {
        error_log("Error en descontarInsumosPedido: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al descontar insumos: ' . $e->getMessage()];
    }
}

/**
 * Restaurar insumos de un pedido (para cancelaciones)
 */
private function restaurarInsumosPedido($id_pedido)
{
    try {
        $sql = "SELECT dp.id_producto, dp.cantidad,
                       p.id_insumo, p.cantidad as cantidad_insumo
                FROM detalle_pedido dp
                JOIN producto pr ON dp.id_producto = pr.id_producto
                JOIN preparacion p ON pr.id_producto = p.id_producto
                WHERE dp.id_pedido = ? AND p.prioridad_insumo = 1";
        
        $stmt = $this->dbBusiness->prepare($sql);
        $stmt->execute([$id_pedido]);
        $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($insumos)) {
            return ['success' => true, 'message' => 'No hay insumos para restaurar'];
        }
        
        $sqlUpdate = "UPDATE insumo SET stock_actual = stock_actual + ? WHERE id_insumo = ?";
        $stmtUpdate = $this->dbBusiness->prepare($sqlUpdate);
        
        foreach ($insumos as $item) {
            $stockARestaurar = $item['cantidad'] * $item['cantidad_insumo'];
            $stmtUpdate->execute([$stockARestaurar, $item['id_insumo']]);
        }
        
        return ['success' => true, 'message' => 'Insumos restaurados correctamente'];
        
    } catch (\PDOException $e) {
        error_log("Error en restaurarInsumosPedido: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al restaurar insumos: ' . $e->getMessage()];
    }
}

/**
 * Verificar si un pedido tiene productos que requieren preparación (COCINA)
 */
private function pedidoRequierePreparacion($id_pedido)
{
    try {
        $sql = "SELECT COUNT(*) as total 
                FROM detalle_pedido dp
                JOIN producto pr ON dp.id_producto = pr.id_producto
                WHERE dp.id_pedido = ? AND pr.tipo_producto = 'COCINA'";
        
        $stmt = $this->dbBusiness->prepare($sql);
        $stmt->execute([$id_pedido]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("pedidoRequierePreparacion - ID: $id_pedido, Total COCINA: " . $result['total']);
        
        return $result['total'] > 0;
        
    } catch (\PDOException $e) {
        error_log("Error en pedidoRequierePreparacion: " . $e->getMessage());
        return true;
    }
}

}
