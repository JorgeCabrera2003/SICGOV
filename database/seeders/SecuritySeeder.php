<?php
namespace App\Database\Seeders;

class SecuritySeeder {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function run() {
        // Verificar e insertar roles
        $count = $this->db->query("SELECT COUNT(*) FROM rol")->fetchColumn();
        $hash = password_hash("1234", PASSWORD_DEFAULT);
        
        if ($count == 0) {
            echo "       Roles no encontrados, insertando...\n";
            $sqlRoles = "INSERT INTO rol (id_rol, nombre_rol, estatus) VALUES 
                ('ADMIN00120251001', 'ADMINISTRADOR', 1),
                ('GEREN00520251001', 'GERENTE', 1)";
            $this->db->exec($sqlRoles);
            echo "       Roles base creados.\n";
        }

        // Verificar e insertar módulos básicos
        $countModulos = $this->db->query("SELECT COUNT(*) FROM modulo")->fetchColumn();
        if ($countModulos == 0) {
            echo "       Módulos no encontrados, insertando...\n";
            $sqlModulos = "INSERT INTO modulo (id_modulo, nombre, estatus) VALUES 
                ('MOD001', 'Dashboard', 1),
                ('MOD002', 'Usuarios', 1),
                ('MOD003', 'Roles', 1),
                ('MOD004', 'Permisos', 1),
                ('MOD005', 'Pedidos', 1),
                ('MOD006', 'Productos', 1),
                ('MOD007', 'Inventario', 1),
                ('MOD008', 'Mesas', 1),
                ('MOD009', 'Reportes', 1),
                ('MOD010', 'Personal', 1),
                ('MOD011', 'Clientes', 1),
                ('MOD012', 'Proveedores', 1),
                ('MOD013', 'Promociones', 1),
                ('MOD014', 'Noticias', 1),
                ('MOD015', 'Notificaciones', 1),
                ('MOD016', 'Bitácora', 1)";
            $this->db->exec($sqlModulos);
            echo "       Módulos base creados.\n";
        }

        // Verificar e insertar permisos para ADMINISTRADOR
        $countPermisos = $this->db->query("SELECT COUNT(*) FROM permiso WHERE id_rol = 'ADMIN00120251001'")->fetchColumn();
        if ($countPermisos == 0) {
            echo "       Permisos para ADMINISTRADOR no encontrados, insertando...\n";
            $modulos = $this->db->query("SELECT id_modulo FROM modulo")->fetchAll(\PDO::FETCH_COLUMN);
            $acciones = ['LEER', 'CREAR', 'EDITAR', 'ELIMINAR'];
            
            $sqlPermiso = "INSERT INTO permiso (id_permiso, id_rol, id_modulo, accion, estatus) VALUES (:id, 'ADMIN00120251001', :modulo, :accion, 1)";
            $stmt = $this->db->prepare($sqlPermiso);
            
            foreach ($modulos as $modulo) {
                foreach ($acciones as $accion) {
                    $stmt->execute([
                        'id' => 'PERM-' . uniqid(),
                        'modulo' => $modulo,
                        'accion' => $accion
                    ]);
                }
            }
            echo "       Permisos para ADMINISTRADOR creados.\n";
        }

        // Crear usuario admin si no existe
        $sqlCheck = "SELECT COUNT(*) FROM usuario WHERE cedula = 'V00000000'";
        $userExists = $this->db->query($sqlCheck)->fetchColumn();

        if (!$userExists) {
            $sqlAdmin = "INSERT INTO usuario
                        (cedula, id_rol, username, clave, tema, estatus)
                        VALUES 
                        ('V00000000', 'ADMIN00120251001', 'admin_root', :clave, 'light', 1)";
            try {
                $stmt = $this->db->prepare($sqlAdmin);
                $stmt->execute(['clave' => $hash]);
                echo "       Usuario Admin Root creado.\n";
            } catch (\PDOException $e) {
                echo "       Error al crear admin: " . $e->getMessage() . "\n";
            }
        } else {
            echo "       El usuario admin ya existe.\n";
        }

        // Crear usuario gerente de ejemplo
        $sqlCheckGerente = "SELECT COUNT(*) FROM usuario WHERE cedula = 'V12345678'";
        $gerenteExists = $this->db->query($sqlCheckGerente)->fetchColumn();

        if (!$gerenteExists) {
            $sqlGerente = "INSERT INTO usuario
                        (cedula, id_rol, username, clave, tema, estatus)
                        VALUES 
                        ('V12345678', 'GEREN00520251001', 'gerente', :clave, 'light', 1)";
            try {
                $stmt = $this->db->prepare($sqlGerente);
                $stmt->execute(['clave' => $hash]);
                echo "       Usuario Gerente de ejemplo creado (cedula: V12345678).\n";
            } catch (\PDOException $e) {
                echo "       Aviso: No se pudo crear usuario gerente: " . $e->getMessage() . "\n";
            }
        }
    }
}