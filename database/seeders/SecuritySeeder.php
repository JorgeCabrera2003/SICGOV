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
            $sqlRoles = "INSERT IGNORE INTO `rol` (`id_rol`, `nombre_rol`, `estatus`) VALUES 
                ('ADMIN00120251001', 'ADMINISTRADOR', '1'),
                ('GEREN00520251001', 'GERENTE', '1'),
                ('ROLS5171202607091004255171', 'Cajero', '1'),
                ('ROLS71920260602140652719', 'SuperUsuario', '1'),
                ('ROLS74320260602130629743', 'Cliente', '1')";
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

        // Insertar Usuarios Reales
        $sqlUsuarios = <<<'SQL'
INSERT IGNORE INTO `usuario` (`cedula`, `id_rol`, `username`, `clave`, `tema`, `ultimo_acceso`, `fecha_registro`, `estatus`, `estatus_clave`, `token_recuperacion`, `fecha_expiracion_token`) VALUES 
            ('V-00000000', 'ROLS71920260602140652719', 'admin_root', '$2y$10$kQ4Pztytm9/sb.G1pI8gv.jTwCg5l9EBDUzEilJ8LtPNc9/12YDcW', 'light', NULL, '2026-07-09 00:56:09', '1', '1', NULL, NULL),
            ('V-12345677', 'ROLS5171202607091004255171', 'Leizer', '$2y$10$Mo/sm7QBLzf38QuJeDPWhunT5.t8LICYWgQnmq7r1s4RfUYcx/HbG', 'light', NULL, '2026-07-09 10:07:30', '1', '1', NULL, NULL),
            ('V-12345678', 'GEREN00520251001', 'Jorge', '$2y$10$ImrLiTTTVgrSlE0SNUcvv.Ms36SMxvbdJrwh6Ymd9u/BEbaq5.Sv.', 'light', NULL, '2026-07-09 00:56:09', '1', '1', NULL, NULL),
            ('V-28165452', 'ROLS74320260602130629743', 'San', '$2y$10$o2gsVbGaLAM21Jh0vrsGx.Q6BEFyBFJC4BXQhHiWiJ3p3/ooc7ria', 'light', NULL, '2026-07-09 09:17:16', '1', '1', NULL, NULL)
SQL;
        $this->db->exec($sqlUsuarios);
        echo "       Usuarios base insertados.\n";
    }
}