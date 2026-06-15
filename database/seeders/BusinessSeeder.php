<?php

namespace App\Database\Seeders;

use Faker\Factory;
use Faker\Provider\es_VE\PhoneNumber;

class BusinessSeeder
{
    private $db;
    private $faker;

    public function __construct($db)
    {
        $this->db = $db;
        $this->faker = Factory::create('es_VE');
        $this->faker->addProvider(new PhoneNumber($this->faker));
    }

    public function run()
    {
        $this->crearCargosBase();
        $this->crearCategoriasBase();
        $this->crearAreasMesa();
        $this->crearMetodosPago();
        $this->crearMesas();
        $this->crearPersonasYUsuarios();
        $this->crearProductosReales();
        $this->crearInsumosFalsos(20);
        $this->crearPreparaciones();
        $this->crearClientesFalsos(15);
        $this->crearProveedoresFalsos(5);
    }

    private function crearCargosBase()
    {
        $count = $this->db->query("SELECT COUNT(*) FROM cargo")->fetchColumn();
        if ($count == 0) {
            $sql = "INSERT INTO cargo (id_cargo, nombre_cargo, estatus) VALUES 
                ('CARGO00120260519200547232', 'Mesero', 1),
                ('CARGO00220260519200547232', 'Cocinero', 1),
                ('CARGO00320260519200547232', 'Cajero', 1),
                ('CARGO00420260519200547232', 'Bartender', 1),
                ('CARGO00520260519200547232', 'Gerente', 1),
                ('CARGO00620260519200547232', 'Supervisor', 1)";
            $this->db->exec($sql);
            echo "       Cargos base creados.\n";
        }
    }

    private function crearCategoriasBase()
    {
        // Categorías de productos
        $count = $this->db->query("SELECT COUNT(*) FROM categoria_producto")->fetchColumn();
        if ($count == 0) {
            $sql = "INSERT IGNORE INTO categoria_producto (`id_categoria`, `nombre_categoria`, `estatus`) VALUES 
            ('CAT202606111102317495', 'Menú Ejecutivo', '1'),
            ('CAT202606131540189625', 'Pepitos', '1'),
            ('CAT202606131540288211', 'Perros Calientes', '1'),
            ('CAT202606131540406364', 'Hamburguesas', '1'),
            ('CAT202606131540568458', 'Hot Maracaibo', '1'),
            ('CAT202606131541438741', 'Tapas', '1'),
            ('CAT202606131541589223', 'Menú Infantil', '1'),
            ('CAT202606131542228868', 'Mexicanisimo', '1'),
            ('CAT202606131542527898', 'Sandwiches', '1'),
            ('CATPROD00220260519200547232', 'Entradas', '1'),
            ('CATPROD00320260519200547232', 'Bebidas', '1'),
            ('CATPROD00520260519200547232', 'Ensaladas', '1')";
        $this->db->exec($sql);
            echo "       Categorías de productos creadas.\n";
        }

        // Categorías de insumos
        $countIng = $this->db->query("SELECT COUNT(*) FROM categoria_insumo")->fetchColumn();
        if ($countIng == 0) {
            $sql = "INSERT INTO categoria_insumo (id_categoria, nombre) VALUES 
                ('CATEGIN00120260519200547232', 'Carnes'),
                ('CATEGIN00220260519200547232', 'Verduras'),
                ('CATEGIN00320260519200547232', 'Lácteos'),
                ('CATEGIN00420260519200547232', 'Granos'),
                ('CATEGIN00520260519200547232', 'Condimentos'),
                ('CATEGIN00620260519200547232', 'Bebidas'),
                ('CATEGIN00720260519200547232', 'Frutas')";
            $this->db->exec($sql);
            echo "       Categorías de insumos creadas.\n";
        }
    }

    private function crearAreasMesa()
    {
        $count = $this->db->query("SELECT COUNT(*) FROM area_mesa")->fetchColumn();
        if ($count == 0) {
            $sql = "INSERT INTO area_mesa (id_area, nombre) VALUES 
                ('AREA00120260519200547232', 'Salón Principal'),
                ('AREA00220260519200547232', 'Terraza'),
                ('AREA00320260519200547232', 'VIP'),
                ('AREA00420260519200547232', 'Barra')";
            $this->db->exec($sql);
            echo "       Áreas de mesa creadas.\n";
        }
    }

