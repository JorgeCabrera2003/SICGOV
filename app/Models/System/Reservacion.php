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
    private $id_mesa;
    private $fecha;
    private $hora;
    private $hora_fin;
    private $estado;

    public function __construct()
    {
        $this->id_reservacion = "";
        $this->cedula_cliente = "";
        $this->id_mesa = null;
        $this->fecha = "";
        $this->hora = "";
        $this->hora_fin = "";
        $this->estado = "PENDIENTE";
    }

    
    public function setId(string $id) { 
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El formato del ID de reservación no es válido.");
        }
        $this->id_reservacion = $id; 
    }

    public function setCedulaCliente(string $cedula) { 
        if (RegexHelper::ValidarFormatos($cedula, 'Cedula') == 0) {
            throw new Exception("El formato de la cédula del cliente no es válido.");
        }
        $this->cedula_cliente = $cedula; 
    }

    public function setIdMesa(?string $id_mesa) { 
        if (!empty($id_mesa) && RegexHelper::ValidarFormatos($id_mesa, 'ID') == 0) {
            throw new Exception("El formato del ID de la mesa no es válido.");
        }
        $this->id_mesa = empty($id_mesa) ? null : $id_mesa; 
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
            throw new Exception("La hora de inicio no tiene un formato válido (HH:MM).");
        }
        $this->hora = $hora; 
    }

    public function setHoraFin(string $hora) { 
        // Formato HH:MM:SS o HH:MM
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            throw new Exception("La hora de fin no tiene un formato válido (HH:MM).");
        }
        $this->hora_fin = $hora; 
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
            $this->ValidarExistencias();
            $this->ValidarDisponibilidad($this->fecha, $this->hora);

            $this->LlamarConexion()->beginTransaction();
            $sql = "INSERT INTO reservacion(id_reservacion, cedula_cliente, fecha, hora, hora_fin, estado) 
                    VALUES (:id, :cedula, :fecha, :hora, :hora_fin, :estado)";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id' => $this->id_reservacion,
                ':cedula' => $this->cedula_cliente,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin,
                ':estado' => $this->estado
            ]);

            if (!empty($this->id_mesa)) {
                $id_asignacion = Helper::generarId('ASM');
                $sqlMesa = "INSERT INTO asignacion_mesa(id_asignacion, id_reservacion, id_mesa) VALUES (:id_asignacion, :id_reservacion, :id_mesa)";
                $stmMesa = $this->LlamarConexion()->prepare($sqlMesa);
                $stmMesa->execute([
                    ':id_asignacion' => $id_asignacion,
                    ':id_reservacion' => $this->id_reservacion,
                    ':id_mesa' => $this->id_mesa
                ]);
            }

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
            $this->ValidarExistencias();
            $this->ValidarDisponibilidad($this->fecha, $this->hora, $this->id_reservacion);

            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE reservacion SET fecha = :fecha, hora = :hora, hora_fin = :hora_fin, estado = :estado 
                    WHERE id_reservacion = :id";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id' => $this->id_reservacion,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin,
                ':estado' => $this->estado
            ]);

            
            $sqlDelMesa = "DELETE FROM asignacion_mesa WHERE id_reservacion = :id";
            $stmDelMesa = $this->LlamarConexion()->prepare($sqlDelMesa);
            $stmDelMesa->execute([':id' => $this->id_reservacion]);

            // Insertar nueva asignacion si hay mesa
            if (!empty($this->id_mesa)) {
                $id_asignacion = Helper::generarId('ASM');
                $sqlMesa = "INSERT INTO asignacion_mesa(id_asignacion, id_reservacion, id_mesa) VALUES (:id_asignacion, :id_reservacion, :id_mesa)";
                $stmMesa = $this->LlamarConexion()->prepare($sqlMesa);
                $stmMesa->execute([
                    ':id_asignacion' => $id_asignacion,
                    ':id_reservacion' => $this->id_reservacion,
                    ':id_mesa' => $this->id_mesa
                ]);
            }

            $this->LlamarConexion()->commit();
            return ['estado' => 1, 'response' => ['resultado' => 200, 'mensaje' => "Reservación actualizada"]];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            throw $e;
        }
    }

    private function ValidarDisponibilidad($fecha, $hora, $id_excluir = null)
    {
        
        if (strtotime($this->hora_fin) <= strtotime($this->hora)) {
            throw new Exception("La hora de fin debe ser mayor a la hora de inicio.");
        }

        
        if (!empty($this->id_mesa)) {
            $sql = "SELECT COUNT(*) FROM reservacion 
                    JOIN asignacion_mesa ON reservacion.id_reservacion = asignacion_mesa.id_reservacion
                    WHERE reservacion.fecha = :fecha 
                    AND asignacion_mesa.id_mesa = :id_mesa 
                    AND reservacion.estado != 'CANCELADA'
                    AND ((reservacion.hora < :hora_fin AND reservacion.hora_fin > :hora))";
            $params = [
                ':fecha' => $fecha, 
                ':id_mesa' => $this->id_mesa,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin
            ];
        } else {
            
            $sql = "SELECT COUNT(*) FROM reservacion 
                    WHERE reservacion.fecha = :fecha 
                    AND reservacion.cedula_cliente = :cedula 
                    AND reservacion.estado != 'CANCELADA'
                    AND ((reservacion.hora < :hora_fin AND reservacion.hora_fin > :hora))";
            $params = [
                ':fecha' => $fecha, 
                ':cedula' => $this->cedula_cliente,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin
            ];
        }

        if ($id_excluir) {
            $sql .= " AND reservacion.id_reservacion != :id";
            $params[':id'] = $id_excluir;
        }

        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute($params);
        if ($stm->fetchColumn() > 0) {
            if (!empty($this->id_mesa)) {
                throw new Exception("La mesa seleccionada ya se encuentra reservada en este horario.");
            } else {
                throw new Exception("El cliente ya tiene una reservación que interfiere con este horario.");
            }
        }
    }

    private function ValidarExistencias()
    {
        $sql = "SELECT COUNT(*) FROM cliente WHERE cedula = :cedula";
        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute([':cedula' => $this->cedula_cliente]);
        if ($stm->fetchColumn() == 0) {
            throw new Exception("El cliente seleccionado no se encuentra registrado en el sistema.");
        }

        if (!empty($this->id_mesa)) {
            $sqlMesa = "SELECT COUNT(*) FROM mesa WHERE id_mesa = :id_mesa";
            $stmMesa = $this->LlamarConexion()->prepare($sqlMesa);
            $stmMesa->execute([':id_mesa' => $this->id_mesa]);
            if ($stmMesa->fetchColumn() == 0) {
                throw new Exception("La mesa seleccionada no existe en el sistema.");
            }
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
        $sql = "SELECT r.id_reservacion, r.cedula_cliente, r.fecha, r.hora, r.hora_fin, r.estado, p.nombre, p.apellido, p.telefono, m.numero_mesa, am.id_mesa 
                FROM reservacion r 
                JOIN persona p ON r.cedula_cliente = p.cedula 
                LEFT JOIN asignacion_mesa am ON r.id_reservacion = am.id_reservacion
                LEFT JOIN mesa m ON am.id_mesa = m.id_mesa
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
                'title' => $r['nombre'] . ' ' . $r['apellido'] . ($r['numero_mesa'] ? " (Mesa {$r['numero_mesa']})" : ""),
                'start' => $r['fecha'] . 'T' . $r['hora'],
                'end' => $r['fecha'] . 'T' . $r['hora_fin'],
                'editable' => ($r['estado'] === 'PENDIENTE'), // Solo se mueven las pendientes
                'extendedProps' => [
                    'cedula' => $r['cedula_cliente'],
                    'telefono' => $r['telefono'],
                    'estado' => $r['estado'],
                    'id_mesa' => $r['id_mesa'],
                    'numero_mesa' => $r['numero_mesa']
                ],
                'className' => 'status-' . strtolower($r['estado'])
            ];

        }

        return ['estado' => 1, 'response' => ['resultado' => 200, 'datos' => $eventos]];
    }

    private function ObtenerDetalle()
    {
        $sql = "SELECT r.id_reservacion, r.cedula_cliente, r.fecha, r.hora, r.hora_fin, r.estado, p.nombre, p.apellido, p.telefono, p.correo, m.numero_mesa, am.id_mesa 
                FROM reservacion r 
                JOIN persona p ON r.cedula_cliente = p.cedula 
                LEFT JOIN asignacion_mesa am ON r.id_reservacion = am.id_reservacion
                LEFT JOIN mesa m ON am.id_mesa = m.id_mesa
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
