<?php

namespace App\Database\Seeders;

use Faker\Factory;
use App\Helpers\Helper;

class BusinessSeeder
{
    private $db;
    private $faker;

    public function __construct($db)
    {
        $this->db = $db;
        $this->faker = Factory::create('es_VE');
    }

    public function run()
    {
        $this->crearMesas();
        $this->crearProductosFalsos(10);
        $this->crearPersonalsFalsos(5);
        $this->crearIngredientesFalsos(20);
        $this->crearClientesFalsos(15);
    }

    private function crearMesas()
    {
        $this->db->exec("DELETE FROM mesa");

        $sql = "INSERT INTO mesa (id_mesa, numero_mesa, capacidad, estado, estatus) VALUES 
                ('MESA00120251001', 1, 4, 'DISPONIBLE', 1),
                ('MESA00220251001', 2, 2, 'DISPONIBLE', 1),
                ('MESA00320251001', 3, 6, 'DISPONIBLE', 1)";
        try {
            $this->db->exec($sql);
            echo "       Mesas creadas correctamente.\n";
        } catch (\Exception $e) {
            echo "       Aviso en mesas: " . $e->getMessage() . "\n";
        }
    }

    private function crearProductosFalsos($cantidad)
    {
        $stmt = $this->db->query("SELECT id_categoria FROM categoria_producto LIMIT 1");
        $categoria = $stmt->fetch();

        if (!$categoria) {
            echo "       No hay categorías. Creando una por defecto...\n";
            $this->db->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, descripcion, icono, estatus) VALUES 
                            ('CATEGEN20251001', 'General', 'Categoría por defecto', 'default.png', 1)");
            $id_categoria = 'CATEGEN20251001';
        } else {
            $id_categoria = $categoria['id_categoria'];
        }

        $sql = "INSERT INTO producto 
                (id_producto, id_categoria, nombre_producto, descripcion, precio, stock, stock_minimo, costo_preparacion, tiempo_preparacion, imagen, es_personalizable, tipo_producto, estatus) 
                VALUES 
                (:id, :id_categoria, :nombre, :desc, :precio, :stock, :stock_minimo, :costo, :tiempo, :imagen, :personalizable, 'COCINA', 1)";

        $stmt = $this->db->prepare($sql);

