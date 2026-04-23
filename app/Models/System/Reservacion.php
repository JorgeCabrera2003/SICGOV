<?php
namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Reservacion extends Database
{
    private $id_reservacion;
    private $cedula_cliente;
    private $fecha;
    private $hora;
    private $estado;

    public function __construct()
    {
        $this->id_reservacion = "";
        $this->cedula_cliente = "";
        $this->fecha = "";
        $this->hora = "";
        $this->estado = "PENDIENTE";
    }

    // SETTERS CON VALIDACIÓN RIGUROSA
    public function setId(string $id) { 
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de reservación no es válido.");
        }
        $this->id_reservacion = $id; 
    }

    public function setCedulaCliente(string $cedula) { 
        if (RegexHelper::ValidarFormatos($cedula, 'Cedula') == 0) {
            throw new Exception("La cédula del cliente no es válida.");
        }
        $this->cedula_cliente = $cedula; 
    }

    public function setFecha(string $fecha) { 
        // Formato YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new Exception("La fecha no tiene un formato válido (AAAA-MM-DD).");
        }
        $this->fecha = $fecha; 
    }

    public function setHora(string $hora) { 
        // Formato HH:MM:SS o HH:MM
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            throw new Exception("La hora no tiene un formato válido (HH:MM).");
        }
        $this->hora = $hora; 
    }

    public function setEstado(string $estado) { 
        $estados_validos = ['PENDIENTE', 'CONFIRMADA', 'CANCELADA', 'COMPLETADA'];
        if (!in_array($estado, $estados_validos)) {
            throw new Exception("Estado de reservación no válido.");
        }
        $this->estado = $estado; 
    }

    public function Transaccion($peticion)
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            
            switch ($peticion['peticion']) {
                case 'registrar':
                    $dato = $this->Registrar();
                    break;
                case 'modificar':
                    $dato = $this->Modificar();
                    break;
                case 'eliminar':
                    $dato = $this->Eliminar();
                    break;
                case 'listar':
                    $dato = $this->ListarEventos($peticion['filtros'] ?? []);
                    break;
                case 'detalle':
                    $dato = $this->ObtenerDetalle();
                    break;
                default:
                    $dato = ['estado' => -1, 'response' => ['resultado' => 400, 'mensaje' => "Petición no válida"]];
                    break;
            }
        } catch (Exception $e) {
            $dato = ['estado' => -1, 'response' => ['resultado' => 500, 'mensaje' => $e->getMessage()]];
        } finally {
            $this->DestruirConexion();
        }
        return $dato;
    }

    private function Registrar()
    {
        try {
            $this->LlamarConexion()->beginTransaction();

            $sql = "INSERT INTO reservacion(id_reservacion, cedula_cliente, fecha, hora, estado) 
                    VALUES (:id, :cedula, :fecha, :hora, :estado)";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id' => $this->id_reservacion,
                ':cedula' => $this->cedula_cliente,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':estado' => $this->estado
            ]);

            $this->LlamarConexion()->commit();
            return ['estado' => 1, 'response' => ['resultado' => 200, 'mensaje' => "Reservación registrada con éxito"]];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            throw $e;
        }
    }

    private function Modificar()
    {
        try {
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE reservacion SET fecha = :fecha, hora = :hora, estado = :estado 
                    WHERE id_reservacion = :id";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id' => $this->id_reservacion,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':estado' => $this->estado
            ]);

            $this->LlamarConexion()->commit();
            return ['estado' => 1, 'response' => ['resultado' => 200, 'mensaje' => "Reservación actualizada"]];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            throw $e;
        }
    }

    private function Eliminar()
    {
        try {
            $this->LlamarConexion()->beginTransaction();

            $sql = "DELETE FROM reservacion WHERE id_reservacion = :id";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([':id' => $this->id_reservacion]);

            $this->LlamarConexion()->commit();
            return ['estado' => 1, 'response' => ['resultado' => 200, 'mensaje' => "Reservación eliminada"]];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            throw $e;
        }
    }

    private function ListarEventos($filtros = [])
    {
        $sql = "SELECT r.*, p.nombre, p.apellido, p.telefono 
                FROM reservacion r 
                JOIN persona p ON r.cedula_cliente = p.cedula 
                WHERE 1=1";
        
        $params = [];
        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $sql .= " AND r.fecha BETWEEN :desde AND :hasta";
            $params[':desde'] = $filtros['desde'];
            $params[':hasta'] = $filtros['hasta'];
        }

        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute($params);
        $res = $stm->fetchAll(PDO::FETCH_ASSOC);

        $eventos = [];
        foreach ($res as $r) {
            // FullCalendar format
            $eventos[] = [
                'id' => $r['id_reservacion'],
                'title' => $r['nombre'] . ' ' . $r['apellido'],
                'start' => $r['fecha'] . 'T' . $r['hora'],
                'end' => date('Y-m-d\TH:i:s', strtotime($r['fecha'] . ' ' . $r['hora'] . ' +1 hour')), // Default 1h
                'extendedProps' => [
                    'cedula' => $r['cedula_cliente'],
                    'telefono' => $r['telefono'],
                    'estado' => $r['estado']
                ],
                'className' => 'status-' . strtolower($r['estado'])
            ];
        }

        return ['estado' => 1, 'response' => ['resultado' => 200, 'datos' => $eventos]];
    }

    private function ObtenerDetalle()
    {
        $sql = "SELECT r.*, p.nombre, p.apellido, p.telefono, p.correo 
                FROM reservacion r 
                JOIN persona p ON r.cedula_cliente = p.cedula 
                WHERE r.id_reservacion = :id";
        
        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute([':id' => $this->id_reservacion]);
        $res = $stm->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            return ['estado' => 1, 'response' => ['resultado' => 200, 'registro' => $res]];
        }
        return ['estado' => -1, 'response' => ['resultado' => 404, 'mensaje' => "No encontrada"]];
    }

    public function ObtenerClientes()
    {
        $sql = "SELECT p.cedula, p.nombre, p.apellido 
                FROM cliente c 
                JOIN persona p ON c.cedula = p.cedula 
                ORDER BY p.nombre ASC";
        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
}
