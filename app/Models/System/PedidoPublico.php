<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;
use Exception;

class PedidoPublico
{
    private $dbBusiness;
    private $dbSecurity;

    public function __construct()
    {
        $this->dbBusiness = Database::getConnection('business');
        $this->dbSecurity = Database::getConnection('security');
    }

    public function registrarPedidoWeb($datosCliente, $carrito, $datosPago)
    {
        try {
            $this->dbBusiness->beginTransaction();
            $this->dbSecurity->beginTransaction();

            // 1. Gestionar Persona y Cliente
            $cedula = $datosCliente['cedula'];
            
            // Verificar si la persona existe
            $stmt = $this->dbBusiness->prepare("SELECT cedula FROM persona WHERE cedula = ?");
            $stmt->execute([$cedula]);
            if ($stmt->rowCount() == 0) {
                // Insertar Persona
                $sql = "INSERT INTO persona (cedula, nombre, apellido, telefono, direccion) VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $this->dbBusiness->prepare($sql);
                // Asumimos nombre y apellido del form (separando si viene en un solo campo)
                $nombresArray = explode(' ', $datosCliente['nombre'], 2);
                $nombre = $nombresArray[0];
                $apellido = isset($nombresArray[1]) ? $nombresArray[1] : '';
                
                $stmtInsert->execute([
                    $cedula,
                    $nombre,
                    $apellido,
                    $datosCliente['telefono'] ?? null,
                    $datosCliente['direccion'] ?? null
                ]);
            }

            // Verificar si es cliente
            $stmt = $this->dbBusiness->prepare("SELECT cedula FROM cliente WHERE cedula = ?");
            $stmt->execute([$cedula]);
            if ($stmt->rowCount() == 0) {
                $this->dbBusiness->prepare("INSERT INTO cliente (cedula) VALUES (?)")->execute([$cedula]);
            }

            // 2. Crear Pedido
            $idPedido = 'PED' . date('YmdHis') . rand(100, 999);
            // Calculamos total asegurado desde backend o confiamos en carrito y validamos
            $totalPedido = $carrito['total']; 
            $observacion = $datosCliente['observacion'] ?? 'Pedido Web';

            $sqlPedido = "INSERT INTO pedido (id_pedido, cedula_cliente, cedula_empleado, tipo_pedido, total, observacion) 
                          VALUES (?, ?, NULL, 'DELIVERY', ?, ?)";
            $this->dbBusiness->prepare($sqlPedido)->execute([
                $idPedido,
                $cedula,
                $totalPedido,
                $observacion
            ]);

            // 3. Crear Detalle de Pedido
            $sqlDetalle = "INSERT INTO detalle_pedido (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, indicacion) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmtDet = $this->dbBusiness->prepare($sqlDetalle);

            foreach ($carrito['items'] as $item) {
                $idDetalle = 'DET' . date('YmdHis') . rand(1000, 9999);
                $indicacion = '';
                
                // Procesar indicaciones (ingredientes quitados, extras añadidos)
                if (!empty($item['removedPrincipales'])) {
                    $nombresRemovidos = array_column($item['removedPrincipales'], 'nombre_insumo');
                    $indicacion .= "Sin: " . implode(", ", $nombresRemovidos) . ". ";
                }
                if (!empty($item['addedAdicionales'])) {
                    $nombresExtras = array_column($item['addedAdicionales'], 'nombre_insumo');
                    $indicacion .= "Extras: " . implode(", ", $nombresExtras) . ". ";
                }

                $stmtDet->execute([
                    $idDetalle,
                    $idPedido,
                    $item['id_producto'],
                    $item['cantidad'],
                    $item['precio_unitario'], // Precio unitario con extras
                    $indicacion
                ]);
            }

            // 4. Crear Pago
            $idPago = 'PAG' . date('YmdHis') . rand(100, 999);
            $idMetodoPago = $datosPago['id_metodo_pago'] ?? 'MP_PM_2024'; // Por defecto Pago Móvil si no viene
            
            $sqlPago = "INSERT INTO pago (id_pago, id_pedido, id_metodo_pago, monto, referencia) 
                        VALUES (?, ?, ?, ?, ?)";
            $this->dbBusiness->prepare($sqlPago)->execute([
                $idPago,
                $idPedido,
                $idMetodoPago,
                $totalPedido,
                $datosPago['referencia'] ?? null
            ]);

            // 5. Crear Imagen del Comprobante si existe
            if (!empty($datosPago['comprobante_url'])) {
                $idImagen = 'IMG' . date('YmdHis') . rand(100, 999);
                $sqlImagen = "INSERT INTO imagen (id_imagen, entidad_tipo, entidad_id, direccion, titulo) 
                              VALUES (?, 'PAGO', ?, ?, 'Comprobante de Pago')";
                $this->dbSecurity->prepare($sqlImagen)->execute([
                    $idImagen,
                    $idPago,
                    $datosPago['comprobante_url']
                ]);
            }

            $this->dbBusiness->commit();
            $this->dbSecurity->commit();

            return ['success' => true, 'message' => 'Pedido registrado exitosamente.', 'id_pedido' => $idPedido];

        } catch (Exception $e) {
            $this->dbBusiness->rollBack();
            $this->dbSecurity->rollBack();
            return ['success' => false, 'message' => 'Error al registrar el pedido: ' . $e->getMessage()];
        }
    }
}