    private function crearMetodosPago()
    {
        $count = $this->db->query("SELECT COUNT(*) FROM metodo_pago")->fetchColumn();
        if ($count == 0) {
            $sql = "INSERT INTO metodo_pago (id_metodo_pago, nombre) VALUES 
                ('METOD00120260519200547232', 'Efectivo'),
                ('METOD00220260519200547232', 'Tarjeta de Crédito'),
                ('METOD00320260519200547232', 'Tarjeta de Débito'),
                ('METOD00420260519200547232', 'Pago Móvil'),
                ('METOD00520260519200547232', 'Transferencia')";
            $this->db->exec($sql);
            echo "       Métodos de pago creados.\n";
        }
    }

    private function crearMesas()
    {
        $this->db->exec("DELETE FROM mesa");

        // Obtener áreas
        $areas = $this->db->query("SELECT id_area FROM area_mesa")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($areas)) {
            $areas = ['AREA001'];
        }

        $sql = "INSERT INTO mesa (id_mesa, id_area, numero_mesa, capacidad, estado, estatus) VALUES 
                (:id, :area, :num, :cap, 'DISPONIBLE', 1)";
        $stmt = $this->db->prepare($sql);

        $mesaNum = 1;
        foreach ($areas as $area) {
            for ($i = 0; $i < 4; $i++) {
                $stmt->execute([
                    'id' => 'MESA' . str_pad($mesaNum, 3, '0', STR_PAD_LEFT) . date('Ymd'),
                    'area' => $area,
                    'num' => $mesaNum,
                    'cap' => $this->faker->randomElement([2, 4, 6, 8])
                ]);
                $mesaNum++;
            }
        }
        echo "       " . ($mesaNum - 1) . " mesas creadas.\n";
    }

    private function crearPersonasYUsuarios()
    {
        // Crear persona para admin root
        $sqlCheckPersona = "SELECT COUNT(*) FROM persona WHERE cedula = 'V-00000000'";
        $personaExists = $this->db->query($sqlCheckPersona)->fetchColumn();
        
        if (!$personaExists) {
            $sqlPersona = "INSERT INTO persona (cedula, nombre, apellido, telefono, correo, sexo) 
                          VALUES ('V-00000000', 'Admin', 'Principal', '04120000000', 'admin@goodvibes.com', 'M')";
            $this->db->exec($sqlPersona);
            echo "       Persona para Admin Root creada.\n";
        }

        // Crear persona para gerente
        $sqlCheckGerente = "SELECT COUNT(*) FROM persona WHERE cedula = 'V-12345678'";
        $gerenteExists = $this->db->query($sqlCheckGerente)->fetchColumn();
        
        if (!$gerenteExists) {
            $sqlPersona = "INSERT INTO persona (cedula, nombre, apellido, telefono, correo, sexo) 
                          VALUES ('V-12345678', 'Gerente', 'General', '04120000001', 'gerente@goodvibes.com', 'M')";
            $this->db->exec($sqlPersona);
            echo "       Persona para Gerente creada.\n";
        }

        // Crear empleados con personas
        $this->crearEmpleadosFalsos(5);
    }

    private function crearEmpleadosFalsos($cantidad)
    {
        $cargos = $this->db->query("SELECT id_cargo FROM cargo")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($cargos)) {
            $cargos = ['CARGO00120260519200547232'];
        }

        $sqlPersona = "INSERT INTO persona (cedula, nombre, apellido, fecha_nacimiento, telefono, correo, direccion, sexo) 
                      VALUES (:cedula, :nombre, :apellido, :fecha_nac, :tel, :correo, :dir, :sexo)";
        $sqlEmpleado = "INSERT INTO empleado (cedula, id_cargo, fecha_ingreso) VALUES (:cedula, :cargo, :fecha_ingreso)";

        $stmtPersona = $this->db->prepare($sqlPersona);
        $stmtEmpleado = $this->db->prepare($sqlEmpleado);

        for ($i = 0; $i < $cantidad; $i++) {
            $cedula = 'V-' . $this->faker->unique()->numberBetween(10000000, 99999999);
            $fechaIngreso = $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
            
            try {
                $stmtPersona->execute([
                    'cedula' => $cedula,
                    'nombre' => $this->faker->firstName(),
                    'apellido' => $this->faker->lastName(),
                    'fecha_nac' => $this->faker->date('Y-m-d', '-25 years'),
                    'tel' => $this->faker->phoneNumber(),
                    'correo' => $this->faker->unique()->safeEmail(),
                    'dir' => $this->faker->address(),
                    'sexo' => $this->faker->randomElement(['M', 'F'])
                ]);
                
                $stmtEmpleado->execute([
                    'cedula' => $cedula,
                    'cargo' => $this->faker->randomElement($cargos),
                    'fecha_ingreso' => $fechaIngreso
                ]);
            } catch (\Exception $e) {
                // Ignorar duplicados
            }
        }
        echo "       $cantidad empleados creados.\n";
    }

    private function crearProductosReales()
    {
        $count = $this->db->query("SELECT COUNT(*) FROM producto")->fetchColumn();
        if ($count == 0) {
            $sql = "INSERT IGNORE INTO producto (`id_producto`, `id_categoria`, `nombre_producto`, `descripcion`, `precio`, `imagen`, `es_personalizable`, `estatus`, `tipo_producto`, `fecha_creacion`) VALUES 
            ('PROD-000120260611', 'CAT202606131540288211', 'Perro Polaco Gratinado', 'Perro con salchicha alimex', '5.00', 'prod_6a2ad51300568.jpg', '0', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-000220260611', 'CAT202606131540288211', 'Perro De Pollo Gratinado', 'Perro con salchicha pollo salsa de queso queso
mozzarella maíz y queso pecorino', '7.00', 'prod_6a2db579e91f9.jpg', '0', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-000320260611', 'CATPROD00220260519200547232', 'Occaecati nihil', 'Atque ut cum harum dolorum.', '76.40', 'default-product.png', '0', '1', 'RETAIL', '2026-06-11 10:46:37'),
            ('PROD-000420260611', 'CAT202606131540568458', 'Arepa Cabimera', 'Arepa Cabimera especial con todo', '44.18', 'prod_6a2daa55f28c9.jpg', '1', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-000520260611', 'CAT202606131541589223', 'Papas Locas', 'Quibusdam et nobis perferendis cupiditate.', '15.00', 'prod_6a2dabb417f3c.jpg', '0', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-000620260611', 'CAT202606131540189625', 'Pepito Especial', 'Pepito tipo barquisimeto gratinado', '12.00', 'prod_6a2dadc2b2204.jpg', '1', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-000720260611', 'CATPROD00220260519200547232', 'Commodi at', 'Laborum provident ut soluta nihil.', '51.13', 'default-product.png', '0', '1', 'RETAIL', '2026-06-11 10:46:37'),
            ('PROD-000820260611', 'CAT202606111102317495', 'Strogonoff De Pollo', 'Pollo en salsa blanca', '9.00', 'prod_6a2dae88b238b.jpg', '1', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-000920260611', 'CAT202606131542228868', 'Combo De Tacos', '3 tacos de carne y pollo', '10.00', 'prod_6a2db1a4a67af.webp', '0', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD-001020260611', 'CAT202606131540568458', 'Patacón', 'tostón de plátano verde acompa
ñado de 130 Gr de carne o pollo mechado 
lechuga tomate jamón queso amarillo y
salsas tradicionales', '9.00', 'prod_6a2db6ce22e31.jpg', '1', '1', 'COCINA', '2026-06-11 10:46:37'),
            ('PROD202606111134584244', 'CAT202606131542228868', 'Nachos Con Queso', 'nachos con queso', '11.00', 'prod_6a2ad5a25275a.jpg', '1', '1', 'COCINA', '2026-06-11 11:34:58')";
            $this->db->exec($sql);
            echo "       Productos reales creados.\n";
        }
    }

    private function crearPreparaciones()
    {
        $count = $this->db->query("SELECT COUNT(*) FROM preparacion")->fetchColumn();
        if ($count == 0) {
            $sql = "INSERT IGNORE INTO preparacion (`id_preparacion`, `id_producto`, `id_insumo`, `prioridad_insumo`, `cantidad`, `id_unidad_medida`, `precio_insumo`) VALUES 
            ('PREP20260613154716442', 'PROD202606111134584244', 'INGR001220260611', '1', '1.00000000', 'MEDIAPA23220260519200547232', '0.00'),
            ('PREP20260613154716736', 'PROD202606111134584244', 'INGR000120260611', '1', '20.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154716780', 'PROD202606111134584244', 'INGR000420260611', '1', '500.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729329', 'PROD-000420260611', 'INGR001020260611', '1', '15.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729356', 'PROD-000420260611', 'INGR000120260611', '1', '2.00000000', 'MEDIAPA23220260519200547232', '0.00'),
            ('PREP20260613154729440', 'PROD-000420260611', 'INGR000620260611', '1', '2.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154729490', 'PROD-000420260611', 'INGR001420260611', '2', '1.00000000', 'MEDIAGA23220260519200547232', '3.00'),
            ('PREP20260613154729521', 'PROD-000420260611', 'INGR000220260611', '1', '0.50000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154729560', 'PROD-000420260611', 'INGR001320260611', '1', '0.50000000', 'MEDIALL23220260519200547232', '0.00'),
            ('PREP20260613154729585', 'PROD-000420260611', 'INGR001620260611', '1', '100.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154748115', 'PROD-000620260611', 'INGR000220260611', '1', '200.00000000', 'MEDIACA23220260519200547232', '0.00'),
            ('PREP20260613154748511', 'PROD-000620260611', 'INGR001720260611', '1', '50.00000000', 'MEDIAML23220260519200547232', '0.00'),
            ('PREP20260613154748803', 'PROD-000620260611', 'INGR001420260611', '1', '200.00000000', 'MEDIAML23220260519200547232', '0.00'),
            ('PREP20260613154748828', 'PROD-000620260611', 'INGR000120260611', '1', '200.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154748947', 'PROD-000620260611', 'INGR001620260611', '1', '100.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154755322', 'PROD-000920260611', 'INGR000120260611', '1', '150.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154755836', 'PROD-000920260611', 'INGR000620260611', '1', '1.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154756746', 'PROD-000920260611', 'INGR001620260611', '1', '60.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154756937', 'PROD-000920260611', 'INGR000220260611', '1', '150.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154932164', 'PROD-000520260611', 'INGR001420260611', '1', '1.00000000', 'MEDIALL23220260519200547232', '0.00'),
            ('PREP20260613154932214', 'PROD-000520260611', 'INGR001120260611', '1', '1.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154932502', 'PROD-000520260611', 'INGR000620260611', '1', '2.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154932542', 'PROD-000520260611', 'INGR001720260611', '2', '100.00000000', 'MEDIAML23220260519200547232', '2.00'),
            ('PREP20260613154932667', 'PROD-000520260611', 'INGR000220260611', '1', '2.00000000', 'MEDIAPA23220260519200547232', '0.00'),
            ('PREP20260613154932851', 'PROD-000520260611', 'INGR001620260611', '1', '300.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932899', 'PROD-000520260611', 'INGR000120260611', '1', '500.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154942352', 'PROD-000820260611', 'INGR000220260611', '1', '300.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613154942499', 'PROD-000820260611', 'INGR001420260611', '1', '100.00000000', 'MEDIAML23220260519200547232', '0.00'),
            ('PREP20260613154942932', 'PROD-000820260611', 'INGR000620260611', '1', '1.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613155023579', 'PROD-000120260611', 'INGR001320260611', '1', '2.00000000', 'MEDIALL23220260519200547232', '0.00'),
            ('PREP20260613155023582', 'PROD-000120260611', 'INGR000820260611', '1', '3.00000000', 'MEDIAML23220260519200547232', '0.00'),
            ('PREP20260613155433100', 'PROD-000220260611', 'INGR001620260611', '1', '50.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613155433457', 'PROD-000220260611', 'INGR001420260611', '1', '50.00000000', 'MEDIALL23220260519200547232', '0.00'),
            ('PREP20260613155433994', 'PROD-000220260611', 'INGR000220260611', '1', '100.00000000', 'MEDIAUN23220260519200547232', '0.00'),
            ('PREP20260613160014297', 'PROD-001020260611', 'INGR000220260611', '1', '1.00000000', 'MEDIACA23220260519200547232', '0.00'),
            ('PREP20260613160014372', 'PROD-001020260611', 'INGR001120260611', '1', '1.00000000', 'MEDIAPA23220260519200547232', '0.00'),
            ('PREP20260613160014381', 'PROD-001020260611', 'INGR001320260611', '1', '1.00000000', 'MEDIAML23220260519200547232', '0.00'),
            ('PREP20260613160014512', 'PROD-001020260611', 'INGR000820260611', '1', '1.00000000', 'MEDIALL23220260519200547232', '0.00'),
            ('PREP20260613160014590', 'PROD-001020260611', 'INGR000120260611', '1', '1.00000000', 'MEDIAUN23220260519200547232', '0.00')";
            $this->db->exec($sql);
            echo "       Preparaciones reales creadas.\n";
        }
    }

    private function crearInsumosFalsos($cantidad)
    {
        $ingredientes = [
            'Carne de res', 'Pollo', 'Cerdo', 'Pescado', 'Camarones',
            'Tomate', 'Cebolla', 'Ajo', 'Pimentón', 'Zanahoria',
            'Lechuga', 'Pepino', 'Aguacate', 'Papa', 'Yuca',
            'Queso mozzarella', 'Queso parmesano', 'Leche', 'Mantequilla', 'Huevos',
            'Harina de trigo', 'Arroz', 'Pasta', 'Pan', 'Aceite de oliva',
            'Sal', 'Pimienta', 'Orégano', 'Salsa de tomate', 'Mayonesa'
        ];

        $cantidad = min($cantidad, count($ingredientes));

        $categorias = $this->db->query("SELECT id_categoria FROM categoria_insumo")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($categorias)) {
            $categorias = ['CATING001'];
        }

        $unidades = $this->db->query("SELECT id_unidad FROM unidad_medida WHERE tipo IN ('PESO', 'VOLUMEN', 'UNIDAD')")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($unidades)) {
            $unidades = ['KG', 'G', 'L', 'ML', 'UN'];
        }

        $sql = "INSERT INTO insumo 
            (id_insumo, id_categoria, nombre_insumo, id_unidad_medida, precio_unitario, stock_actual, stock_minimo, estatus) 
            VALUES 
            (:id, :cat, :nombre, :unidad, :precio, :stock, :stock_min, 1)";

        $stmt = $this->db->prepare($sql);

        for ($i = 0; $i < $cantidad; $i++) {
            $stmt->execute([
                'id' => 'INGR' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . date('Ymd'),
                'cat' => $this->faker->randomElement($categorias),
                'nombre' => $ingredientes[$i],
                'unidad' => $this->faker->randomElement($unidades),
                'precio' => $this->faker->randomFloat(2, 1, 30),
                'stock' => $this->faker->randomFloat(3, 1, 50),
                'stock_min' => $this->faker->randomFloat(3, 0.5, 5)
            ]);
        }
        echo "       $cantidad ingredientes generados.\n";
    }

    private function crearClientesFalsos($cantidad)
    {
        $sqlPersona = "INSERT INTO persona 
                (cedula, nombre, apellido, fecha_nacimiento, telefono, correo, direccion, sexo) 
                VALUES 
                (:cedula, :nombre, :apellido, :fecha_nac, :tel, :correo, :dir, :sexo)";

        $sqlCliente = "INSERT INTO cliente (cedula, fecha_registro) VALUES (:cedula, :fecha_reg)";

        $stmtPersona = $this->db->prepare($sqlPersona);
        $stmtCliente = $this->db->prepare($sqlCliente);

        for ($i = 0; $i < $cantidad; $i++) {
            $cedula = 'V-' . $this->faker->unique()->numberBetween(10000000, 99999999);
            
            try {
                $stmtPersona->execute([
                    'cedula' => $cedula,
                    'nombre' => $this->faker->firstName(),
                    'apellido' => $this->faker->lastName(),
                    'fecha_nac' => $this->faker->date('Y-m-d', '-30 years'),
                    'tel' => $this->faker->phoneNumber(),
                    'correo' => $this->faker->unique()->safeEmail(),
                    'dir' => $this->faker->address(),
                    'sexo' => $this->faker->randomElement(['M', 'F'])
                ]);
                
                $stmtCliente->execute([
                    'cedula' => $cedula,
                    'fecha_reg' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Ignorar duplicados
            }
        }
        echo "       $cantidad clientes generados.\n";
    }

    private function crearProveedoresFalsos($cantidad)
    {
        $count = $this->db->query("SELECT COUNT(*) FROM proveedor")->fetchColumn();
        if ($count > 0) {
            return;
        }

        $sql = "INSERT INTO proveedor (documento_legal, nombre, telefono, correo, direccion) 
                VALUES (:doc, :nombre, :tel, :correo, :dir)";
        $stmt = $this->db->prepare($sql);

        $empresas = ['Distribuidora', 'Importadora', 'Comercial', 'Mayorista', 'Alimentos'];
        $nombres = ['El Buen Sabor', 'La Granja', 'Del Campo', 'Premium', 'Selecta'];

        for ($i = 0; $i < $cantidad; $i++) {
            $stmt->execute([
                'doc' => 'J-' . $this->faker->unique()->numberBetween(10000000, 99999999),
                'nombre' => $this->faker->randomElement($empresas) . ' ' . $this->faker->randomElement($nombres),
                'tel' => $this->faker->phoneNumber(),
                'correo' => $this->faker->unique()->companyEmail(),
                'dir' => $this->faker->address()
            ]);
        }
        echo "       $cantidad proveedores generados.\n";
    }
}