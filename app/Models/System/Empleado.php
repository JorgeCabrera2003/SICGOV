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
        $ced = strtoupper(trim($this->cedula));
        $cedSin = str_replace('-', '', $ced);

        // Asegurar formato con guion para comparaciones
        $cedConGuion = $ced;
        if (!str_contains($cedConGuion, '-') && strlen($cedConGuion) >= 2) {
            $cedConGuion = $cedConGuion[0] . '-' . substr($cedConGuion, 1);
        }

        // Consultar uniendo persona + empleado y traer nombre y cargo (si existe)
        $sql = "SELECT p.cedula as cedula_personal, p.nombre as nombre_personal, p.apellido as apellido_personal,
                       e.cedula as cedula_empleado, e.id_cargo, c.nombre_cargo as cargo, e.fecha_ingreso
                FROM persona p
                INNER JOIN empleado e ON p.cedula = e.cedula
                LEFT JOIN cargo c ON e.id_cargo = c.id_cargo
                WHERE p.cedula = :ced1 OR p.cedula = :ced2 OR REPLACE(p.cedula, '-', '') = :cedSin
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ced1' => $cedConGuion, 'ced2' => str_replace('-', '', $cedConGuion), 'cedSin' => $cedSin]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Si no se encuentra con join, intentar buscar sólo en persona (por si hay registros mal normalizados)
        if (!$row) {
            $sql2 = "SELECT cedula as cedula_personal, nombre as nombre_personal, apellido as apellido_personal FROM persona WHERE cedula = :ced1 OR cedula = :ced2 OR REPLACE(cedula, '-', '') = :cedSin LIMIT 1";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute(['ced1' => $cedConGuion, 'ced2' => str_replace('-', '', $cedConGuion), 'cedSin' => $cedSin]);
            $row = $stmt2->fetch(\PDO::FETCH_ASSOC);
        }

        return $row;
    }
}