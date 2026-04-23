<?php
namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Bitacora extends Database {
    private $id_bitacora;
    private $cedula;
    private $modulo;
    private $accion;
    private $detalle;
    private $ip_address;
    private $valores_anteriores;
    private $valores_nuevos;
    private $fecha;

    public function __construct() {
        $this->id_bitacora = "";
        $this->cedula = "";
        $this->modulo = "";
        $this->accion = "";
        $this->detalle = "";
        $this->ip_address = "";
        $this->valores_anteriores = NULL;
        $this->valores_nuevos = NULL;
        $this->fecha = "";
    }

    // SETTERS CON VALIDACIÓN (RegexHelper)
    public function setIdBitacora($id) { 
        if (!empty($id) && RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de bitácora no tiene un formato válido.");
        }
        $this->id_bitacora = $id; 
    }

    public function set_cedula($c) { 
        // Permitir 'Sistema' o validar formato de cédula real
        if (!empty($c) && $c !== 'Sistema' && RegexHelper::ValidarFormatos($c, 'Cedula') == 0) {
            throw new Exception("La cédula en bitácora no tiene un formato válido (Ej: V-12345678).");
        }
        $this->cedula = $c; 
    }

    public function set_modulo($m) { 
        if (RegexHelper::ValidarFormatos($m, 'Objeto') == 0) {
            throw new Exception("El nombre del módulo no es válido.");
        }
        $this->modulo = $m; 
    }

    public function set_accion($a) { 
        if (RegexHelper::ValidarFormatos($a, 'Objeto') == 0) {
            throw new Exception("La acción no cumple con el formato permitido.");
        }
        $this->accion = $a; 
    }

    public function set_detalle($d) { $this->detalle = $d; }
    public function set_ip_address($ip) { $this->ip_address = $ip; }
    public function set_anteriores($val) { $this->valores_anteriores = $val; }
    public function set_nuevos($val) { $this->valores_nuevos = $val; }
    public function set_fecha($f) { $this->fecha = $f; }

    public function Transaccion($peticion) {
        $response = false;
        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'listar' => $this->listarBitacora($peticion['filtros'] ?? []),
                'registrar' => $this->Registrar(),
                default => false
            };
        }
        return $response;
    }

    private function listarBitacora($filtros = []) {
        $arreglo = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT 
                        b.id_bitacora,
                        b.modulo,
                        b.accion,
                        b.detalle,
                        b.ip_address,
                        b.fecha,
                        b.valores_anteriores,
                        b.valores_nuevos,
                        u.username,
                        u.cedula,
                        r.nombre_rol as rol
                    FROM bitacora b
                    LEFT JOIN usuario u ON b.cedula = u.cedula
                    LEFT JOIN rol r ON u.id_rol = r.id_rol";
            
            $where = [];
            $params = [];

            if (!empty($filtros['modulo'])) {
                $where[] = "b.modulo = :modulo";
                $params['modulo'] = $filtros['modulo'];
            }

            if (!empty($filtros['desde'])) {
                $where[] = "b.fecha >= :desde";
                $params['desde'] = $filtros['desde'] . " 00:00:00";
            }

            if (!empty($filtros['hasta'])) {
                $where[] = "b.fecha <= :hasta";
                $params['hasta'] = $filtros['hasta'] . " 23:59:59";
            }

            if (count($where) > 0) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY b.fecha DESC";
            
            $stmt = $this->LlamarConexion()->prepare($sql);
            $stmt->execute($params);
            $arreglo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->LlamarConexion()->commit();
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog("Error en listarBitacora: " . $e->getMessage());
            $arreglo = [];
        }
        $this->DestruirConexion();
        return $arreglo;
    }

    private function Registrar() {
        $result = false;
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $this->ip_address = $this->ip_address ?: ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            
            $sql = "INSERT INTO bitacora (
                        id_bitacora, 
                        cedula, 
                        modulo, 
                        accion, 
                        detalle,
                        ip_address,
                        valores_anteriores,
                        valores_nuevos,
                        fecha
                    ) VALUES (
                        :id_bitacora,
                        :cedula, 
                        :modulo, 
                        :accion,
                        :detalle,
                        :ip_address,
                        :old,
                        :new,
                        :fecha
                    )";

            $stmt = $this->LlamarConexion()->prepare($sql);
            
            $result = $stmt->execute([
                'id_bitacora' => $this->id_bitacora,
                'cedula' => $this->cedula,
                'modulo' => $this->modulo,
                'accion' => $this->accion,
                'detalle' => $this->detalle ?? null,
                'ip_address' => $this->ip_address,
                'old' => $this->valores_anteriores ?? null,
                'new' => $this->valores_nuevos ?? null,
                'fecha' => $this->fecha ?? date('Y-m-d H:i:s')
            ]);
            
            $this->LlamarConexion()->commit();
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog("Error en Bitacora::Registrar: " . $e->getMessage());
            $result = false;
        }
        $this->DestruirConexion();
        return $result;
    }
}