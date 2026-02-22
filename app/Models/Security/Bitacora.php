<?php
namespace App\Models\Security;

use App\Core\Database;
use PDO;

class Bitacora {
    private $db;
    private $id_bitacora;
    private $usuario;
    private $modulo;
    private $accion;
    private $detalles;
    private $ip_address;
    private $fecha;

    public function __construct() {
        $this->db = Database::getConnection('security');
    }

    // Getters y Setters
    public function set_usuario($u) { $this->usuario = $u; }
    public function set_modulo($m) { $this->modulo = $m; }
    public function set_accion($a) { $this->accion = $a; }
    public function set_detalles($d) { $this->detalles = $d; }
    public function set_ip_address($ip) { $this->ip_address = $ip; }
    public function set_fecha($f) { $this->fecha = $f; }

    public function Transaccion($peticion) {
        switch ($peticion['peticion']) {
            case 'listar':
                return $this->listarBitacora();
            case 'registrar':
                return $this->Registrar();
            default:
                return false;
        }
    }

    private function listarBitacora() {
        try {
            $sql = "SELECT 
                        b.id_bitacora,
                        b.modulo,
                        b.accion,
                        b.detalles,
                        b.ip_address,
                        b.fecha,
                        u.username,
                        u.nombres,
                        u.apellidos,
                        u.cedula
                    FROM bitacora b
                    LEFT JOIN usuario u ON b.id_usuario = u.id_usuario
                    ORDER BY b.fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("Error en listarBitacora: " . $e->getMessage());
            return [];
        }
    }

    private function Registrar() {
        try {
            $this->id_bitacora = $this->generarIdBitacora();
            
            // Obtener IP del cliente
            $this->ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            $sql = "INSERT INTO bitacora (
                        id_bitacora, 
                        id_usuario, 
                        modulo, 
                        accion, 
                        detalles,
                        ip_address,
                        fecha
                    ) VALUES (
                        :id_bitacora,
                        (SELECT id_usuario FROM usuario WHERE cedula = :cedula OR id_usuario = :id_usuario LIMIT 1), 
                        :modulo, 
                        :accion,
                        :detalles,
                        :ip_address,
                        :fecha
                    )";

            $stmt = $this->db->prepare($sql);
            
            // Determinar si tenemos cédula o id_usuario
            $idUsuario = null;
            $cedula = null;
            
            if (isset($this->usuario)) {
                if (strpos($this->usuario, 'V-') === 0 || strpos($this->usuario, 'E-') === 0) {
                    $cedula = $this->usuario;
                } else {
                    $idUsuario = $this->usuario;
                }
            }
            
            $result = $stmt->execute([
                'id_bitacora' => $this->id_bitacora,
                'cedula' => $cedula,
                'id_usuario' => $idUsuario,
                'modulo' => $this->modulo,
                'accion' => $this->accion,
                'detalles' => $this->detalles ?? null,
                'ip_address' => $this->ip_address,
                'fecha' => $this->fecha ?? date('Y-m-d H:i:s')
            ]);
            
            return $result;
            
        } catch (\PDOException $e) {
            error_log("Error en Bitacora::Registrar: " . $e->getMessage());
            return false;
        }
    }

    private function generarIdBitacora() {
        $prefijo = 'BIT';
        $fecha = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefijo . $fecha . $random;
    }
}