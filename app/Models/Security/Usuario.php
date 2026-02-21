<?php
namespace App\Models\Security;

use App\Core\Database;
use PDO;

class Usuario {
    private $cedula;
    private $clave;
    private $db;

    public function __construct() {
        $this->db = Database::getConnection('security');
    }

    public function set_cedula($c) { $this->cedula = $c; }
    public function set_clave($k) { $this->clave = $k; }

    public function Transaccion($peticion) {
        switch ($peticion['peticion']) {
            case 'sesion':
                return $this->IniciarSesion();
            case 'perfil':
                return $this->PerfilUsuario();
        }
        return false;
    }

    private function IniciarSesion() {
        $sql = "SELECT clave FROM usuario WHERE cedula = :cedula AND estatus = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $this->cedula]);
        $user = $stmt->fetch();

        return ($user && $this->clave === $user['clave']);
    }

    private function PerfilUsuario() {
        $sql = "SELECT * FROM usuario WHERE cedula = :cedula";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $this->cedula]);
        return ['datos' => $stmt->fetch()];
    }
}