<?php
namespace App\Database\Seeders;

use Faker\Factory;

class BusinessSeeder {
    private $db;
    private $faker;

    public function __construct($db) {
        $this->db = $db;
        $this->faker = Factory::create('es_VE');
    }

    public function run() {
        $this->crearMesas();
        $this->crearProductosFalsos(10);
    }

    private function crearMesas() {
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

    private function crearProductosFalsos($cantidad) {
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
                (id_producto, id_categoria, nombre_producto, descripcion, precio, stock, stock_minimo, costo_preparacion, tiempo_preparacion, imagen, es_personalizable, estatus) 
                VALUES 
                (:id, :id_categoria, :nombre, :desc, :precio, :stock, :stock_minimo, :costo, :tiempo, :imagen, :personalizable, 1)";
        
        $stmt = $this->db->prepare($sql);

        for ($i = 0; $i < $cantidad; $i++) {
            $precio = $this->faker->randomFloat(2, 5, 100);
            $stmt->execute([
                'id'             => 'PROD-' . $this->faker->unique()->numberBetween(1000, 9999) . time(), // ID único simple
                'id_categoria'   => $id_categoria,
                'nombre'         => ucfirst($this->faker->words(2, true)),
                'desc'           => $this->faker->sentence(6),
                'precio'         => $precio,
                'costo'          => $this->faker->randomFloat(2, 1, $precio * 0.7), // Costo aleatorio
                'stock'          => $this->faker->numberBetween(10, 100), // Stock aleatorio
                'stock_minimo'   => $this->faker->numberBetween(1, 10), // Stock mínimo aleatorio
                'tiempo'         => $this->faker->numberBetween(5, 30), // Minutos
                'imagen'         => $this->faker->imageUrl(200, 200, 'food', true), // Imagen fake
                'personalizable' => $this->faker->boolean(30) // 30% de probabilidad de ser personalizable
            ]);
        }
        echo "       $cantidad Productos generados correctamente con Faker.\n";
    }
}