        for ($i = 0; $i < $cantidad; $i++) {
            $precio = $this->faker->randomFloat(2, 5, 100);
            $stmt->execute([
                'id'             => 'PROD-' . $this->faker->unique()->numberBetween(1000, 9999) . time(),
                'id_categoria'   => $id_categoria,
                'nombre'         => ucfirst($this->faker->words(2, true)),
                'desc'           => $this->faker->sentence(6),
                'precio'         => $precio,
                'costo'          => $this->faker->randomFloat(2, 1, $precio * 0.7),
                'stock'          => $this->faker->numberBetween(10, 100),
                'stock_minimo'   => $this->faker->numberBetween(1, 10),
                'tiempo'         => $this->faker->numberBetween(5, 30),
                'imagen'         => null,
                'personalizable' => $this->faker->boolean(30)
            ]);
        }
        echo "       $cantidad Productos generados correctamente con Faker.\n";
    }

    private function crearPersonalsFalsos($cantidad)
    {
        $stmt = $this->db->query("SELECT id_cargo FROM cargo LIMIT 1");
        $cargo = $stmt->fetch();

        if (!$cargo) {
            echo "       No hay cargos. Creando uno por defecto...\n";
            $this->db->exec("INSERT INTO cargo (id_cargo, nombre_cargo, descripcion, estatus) VALUES 
                            ('CARGO20251001', 'Personal', 'Cargo por defecto', 1)");
            $id_cargo = 'CARGO20251001';
        } else {
            $id_cargo = $cargo['id_cargo'];
        }

        $sql = "INSERT INTO personal 
                (cedula_personal, nombres, apellidos, id_cargo, telefono, correo, fecha_ingreso, salario, estatus) 
                VALUES 
                (:cedula, :nombres, :apellidos, :id_cargo, :telefono, :correo, :fecha_ingreso, :salario, 1)";

        $stmt = $this->db->prepare($sql);

        for ($i = 0; $i < $cantidad; $i++) {
            $fechaIngreso = $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
            $salario = $this->faker->randomFloat(2, 30000, 100000);
            $stmt->execute([
                'cedula'      => $this->faker->unique()->numberBetween(10000000, 99999999),
                'nombres'     => $this->faker->firstName(),
                'apellidos'   => $this->faker->lastName(),
                'id_cargo'    => $id_cargo,
                'telefono'    => $this->faker->phoneNumber(),
                'correo'      => $this->faker->unique()->email(),
                'fecha_ingreso' => $fechaIngreso,
                'salario'     => $salario
            ]);
        }
        echo "       Empleados generados correctamente con Faker.\n";
    }

    private function crearIngredientesFalsos($cantidad)
    {
        $ingredientes = [
            'carne de res', 'carne molida', 'pollo', 'pechuga de pollo', 'cerdo',
            'tocino', 'jamón', 'salchicha', 'chorizo', 'costillas', 'pescado', 'atún',
            'camarones', 'pulpo', 'calamares', 'langosta', 'lechuga', 'tomate',
            'cebolla', 'cebolla morada', 'ajo', 'pimentón', 'ají dulce', 'zanahoria',
            'pepino', 'aguacate', 'espinaca', 'repollo', 'brocoli', 'coliflor',
            'calabacín', 'berenjena', 'champiñones', 'maíz', 'arvejas', 'remolacha',
            'apio', 'perejil', 'cilantro', 'orégano', 'queso mozzarella',
            'queso cheddar', 'queso amarillo', 'queso blanco', 'queso parmesano',
            'queso de cabra', 'queso crema', 'requesón', 'leche', 'crema de leche',
            'mantequilla', 'yogur', 'huevos', 'harina de trigo', 'harina de maíz',
            'arroz', 'pasta', 'pan', 'pan rallado', 'avena', 'quinua', 'lentejas',
            'caraotas', 'garbanzos', 'plátano', 'cambur', 'manzana', 'naranja',
            'limón', 'fresa', 'piña', 'mango', 'papaya', 'melón', 'sandía', 'durazno',
            'coco', 'sal', 'pimienta', 'comino', 'paprika', 'curry', 'nuez moscada',
            'canela', 'clavo de olor', 'laurel', 'tomillo', 'romero', 'albahaca',
            'salsa de tomate', 'mayonesa', 'mostaza', 'salsa inglesa', 'vinagre',
            'aceite de oliva', 'aceite vegetal', 'miel', 'azúcar', 'salsa de soya',
            'papas', 'yuca', 'plátano verde', 'tostones', 'patacones', 'arepa',
            'pan de hamburguesa', 'pan de perro caliente', 'tortilla', 'masa de pizza'
        ];

        $cantidad = min($cantidad, count($ingredientes));

        $categoriaStmt = $this->db->query("SELECT id_categoria FROM categoria_ingrediente LIMIT 1");
        $categoria = $categoriaStmt->fetch();
        if (!$categoria) {
            echo "       No hay categorías de ingredientes. Creando una por defecto...\n";
            $this->db->exec("INSERT INTO categoria_ingrediente (id_categoria, nombre, descripcion) VALUES ('CATINGR20251001', 'General', 'Categoría de ingredientes por defecto')");
            $categoriaId = 'CATINGR20251001';
        } else {
            $categoriaId = $categoria['id_categoria'];
        }

        // Obtener unidades de medida disponibles
        $unidadesStmt = $this->db->query("SELECT id_unidad FROM unidad_medida WHERE tipo IN ('PESO', 'VOLUMEN', 'UNIDAD')");
        $unidades = $unidadesStmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (empty($unidades)) {
            $unidades = ['KG', 'G', 'L', 'ML', 'UN'];
        }

        $sql = "INSERT INTO ingrediente 
            (id_ingrediente, id_categoria, nombre_ingrediente, id_unidad_medida, precio_unitario, estatus) 
            VALUES 
            (:id, :id_categoria, :nombre, :id_unidad, :precio, 1)";

        $stmt = $this->db->prepare($sql);

        $this->faker->unique(true);

        for ($i = 0; $i < $cantidad; $i++) {
            $stmt->execute([
                'id'          => 'INGR-' . $this->faker->unique()->numberBetween(1000, 9999) . time(),
                'id_categoria' => $categoriaId,
                'nombre'      => ucfirst($this->faker->unique()->randomElement($ingredientes)),
                'id_unidad'   => $this->faker->randomElement($unidades),
                'precio'      => $this->faker->randomFloat(2, 1, 20)
            ]);
        }
        echo "       $cantidad Ingredientes únicos generados correctamente con Faker.\n";
    }

    private function crearClientesFalsos($cantidad)
    {
        $sqlPersona = "INSERT INTO persona 
                (cedula, nombre, apellido, fecha_nacimiento, telefono, correo, direccion, sexo) 
                VALUES 
                (:cedula, :nombre, :apellido, :fecha_nacimiento, :telefono, :correo, :direccion, :sexo)";

        $sqlCliente = "INSERT INTO cliente 
                (cedula, fecha_registro, ultima_visita) 
                VALUES 
                (:cedula, :fecha_registro, NULL)";

        $stmtPersona = $this->db->prepare($sqlPersona);
        $stmtCliente = $this->db->prepare($sqlCliente);

        for ($i = 0; $i < $cantidad; $i++) {
            $cedula = (string) $this->faker->unique()->numberBetween(10000000, 99999999);
            $fechaRegistro = $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');
            $stmtPersona->execute([
                'cedula' => $cedula,
                'nombre' => $this->faker->firstName(),
                'apellido' => $this->faker->lastName(),
                'fecha_nacimiento' => $this->faker->date('Y-m-d'),
                'telefono' => $this->faker->phoneNumber(),
                'correo' => $this->faker->unique()->safeEmail(),
                'direccion' => $this->faker->address(),
                'sexo' => $this->faker->randomElement(['M', 'F'])
            ]);
            $stmtCliente->execute([
                'cedula' => $cedula,
                'fecha_registro' => $fechaRegistro
            ]);
        }
        echo "       Clientes generados correctamente con Faker.\n";
    }
}