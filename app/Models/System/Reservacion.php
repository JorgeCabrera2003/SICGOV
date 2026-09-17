<?php
namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use PDOException;
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
    private $cantidad_personas;

    public function __construct()
    {
        $this->id_reservacion = "";
        $this->cedula_cliente = "";
        $this->id_mesa = null;
        $this->fecha = "";
        $this->hora = "";
        $this->hora_fin = "";
        $this->estado = "PENDIENTE";
        $this->cantidad_personas = 1;
    }

    // =========================================================================
    // Getters y Setters con validaciones de formato
    // =========================================================================
    public function getId() {
        return $this->id_reservacion;
    }

    public function setId(string $id) { 
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El formato del ID de reservación no es válido.");
        }
        $this->id_reservacion = $id; 
    }

    public function getCedulaCliente() {
        return $this->cedula_cliente;
    }

    public function setCedulaCliente(string $cedula) { 
        if (RegexHelper::ValidarFormatos($cedula, 'Cedula') == 0) {
            throw new Exception("El formato de la cédula del cliente no es válido.");
        }
        $this->cedula_cliente = $cedula; 
    }

    public function getIdMesa() {
        return $this->id_mesa;
    }

    public function setIdMesa(?string $id_mesa) { 
        if (!empty($id_mesa) && RegexHelper::ValidarFormatos($id_mesa, 'ID') == 0) {
            throw new Exception("El formato del ID de la mesa no es válido.");
        }
        $this->id_mesa = empty($id_mesa) ? null : $id_mesa; 
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha(string $fecha) { 
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new Exception("La fecha no tiene un formato válido (AAAA-MM-DD).");
        }
        $this->fecha = $fecha; 
    }

    public function getHora() {
        return $this->hora;
    }

    public function setHora(string $hora) { 
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            throw new Exception("La hora de inicio no tiene un formato válido (HH:MM).");
        }
        $this->hora = $hora; 
    }

    public function getHoraFin() {
        return $this->hora_fin;
    }

    public function setHoraFin(string $hora) { 
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            throw new Exception("La hora de fin no tiene un formato válido (HH:MM).");
        }
        $this->hora_fin = $hora; 
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado(string $estado) { 
        $estados_validos = ['PENDIENTE', 'CONFIRMADA', 'CANCELADA', 'COMPLETADA'];
        if (!in_array($estado, $estados_validos)) {
            throw new Exception("Estado de reservación no válido.");
        }
        $this->estado = $estado; 
    }

    public function getCantidadPersonas() {
        return $this->cantidad_personas;
    }

    public function setCantidadPersonas(int $cant) {
        if ($cant < 1 || $cant > 50) {
            throw new Exception("La cantidad de personas debe ser entre 1 y 50.");
        }
        $this->cantidad_personas = $cant;
    }

    // =========================================================================
    // Manejador de Operaciones (Transacción)
    // =========================================================================
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
                case 'validar':
                    $dato = $this->ValidarReservacion();
                    break;
                case 'listar':
                    $dato = $this->ListarEventos($peticion['filtros'] ?? []);
                    break;
                case 'detalle':
                    $dato = $this->ObtenerDetalle();
                    break;
                default:
                    $dato = [
                        'estado' => -1,
                        'bool' => 0,
                        'response' => ['resultado' => 400, 'mensaje' => "Petición no válida"],
                        'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                    ];
                    break;
            }
        } catch (Exception $e) {
            $dato = [
                'estado' => -1,
                'bool' => 0,
                'response' => ['resultado' => 500, 'mensaje' => $e->getMessage()],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => "Error interno del servidor"]
            ];
        } finally {
            $this->DestruirConexion();
        }
        return $dato;
    }

    // =========================================================================
    // Métodos de Validación Independientes (Patrón Insumo)
    // =========================================================================
    public function ValidarReservacion()
    {
        // 1. Bloquear fechas pasadas
        $hoy = date('Y-m-d');
        if ($this->fecha < $hoy && $this->estado !== 'CANCELADA') {
            return [
                'estado' => -1,
                'bool' => 0,
                'response' => ['resultado' => 400, 'icon' => 'warning', 'mensaje' => "No se pueden realizar ni modificar reservaciones en fechas pasadas."],
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Fecha pasada no permitida"]
            ];
        }

        // 2. Validar rango horario
        if (strtotime($this->hora_fin) <= strtotime($this->hora)) {
            return [
                'estado' => -1,
                'bool' => 0,
                'response' => ['resultado' => 400, 'icon' => 'warning', 'mensaje' => "La hora de fin debe ser posterior a la hora de inicio."],
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Horario no válido"]
            ];
        }

        // 3. Validar existencia del cliente
        $sqlCli = "SELECT COUNT(*) FROM cliente WHERE cedula = :cedula";
        $stmCli = $this->LlamarConexion()->prepare($sqlCli);
        $stmCli->execute([':cedula' => $this->cedula_cliente]);
        if ($stmCli->fetchColumn() == 0) {
            return [
                'estado' => -1,
                'bool' => 0,
                'response' => ['resultado' => 404, 'icon' => 'error', 'mensaje' => "El cliente seleccionado no se encuentra registrado en el sistema."],
                'HTTP_STATUS' => ['codigo' => 404, 'mensaje' => "Cliente no encontrado"]
            ];
        }

        // 4. Si se asigna mesa: validar existencia, capacidad y disponibilidad
        if (!empty($this->id_mesa)) {
            $sqlMesa = "SELECT m.id_mesa, m.numero_mesa, m.capacidad, a.nombre as nombre_area 
                        FROM mesa m 
                        LEFT JOIN area_mesa a ON m.id_area = a.id_area 
                        WHERE m.id_mesa = :id_mesa";
            $stmMesa = $this->LlamarConexion()->prepare($sqlMesa);
            $stmMesa->execute([':id_mesa' => $this->id_mesa]);
            $datosMesa = $stmMesa->fetch(PDO::FETCH_ASSOC);

            if (!$datosMesa) {
                return [
                    'estado' => -1,
                    'bool' => 0,
                    'response' => ['resultado' => 404, 'icon' => 'error', 'mensaje' => "La mesa seleccionada no existe en el sistema."],
                    'HTTP_STATUS' => ['codigo' => 404, 'mensaje' => "Mesa no encontrada"]
                ];
            }

            // Verificación de capacidad
            if ($this->cantidad_personas > (int)$datosMesa['capacidad']) {
                $areaTexto = !empty($datosMesa['nombre_area']) ? " ({$datosMesa['nombre_area']})" : "";
                return [
                    'estado' => -1,
                    'bool' => 0,
                    'response' => [
                        'resultado' => 400,
                        'icon' => 'warning',
                        'mensaje' => "La Mesa #{$datosMesa['numero_mesa']}{$areaTexto} tiene capacidad para {$datosMesa['capacidad']} personas, pero la reservación es para {$this->cantidad_personas} comensales."
                    ],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Capacidad insuficiente"]
                ];
            }

            // Verificación de solapamiento de mesa
            $sqlDisp = "SELECT COUNT(*) FROM reservacion r 
                        JOIN asignacion_mesa am ON r.id_reservacion = am.id_reservacion
                        WHERE r.fecha = :fecha 
                        AND am.id_mesa = :id_mesa 
                        AND r.estado != 'CANCELADA'
                        AND ((r.hora < :hora_fin AND r.hora_fin > :hora))";
            $paramsDisp = [
                ':fecha' => $this->fecha,
                ':id_mesa' => $this->id_mesa,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin
            ];

            if (!empty($this->id_reservacion)) {
                $sqlDisp .= " AND r.id_reservacion != :id_excluir";
                $paramsDisp[':id_excluir'] = $this->id_reservacion;
            }

            $stmDisp = $this->LlamarConexion()->prepare($sqlDisp);
            $stmDisp->execute($paramsDisp);

            if ($stmDisp->fetchColumn() > 0) {
                return [
                    'estado' => -1,
                    'bool' => 0,
                    'response' => ['resultado' => 409, 'icon' => 'warning', 'mensaje' => "La mesa seleccionada ya se encuentra reservada en el horario especificado."],
                    'HTTP_STATUS' => ['codigo' => 409, 'mensaje' => "Horario no disponible para la mesa"]
                ];
            }
        } else {
            // Si es sin asignar mesa: verificar que el cliente no tenga otra reservación solapada
            $sqlDispCli = "SELECT COUNT(*) FROM reservacion r 
                           WHERE r.fecha = :fecha 
                           AND r.cedula_cliente = :cedula 
                           AND r.estado != 'CANCELADA'
                           AND ((r.hora < :hora_fin AND r.hora_fin > :hora))";
            $paramsDispCli = [
                ':fecha' => $this->fecha,
                ':cedula' => $this->cedula_cliente,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin
            ];

            if (!empty($this->id_reservacion)) {
                $sqlDispCli .= " AND r.id_reservacion != :id_excluir";
                $paramsDispCli[':id_excluir'] = $this->id_reservacion;
            }

            $stmDispCli = $this->LlamarConexion()->prepare($sqlDispCli);
            $stmDispCli->execute($paramsDispCli);

            if ($stmDispCli->fetchColumn() > 0) {
                return [
                    'estado' => -1,
                    'bool' => 0,
                    'response' => ['resultado' => 409, 'icon' => 'warning', 'mensaje' => "El cliente ya posee una reservación activa que coincide con este horario."],
                    'HTTP_STATUS' => ['codigo' => 409, 'mensaje' => "Conflicto de horario con el cliente"]
                ];
            }
        }

        return [
            'estado' => 1,
            'bool' => 1,
            'response' => ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Datos de reservación válidos y disponibles"],
            'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => "OK"]
        ];
    }

    // =========================================================================
    // Operaciones de Persistencia (Registrar, Modificar, Eliminar)
    // =========================================================================
    private function Registrar()
    {
        try {
            $this->LlamarConexion()->beginTransaction();
            $sql = "INSERT INTO reservacion(id_reservacion, cedula_cliente, fecha, hora, hora_fin, id_mesa, estado, cantidad_personas) 
                    VALUES (:id, :cedula, :fecha, :hora, :hora_fin, :id_mesa, :estado, :cantidad_personas)";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id' => $this->id_reservacion,
                ':cedula' => $this->cedula_cliente,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin,
                ':id_mesa' => $this->id_mesa,
                ':estado' => $this->estado,
                ':cantidad_personas' => $this->cantidad_personas
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
            return [
                'estado' => 1, 
                'bool' => 1,
                'response' => ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Reservación registrada con éxito"],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => "OK"]
            ];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            throw $e;
        }
    }

    private function Modificar()
    {
        try {
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE reservacion SET fecha = :fecha, hora = :hora, hora_fin = :hora_fin, id_mesa = :id_mesa, estado = :estado, cantidad_personas = :cantidad_personas 
                    WHERE id_reservacion = :id";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id' => $this->id_reservacion,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':hora_fin' => $this->hora_fin,
                ':id_mesa' => $this->id_mesa,
                ':estado' => $this->estado,
                ':cantidad_personas' => $this->cantidad_personas
            ]);

            $sqlDelMesa = "DELETE FROM asignacion_mesa WHERE id_reservacion = :id";
            $stmDelMesa = $this->LlamarConexion()->prepare($sqlDelMesa);
            $stmDelMesa->execute([':id' => $this->id_reservacion]);

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
            return [
                'estado' => 1, 
                'bool' => 1,
                'response' => ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Reservación actualizada exitosamente"],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => "OK"]
            ];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
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
            return [
                'estado' => 1, 
                'bool' => 1,
                'response' => ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Reservación eliminada exitosamente"],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => "OK"]
            ];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            throw $e;
        }
    }

    // =========================================================================
    // Consultas de Eventos y Detalles (Optimizadas)
    // =========================================================================
    private function ListarEventos($filtros = [])
    {
        $sql = "SELECT r.id_reservacion, r.cedula_cliente, r.fecha, r.hora, r.hora_fin, r.estado, r.cantidad_personas,
                       p.nombre, p.apellido, p.telefono, m.numero_mesa, COALESCE(am.id_mesa, r.id_mesa) as id_mesa, m.capacidad, a.nombre as area_nombre 
                FROM reservacion r 
                JOIN persona p ON r.cedula_cliente = p.cedula 
                LEFT JOIN asignacion_mesa am ON r.id_reservacion = am.id_reservacion
                LEFT JOIN mesa m ON (m.id_mesa = COALESCE(am.id_mesa, r.id_mesa))
                LEFT JOIN area_mesa a ON m.id_area = a.id_area
                WHERE 1=1";
        
        $params = [];
        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $sql .= " AND r.fecha BETWEEN :desde AND :hasta";
            $params[':desde'] = $filtros['desde'];
            $params[':hasta'] = $filtros['hasta'];
        }

        $sql .= " ORDER BY r.fecha ASC, r.hora ASC";

        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute($params);
        $res = $stm->fetchAll(PDO::FETCH_ASSOC);

        $eventos = [];
        foreach ($res as $r) {
            $mesaTxt = $r['numero_mesa'] ? " (Mesa #{$r['numero_mesa']})" : "";
            $eventos[] = [
                'id' => $r['id_reservacion'],
                'title' => $r['nombre'] . ' ' . $r['apellido'] . $mesaTxt,
                'start' => $r['fecha'] . 'T' . $r['hora'],
                'end' => $r['fecha'] . 'T' . $r['hora_fin'],
                'editable' => ($r['estado'] === 'PENDIENTE'),
                'extendedProps' => [
                    'cedula' => $r['cedula_cliente'],
                    'nombre' => $r['nombre'],
                    'apellido' => $r['apellido'],
                    'telefono' => $r['telefono'],
                    'estado' => $r['estado'],
                    'id_mesa' => $r['id_mesa'],
                    'numero_mesa' => $r['numero_mesa'],
                    'capacidad' => $r['capacidad'],
                    'area_nombre' => $r['area_nombre'],
                    'cantidad_personas' => (int)($r['cantidad_personas'] ?? 1)
                ],
                'className' => 'status-' . strtolower($r['estado'])
            ];
        }

        return [
            'estado' => 1, 
            'bool' => 1,
            'response' => ['resultado' => 200, 'datos' => $eventos],
            'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => "OK"]
        ];
    }

    private function ObtenerDetalle()
    {
        $sql = "SELECT r.id_reservacion, r.cedula_cliente, r.fecha, r.hora, r.hora_fin, r.estado, r.cantidad_personas,
                       p.nombre, p.apellido, p.telefono, p.correo, m.numero_mesa, COALESCE(am.id_mesa, r.id_mesa) as id_mesa, m.capacidad, a.nombre as area_nombre 
                FROM reservacion r 
                JOIN persona p ON r.cedula_cliente = p.cedula 
                LEFT JOIN asignacion_mesa am ON r.id_reservacion = am.id_reservacion
                LEFT JOIN mesa m ON (m.id_mesa = COALESCE(am.id_mesa, r.id_mesa))
                LEFT JOIN area_mesa a ON m.id_area = a.id_area
                WHERE r.id_reservacion = :id";
        
        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute([':id' => $this->id_reservacion]);
        $res = $stm->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            return [
                'estado' => 1, 
                'bool' => 1,
                'response' => ['resultado' => 200, 'registro' => $res],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => "OK"]
            ];
        }
        return [
            'estado' => -1, 
            'bool' => 0,
            'response' => ['resultado' => 404, 'mensaje' => "Reservación no encontrada"],
            'HTTP_STATUS' => ['codigo' => 404, 'mensaje' => "No encontrada"]
        ];
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
