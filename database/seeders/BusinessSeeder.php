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
        $this->crearProductosFalsos(10);
        $this->crearIngredientesFalsos(20);
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
            $sql = "INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus) VALUES 
                ('CATPROD00120260519200547232', 'Platos Principales', 1),
                ('CATPROD00220260519200547232', 'Entradas', 1),
                ('CATPROD00320260519200547232', 'Bebidas', 1),
                ('CATPROD00420260519200547232', 'Postres', 1),
                ('CATPROD00520260519200547232', 'Ensaladas', 1)";
            $this->db->exec($sql);
            echo "       Categorías de productos creadas.\n";
        }

        // Categorías de ingredientes
        $countIng = $this->db->query("SELECT COUNT(*) FROM categoria_ingrediente")->fetchColumn();
        if ($countIng == 0) {
            $sql = "INSERT INTO categoria_ingrediente (id_categoria, nombre) VALUES 
                ('CATEGIN00120260519200547232', 'Carnes'),
                ('CATEGIN00220260519200547232', 'Verduras'),
                ('CATEGIN00320260519200547232', 'Lácteos'),
                ('CATEGIN00420260519200547232', 'Granos'),
                ('CATEGIN00520260519200547232', 'Condimentos'),
                ('CATEGIN00620260519200547232', 'Bebidas'),
                ('CATEGIN00720260519200547232', 'Frutas')";
            $this->db->exec($sql);
            echo "       Categorías de ingredientes creadas.\n";
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

    private function crearProductosFalsos($cantidad)
    {
        $categorias = $this->db->query("SELECT id_categoria FROM categoria_producto")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($categorias)) {
            $categorias = ['CATPROD00120260519200547232'];
        }

        $sql = "INSERT INTO producto 
                (id_producto, id_categoria, nombre_producto, descripcion, precio, es_personalizable, tipo_producto, estatus) 
                VALUES 
                (:id, :id_categoria, :nombre, :descripcion, :precio, :personalizable, :tipo, 1)";

        $stmt = $this->db->prepare($sql);
        $tipos = ['COCINA', 'BARRA', 'POSTRE', 'RETAIL'];

        for ($i = 0; $i < $cantidad; $i++) {
            $precio = $this->faker->randomFloat(2, 5, 100);
            $stmt->execute([
                'id' => 'PROD-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . date('Ymd'),
                'id_categoria' => $this->faker->randomElement($categorias),
                'nombre' => ucfirst($this->faker->words(2, true)),
                'descripcion' => $this->faker->sentence(6),
                'precio' => $precio,
                'personalizable' => $this->faker->boolean(30) ? 1 : 0,
                'tipo' => $this->faker->randomElement($tipos)
            ]);
        }
        echo "       $cantidad productos generados.\n";
    }

    private function crearIngredientesFalsos($cantidad)
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

        $categorias = $this->db->query("SELECT id_categoria FROM categoria_ingrediente")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($categorias)) {
            $categorias = ['CATING001'];
        }

        $unidades = $this->db->query("SELECT id_unidad FROM unidad_medida WHERE tipo IN ('PESO', 'VOLUMEN', 'UNIDAD')")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($unidades)) {
            $unidades = ['KG', 'G', 'L', 'ML', 'UN'];
        }

        $sql = "INSERT INTO ingrediente 
            (id_ingrediente, id_categoria, nombre_ingrediente, id_unidad_medida, precio_unitario, stock_actual, stock_minimo, estatus) 
            VALUES 
            (:id, :cat, :nombre, :unidad, :precio, :stock, :stock_min, 1)";

        $stmt = $this->db->prepare($sql);

        for ($i = 0; $i < $cantidad; $i++) {
            $stmt->execute([
                'id' => 'INGR-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . date('Ymd'),
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