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
    private $fecha;
    private $hora;

    public function __construct() {
        $this->db = Database::getConnection('security');
    }

    public function set_usuario($u) { $this->usuario = $u; }
    public function set_modulo($m) { $this->modulo = $m; }
    public function set_accion($a) { $this->accion = $a; }
    public function set_fecha($f) { $this->fecha = $f; }
    public function set_hora($h) { $this->hora = $h; }

    public function Transaccion($peticion) {
        if ($peticion['peticion'] === 'registrar') {
            return $this->Registrar();
        }
        return false;
    }

    private function Registrar() {
        try {
            // Generar ID único para la bitácora
            $this->id_bitacora = $this->generarIdBitacora();
            
            $sql = "INSERT INTO bitacora (id_bitacora, id_usuario, modulo, accion, fecha) 
                    VALUES (
                        :id_bitacora,
                        (SELECT id_usuario FROM usuario WHERE cedula = :cedula LIMIT 1), 
                        :modulo, 
                        :accion, 
                        :fecha
                    )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_bitacora' => $this->id_bitacora,
                'cedula' => $this->usuario,
                'modulo' => $this->modulo,
                'accion' => $this->accion,
                'fecha'  => $this->fecha . ' ' . $this->hora
            ]);
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en Bitacora::Registrar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un ID único para la bitácora
     * Formato: BIT + timestamp + random
     * Ejemplo: BIT202502171234561234
     */
    private function generarIdBitacora() {
        $prefijo = 'BIT';
        $fecha = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefijo . $fecha . $random;
    }
}