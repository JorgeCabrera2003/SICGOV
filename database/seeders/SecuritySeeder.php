<?php
namespace App\Database\Seeders;

class SecuritySeeder
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function run()
    {
        // Verificar e insertar roles
        $count = $this->db->query("SELECT COUNT(*) FROM rol")->fetchColumn();
        $hash = password_hash("1234", PASSWORD_DEFAULT);

        if ($count == 0) {
            echo "       Roles no encontrados, insertando...\n";
            $sqlRoles = "INSERT INTO rol (id_rol, nombre_rol, estatus) VALUES 
                ('ADMIN00120251001', 'ADMINISTRADOR', 1),
                ('GEREN00520251001', 'GERENTE', 1),
                ('ROLS74320260602130629743', 'Cliente', 1),
                ('ROLS71920260602140652719', 'SuperUsuario', 1)";
            $this->db->exec($sqlRoles);
            echo "       Roles base creados.\n";
        }

        // Verificar e insertar módulos básicos
        $countModulos = $this->db->query("SELECT COUNT(*) FROM modulo")->fetchColumn();
        if ($countModulos == 0) {
            echo "       Módulos no encontrados, insertando...\n";
            $sqlModulos = "INSERT INTO modulo (id_modulo, nombre, estatus) VALUES 
                ('AREAM0000720260519200547232', 'area_mesa', 1),
                ('ASIST0001020260519200547232', 'asistencia', 1),
                ('BITAC0000320260519200547232', 'bitacora', 1),
                ('CARGO0001120260519200547232', 'cargo', 1),
                ('CATIN0001520260519200547232', 'categoria_insumo', 1),
                ('CATME0001620260519200547232', 'categoria_menu', 1),
                ('CLIEN0002020260519200547232', 'cliente', 1),
                ('EMPLE0001220260519200547232', 'empleado', 1),
                ('HORAR0001320260519200547232', 'horario', 1),
                ('INSUM0001720260519200547232', 'insumo', 1),
                ('MANTE0000420260519200547232', 'mantenimiento', 1),
                ('MESA00000620260519200547232', 'mesa', 1),
                ('METOP0032020260519200547232', 'metodo_pago', 1),
                ('MODUL0000520260519200547232', 'modulo_sistema', 1),
                ('MULTI0000820260519200547232', 'multimedia', 1),
                ('NOTIC0000920260519200547232', 'noticia', 1),
                ('PAGOC0022020260519200547232', 'pago', 1),
                ('PEDID0002020260519200547232', 'pedido', 1),
                ('PERML0011420260519200547232', 'permiso_laboral', 1),
                ('PRODU0001820260519200547232', 'producto', 1),
                ('PROMO0012020260519200547232', 'promocion', 1),
                ('PROVE0001920260519200547232', 'proveedor', 1),
                ('RESER0002120260519200547232', 'reservacion', 1),
                ('ROL000000220260519200547232', 'rol', 1),
                ('TIPOP0021420260519200547232', 'tipo_permiso', 1),
                ('TURNO0001420260519200547232', 'turno', 1),
                ('USUAR0000120260519200547232', 'usuario', 1);";
            $this->db->exec($sqlModulos);
            echo "       Módulos base creados.\n";
        }

        // Crear usuario admin si no existe
        $sqlCheck = "SELECT COUNT(*) FROM usuario WHERE cedula = 'V-00000000'";
        $userExists = $this->db->query($sqlCheck)->fetchColumn();

        if (!$userExists) {
            $sqlAdmin = "INSERT INTO usuario
                        (cedula, id_rol, username, clave, tema, estatus)
                        VALUES 
                        ('V-00000000', 'ROLS71920260602140652719', 'admin_root', :clave, 'light', 1)";
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
        $sqlCheckGerente = "SELECT COUNT(*) FROM usuario WHERE cedula = 'V-12345678'";
        $gerenteExists = $this->db->query($sqlCheckGerente)->fetchColumn();

        if (!$gerenteExists) {
            $sqlGerente = "INSERT INTO usuario
                        (cedula, id_rol, username, clave, tema, estatus)
                        VALUES 
                        ('V-12345678', 'GEREN00520251001', 'gerente', :clave, 'light', 1)";
            try {
                $stmt = $this->db->prepare($sqlGerente);
                $stmt->execute(['clave' => $hash]);
                echo "       Usuario Gerente de ejemplo creado (cedula: V-12345678).\n";
            } catch (\PDOException $e) {
                echo "       Aviso: No se pudo crear usuario gerente: " . $e->getMessage() . "\n";
            }
        }
    }
}