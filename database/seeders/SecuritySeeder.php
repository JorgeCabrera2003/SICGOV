<?php
namespace App\Database\Seeders;

class SecuritySeeder {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function run() {
        $count = $this->db->query("SELECT COUNT(*) FROM rol")->fetchColumn();
        if ($count == 0) {
            echo "       Roles no encontrados, insertando...\n";
            $sqlRoles = "INSERT INTO rol (id_rol, nombre_rol, descripcion, estatus) VALUES 
                ('ADMIN00120251001', 'ADMINISTRADOR', 'Acceso completo al sistema', 1),
                ('GEREN00520251001', 'GERENTE', 'Supervisión y reportes', 1)";
            $this->db->exec($sqlRoles);
            echo "       Roles base creados.\n";
        }

        $sqlCheck = "SELECT COUNT(*) FROM usuario WHERE cedula = 'V00000000'";
        $userExists = $this->db->query($sqlCheck)->fetchColumn();

        if (!$userExists) {
            $sqlAdmin = "INSERT INTO usuario 
                        (id_usuario, cedula, id_rol, username, nombres, apellidos, correo, clave, estatus) 
                        VALUES 
                        ('SUPER00720251001', 'V00000000', 'ADMIN00120251001', 'admin_root', 'Admin', 'Principal', 'admin@goodvibes.com', '1234', 1)";
            try {
                $this->db->exec($sqlAdmin);
                echo "       Usuario Admin Root creado.\n";
            } catch (\PDOException $e) {
                echo "       Error al crear admin: " . $e->getMessage() . "\n";
            }
        } else {
            echo "       El usuario admin ya existe.\n";
        }
    }
}