<?php
namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Mesas extends Database
{
private $id_mesa;
private $id_area;
private $numero_mesa;
private $capacidad;
private $estado; // Campo que mapea a 'estado' o 'estatus' según tu tabla
private $estatus; // tinyint(1)

public function __construct()
{
    $this->id_mesa = "";
    $this->id_area = "";
    $this->numero_mesa = 0;
    $this->capacidad = 0;
    $this->estado = "DISPONIBLE";
    $this->estatus = 1;
}

public function LlamarConexion($nombreBD = 'security', PDO &$pdo = NULL)
{
    return parent::LlamarConexion($nombreBD, $pdo);
}

// SETTERS CON VALIDACIÓN RIGUROSA (RegexHelper)
public function setIdMesa(string $id_mesa) { 
    if (RegexHelper::ValidarFormatos($id_mesa, 'ID') == 0) {
        throw new Exception("El ID de la mesa no cumple con el formato permitido.");
    }
    $this->id_mesa = $id_mesa; 
}

public function setIdArea(string $id_area) { 
    if (RegexHelper::ValidarFormatos($id_area, 'ID') == 0) {
        throw new Exception("El ID del área no cumple con el formato permitido.");
    }
    $this->id_area = $id_area; 
}

public function setNumeroMesa(int $numero_mesa) { 
    if ($numero_mesa <= 0 || $numero_mesa > 999) {
        throw new Exception("El número de mesa debe ser un valor entre 1 y 999.");
    }
    $this->numero_mesa = $numero_mesa; 
}

public function setCapacidad(int $capacidad) { 
    if ($capacidad < 1 || $capacidad > 50) {
        throw new Exception("La capacidad debe ser de al menos 1 persona y máximo 50 personas.");
    }
    $this->capacidad = $capacidad; 
}

public function setEstado(string $estado) { 
    $estados_validos = ['DISPONIBLE', 'LIBRE', 'OCUPADA', 'MANTENIMIENTO'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception("Estado no válido. Use: DISPONIBLE, LIBRE, OCUPADA o MANTENIMIENTO.");
    }
    $this->estado = $estado; 
}

public function setEstatus(int $estatus) { 
    if (!in_array($estatus, [0, 1])) {
        throw new Exception("El estatus debe ser 0 (inactivo) o 1 (activo).");
    }
    $this->estatus = $estatus; 
}

// GETTERS
public function getIdMesa() { 
    return $this->id_mesa; 
}

public function getIdArea() { 
    return $this->id_area; 
}

public function getNumeroMesa() { 
    return $this->numero_mesa; 
}

public function getCapacidad() { 
    return $this->capacidad; 
}

public function getEstado() { 
    return $this->estado; 
}

public function getEstatus() { 
    return $this->estatus; 
}

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarMesa(),
                'consultar' => $this->ConsultarMesas(),
                'consultar_una' => $this->ConsultarMesa($peticion['id_mesa'] ?? ''),
                'consultar_por_area' => $this->ConsultarMesasPorArea($peticion['id_area'] ?? ''),
                'actualizar', 'modificar' => $this->ModificarMesa(),
                'eliminar' => $this->EliminarMesa(),
                'cambiar_estado' => $this->CambiarEstadoMesa($peticion['estado'] ?? ''),
                'cambiar_estatus' => $this->CambiarEstatusMesa($peticion['estatus'] ?? null),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }

    private function ConsultarMesas()
{
    $dato = [];
    $arreglo = [];
    try {
        $this->LlamarConexion();
        $this->LlamarConexion()->beginTransaction();

        $sql = "SELECT m.*, a.nombre as area_nombre 
                FROM mesa m 
                LEFT JOIN area_mesa a ON m.id_area = a.id_area
                WHERE m.estatus = 1
                ORDER BY m.numero_mesa ASC";
        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $this->LlamarConexion()->commit();
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
    private function RegistrarMesa()
{
    $dato = [];
    try {
        $this->LlamarConexion();
        $this->LlamarConexion()->beginTransaction();

        $sql = "INSERT INTO mesa(id_mesa, id_area, numero_mesa, capacidad, estado, estatus)
                VALUES (:id_mesa, :id_area, :numero_mesa, :capacidad, :estado, :estatus)";

        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->bindParam(':id_mesa', $this->id_mesa);
        $stm->bindParam(':id_area', $this->id_area);
        $stm->bindParam(':numero_mesa', $this->numero_mesa);
        $stm->bindParam(':capacidad', $this->capacidad);
        $stm->bindParam(':estado', $this->estado);
        $stm->bindParam(':estatus', $this->estatus);
        $stm->execute();

        $this->LlamarConexion()->commit();

        $dato['estado'] = 1;
        $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Mesa registrada con éxito"];
        $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

    } catch (\PDOException $e) {
        if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
        
        // Verificar si es error de duplicado (código 1062)
        if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $dato['estado'] = 0;
            $dato['response'] = ['resultado' => 409, 'icon' => 'warning', 'mensaje' => "El ID de la mesa ya existe. Use un identificador diferente"];
            $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => "Conflicto - ID duplicado"];
        } else {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
    }
    $this->DestruirConexion();
    return $dato;
    }

// Modificar una mesa existente
private function ModificarMesa()
{
    $dato = [];
    try {
        $this->LlamarConexion();
        $this->LlamarConexion()->beginTransaction();

        $sql = "UPDATE mesa SET 
                    id_area = :id_area, 
                    numero_mesa = :numero_mesa, 
                    capacidad = :capacidad, 
                    estado = :estado, 
                    estatus = :estatus 
                WHERE id_mesa = :id_mesa";

        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->bindParam(':id_mesa', $this->id_mesa);
        $stm->bindParam(':id_area', $this->id_area);
        $stm->bindParam(':numero_mesa', $this->numero_mesa);
        $stm->bindParam(':capacidad', $this->capacidad);
        $stm->bindParam(':estado', $this->estado);
        $stm->bindParam(':estatus', $this->estatus);
        $stm->execute();

        $this->LlamarConexion()->commit();

        $dato['estado'] = 1;
        $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Mesa actualizada con éxito"];
        $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

    } catch (\PDOException $e) {
        if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
        Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
        $dato['estado'] = -1;
        $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
        $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
    }
    $this->DestruirConexion();
    return $dato;
}

// Eliminar una mesa (borrado lógico o físico)
private function EliminarMesa()
{
    $dato = [];
    try {
        $this->LlamarConexion();
        $this->LlamarConexion()->beginTransaction();

        // Borrado físico
        $sql = "DELETE FROM mesa WHERE id_mesa = :id_mesa";
        
        // O borrado lógico (recomendado):
        // $sql = "UPDATE mesa SET estatus = 0 WHERE id_mesa = :id_mesa";

        $stm = $this->LlamarConexion()->prepare($sql);
        $stm->bindParam(':id_mesa', $this->id_mesa);
        $stm->execute();

        $this->LlamarConexion()->commit();

        $dato['estado'] = 1;
        $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Mesa eliminada con éxito"];
        $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

    } catch (\PDOException $e) {
        if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
        Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
        $dato['estado'] = -1;
        $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
        $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
    }
    $this->DestruirConexion();
    return $dato;
}

}


