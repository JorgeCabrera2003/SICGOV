<?php
namespace App\Models\System;

use App\Core\Database;

class Empleado {
    private $db;
    private $cedula;

    public function __construct() {
        // Conexión por defecto a 'business' (goobv-sistema)
        $this->db = Database::getConnection('business');
    }

    public function set_cedula($c) { $this->cedula = $c; }

    public function obtenerDatos() {
        $sql = "SELECT * FROM personal WHERE cedula_personal = :cedula";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $this->cedula]);
        return $stmt->fetch();
    }
}