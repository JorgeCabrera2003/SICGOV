<?php
namespace App\Models\Security;

use App\Core\Database;
use PDO;

class Bitacora {
    private $db;
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
        $this->db = Database::getConnection('security');
    }

    public function setIdBitacora($id) { $this->id_bitacora = $id; }
    public function set_cedula($c) { $this->cedula = $c; }
    public function set_modulo($m) { $this->modulo = $m; }
    public function set_accion($a) { $this->accion = $a; }
    public function set_detalle($d) { $this->detalle = $d; }
    public function set_ip_address($ip) { $this->ip_address = $ip; }
    public function set_anteriores($val) { $this->valores_anteriores = $val; }
    public function set_nuevos($val) { $this->valores_nuevos = $val; }
    public function set_fecha($f) { $this->fecha = $f; }

    public function Transaccion($peticion) {
        switch ($peticion['peticion']) {
            case 'listar':
                return $this->listarBitacora($peticion['filtros'] ?? []);
            case 'registrar':
                return $this->Registrar();
            default:
                return false;
        }
    }

    private function listarBitacora($filtros = []) {
        try {
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
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("Error en listarBitacora: " . $e->getMessage());
            return [];
        }
    }

    private function Registrar() {
        try {
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

            $stmt = $this->db->prepare($sql);
            
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
            
            return $result;
            
        } catch (\PDOException $e) {
            error_log("Error en Bitacora::Registrar: " . $e->getMessage());
            return false;
        }
    }
}