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
        $this->crearPersonasYEmpleados();
        $this->crearProductosReales();
        $this->crearInsumosFalsos(20);
        $this->crearPreparaciones();
        $this->crearClientesActuales();
        $this->crearProveedoresFalsos(5);
        $this->crearAsociacionProveedor(5);
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
                ('CATEGIN00720260519200547232', 'Frutas'),
                ('CATEGIN00820260519200547232', 'Mariscos'),
                ('CATEGIN00920260519200547232', 'Huevos'),
                ('CATEGIN01020260519200547232', 'Consumible'),
                ('CATEGIN01120260519200547232', 'Alcohólico')";
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

    private function crearPersonasYEmpleados()
    {
        $sqlPersonas = <<<'SQL'
INSERT IGNORE INTO `persona` (`cedula`, `documento`, `nombre`, `apellido`, `fecha_nacimiento`, `telefono`, `correo`, `direccion`, `sexo`) VALUES 
            ('V-00000000', NULL, 'Admin', 'Principal', NULL, '04120000000', 'admin@goodvibes.com', NULL, 'M'),
            ('V-1111111', NULL, 'Abrahan', 'Rodriguez', '2004-06-10', '0414-2233122', 'Abrahan@gmail.com', 'Barrio Union', 'M'),
            ('V-11603703', NULL, 'Martin', 'Burgos', '1981-11-23', '+58 460 436 6488', 'mateo.banda@example.org', 'Calle Bustamante, 4, Hab. 54, Villa Miriamde Mara Edo. Bolívar, 2632', 'M'),
            ('V-123123121', NULL, 'Josue', 'Garrido', '2001-10-07', '0416-1123312', 'negro@gmail.com', 'La Quiboreña', 'F'),
            ('V-12345677', NULL, 'Leizer', 'Torrealba', '2002-04-01', '0416-2232332', 'Leizer@gmail.com', 'Barquisimeto', 'M'),
            ('V-12345678', NULL, 'Jorge', 'Cabrera', '2004-02-08', '0422-2234545', 'jorge@gmail.com', 'Cabudare', 'M'),
            ('V-16922393', NULL, 'Emma', 'Balderas', '1987-01-22', '235-5409484', 'aleix69@example.org', 'Carretera Quesada, Hab. 6, El Leyrede Mara Edo. Sucre, 8168', 'M'),
            ('V-21017978', NULL, 'Raul', 'Contreras', '1980-10-08', '457-9944326', 'ozuna.nahia@example.org', 'Callejón Valdivia, 136, Apto 3, Valle Adrianade Mara Edo. Portuguesa, 2642', 'F'),
            ('V-22284399', NULL, 'Mario', 'Ulloa', '1970-06-12', '283-3001817', 'elsa.irizarry@example.net', 'Avenida Sebastian, 26, Casa 1, Daniel del Tuy Edo. Falcón', 'M'),
            ('V-25261082', NULL, 'Mireia', 'Córdova', '1992-06-25', '0426-6565656', 'xcortes@example.org', 'Avenida Hugo, Nro 1, Teresa De Mara Edo. Barinas', 'F'),
            ('V-27095670', NULL, 'Alejandra', 'Grijalva', '1986-04-10', '+58 403-6099650', 'iduarte@example.net', 'Carretera Pablo, Casa 2, Juan del Tuy Edo. Zulia', 'F'),
            ('V-27944092', NULL, 'Erika', 'Cruz', '1973-07-14', '252 4141086', 'marcos.ines@example.net', 'Carretera Angela, 8, Piso 78, La Domingodel Tuy Edo. Nueva Esparta', 'F'),
            ('V-28165452', NULL, 'Santiago', 'Coello', '2001-11-08', '0414-5290172', 'santiagojcoello@gmail.com', 'La Libertad, Quibor', 'M'),
            ('V-32858359', NULL, 'Alejandra', 'Rentería', '1977-01-29', '+58 234 197 8848', 'luna04@example.net', 'Carretera Francisco, 2, Piso 90, El Dariode Mata Edo. Sucre, 2801', 'F'),
            ('V-33920706', NULL, 'Aleix', 'Navarro', '1990-09-29', '468-6771071', 'elsa11@example.com', 'Callejón Menéndez, 45, Nro 95, Briseño de Asis Edo. Falcón', 'M'),
            ('V-34233516', NULL, 'Carla', 'Melgar', '1970-12-28', '+58 470-538-3337', 'ramirez.enrique@example.com', 'Calle Rodrigo, Casa 5, Valle Hector Edo. Delta Amacuro', 'F'),
            ('V-52088190', NULL, 'Gonzalo', 'Sauceda', '1990-11-07', '+58 2075059482', 'gallego.diego@example.org', 'Carretera Asensio, 37, Nro 6, Carmen de Altagracia Edo. Trujillo, 0958', 'F'),
            ('V-56844801', NULL, 'Mohamed', 'Piñeiro', '1994-04-06', '+58 221-2938430', 'luisa77@example.org', 'Av. Anna, Piso 4, Valle Alicia Edo. Distrito Capital, 4539', 'F'),
            ('V-60061765', NULL, 'Eric', 'Matos', '1971-10-04', '+58 207 8701206', 'quiroz.guillem@example.net', 'Calle Laura Rosas, Piso 28, Los Hector Edo. Vargas, 3883', 'M'),
            ('V-64132633', NULL, 'Ariadna', 'Garza', '1980-05-11', '400-745-2319', 'montez.isaac@example.org', 'Calle Ivan, 537, Casa 95, Delagarza del Valle Edo. Amazonas', 'F'),
            ('V-68183467', NULL, 'Hugo', 'Matos', '1972-06-19', '+58 256 967 1399', 'jana77@example.com', 'Avenida Bruno Quiroz, Apto 49, Jon de Altagracia Edo. Anzoátegui', 'M'),
            ('V-70444533', NULL, 'Dario', 'Chacón', '1982-08-12', '+58 486-749-4826', 'gabriel.llamas@example.com', 'Callejón Ledesma, 69, Nro 2, Parroquia Manuela Edo. Distrito Capital, 3995', 'M'),
            ('V-91174347', NULL, 'Elena', 'Tijerina', '1993-01-02', '+58 4455517957', 'alberto.millan@example.net', 'Avenida Hernando, Casa 0, Los Joan Edo. Aragua', 'M'),
            ('V-92108587', NULL, 'Blanca', 'Vásquez', '1981-10-02', '447 455 1762', 'ybeltran@example.com', 'Av. Ona Ibáñez, 7, Hab. 6, Las Marcos Edo. Yaracuy, 8123', 'F'),
            ('V-92187658', NULL, 'Santiago', 'Cortes', '1982-04-28', '217-685-9389', 'giron.gonzalo@example.net', 'Cl. Beatriz Cervántez, 8, Nro 93, Los Aliciadel Tuy Edo. Barinas', 'M'),
            ('V-96679533', NULL, 'Samuel', 'Roca', '1982-01-08', '426 951 8855', 'bsarabia@example.com', 'Av. Pedraza, Nro 05, Mateo de Mata Edo. Barinas, 8285', 'M')
SQL;
        $this->db->exec($sqlPersonas);

        $sqlEmpleados = <<<'SQL'
INSERT IGNORE INTO `empleado` (`cedula`, `id_cargo`, `fecha_ingreso`, `estatus`) VALUES 
            ('V-123123121', 'CARGO00420260519200547232', '2026-07-09', '1'),
            ('V-12345677', 'CARGO00320260519200547232', '2026-07-09', '1'),
            ('V-12345678', 'CARGO00620260519200547232', '2026-07-09', '1'),
            ('V-25261082', 'CARGO00220260519200547232', '2024-08-18', '1'),
            ('V-56844801', 'CARGO00220260519200547232', '2025-10-21', '1'),
            ('V-60061765', 'CARGO00120260519200547232', '2025-04-08', '1'),
            ('V-70444533', 'CARGO00520260519200547232', '2024-09-11', '1'),
            ('V-91174347', 'CARGO00420260519200547232', '2026-02-25', '1')
SQL;
        $this->db->exec($sqlEmpleados);
        echo "       Personas y Empleados actuales creados.\n";
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
            ('PREP20260613154716442', 'PROD202606111134584244', 'INGR001220260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154716736', 'PROD202606111134584244', 'INGR000120260611', '1', '20.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154716780', 'PROD202606111134584244', 'INGR000420260611', '1', '500.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729329', 'PROD-000420260611', 'INGR001020260611', '1', '15.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729356', 'PROD-000420260611', 'INGR000120260611', '1', '2.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729440', 'PROD-000420260611', 'INGR000620260611', '1', '2.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729490', 'PROD-000420260611', 'INGR001420260611', '2', '1.00000000', 'MEDIAGR23220260519200547232', '3.00'),
            ('PREP20260613154729521', 'PROD-000420260611', 'INGR000220260611', '1', '0.50000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729560', 'PROD-000420260611', 'INGR001320260611', '1', '0.50000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154729585', 'PROD-000420260611', 'INGR001620260611', '1', '100.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154748115', 'PROD-000620260611', 'INGR000220260611', '1', '200.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154748511', 'PROD-000620260611', 'INGR001720260611', '1', '50.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154748803', 'PROD-000620260611', 'INGR001420260611', '1', '200.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154748828', 'PROD-000620260611', 'INGR000120260611', '1', '200.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154748947', 'PROD-000620260611', 'INGR001620260611', '1', '100.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154755322', 'PROD-000920260611', 'INGR000120260611', '1', '150.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154755836', 'PROD-000920260611', 'INGR000620260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154756746', 'PROD-000920260611', 'INGR001620260611', '1', '60.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154756937', 'PROD-000920260611', 'INGR000220260611', '1', '150.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932164', 'PROD-000520260611', 'INGR001420260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932214', 'PROD-000520260611', 'INGR001120260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932502', 'PROD-000520260611', 'INGR000620260611', '1', '2.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932542', 'PROD-000520260611', 'INGR001720260611', '2', '100.00000000', 'MEDIAGR23220260519200547232', '2.00'),
            ('PREP20260613154932667', 'PROD-000520260611', 'INGR000220260611', '1', '2.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932851', 'PROD-000520260611', 'INGR001620260611', '1', '300.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154932899', 'PROD-000520260611', 'INGR000120260611', '1', '500.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154942352', 'PROD-000820260611', 'INGR000220260611', '1', '300.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154942499', 'PROD-000820260611', 'INGR001420260611', '1', '100.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613154942932', 'PROD-000820260611', 'INGR000620260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613155023579', 'PROD-000120260611', 'INGR001320260611', '1', '2.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613155023582', 'PROD-000120260611', 'INGR000820260611', '1', '3.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613155433100', 'PROD-000220260611', 'INGR001620260611', '1', '50.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613155433457', 'PROD-000220260611', 'INGR001420260611', '1', '50.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613155433994', 'PROD-000220260611', 'INGR000220260611', '1', '100.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613160014297', 'PROD-001020260611', 'INGR000220260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613160014372', 'PROD-001020260611', 'INGR001120260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613160014381', 'PROD-001020260611', 'INGR001320260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613160014512', 'PROD-001020260611', 'INGR000820260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00'),
            ('PREP20260613160014590', 'PROD-001020260611', 'INGR000120260611', '1', '1.00000000', 'MEDIAGR23220260519200547232', '0.00')";
            $this->db->exec($sql);
            echo "       Preparaciones reales creadas.\n";
        }
    }

    private function crearInsumosFalsos($cantidad)
    {

        $sql = "INSERT INTO `insumo` (`id_insumo`, `id_categoria`, `nombre_insumo`, `id_unidad_medida`, `precio_unitario`, `stock_actual`, `stock_minimo`, `stock_maximo`, `estatus`) VALUES
('INGR000120260611', 'CATEGIN00120260519200547232', 'Carne de res', 'MEDIAKG23220260519200547232', 8.99, 15.80000000, 3.00000000, NULL, 1),
('INGR000220260611', 'CATEGIN00120260519200547232', 'Pollo', 'MEDIAKG23220260519200547232', 27.34, 16.75000000, 1.00000000, NULL, 1),
('INGR000320260611', 'CATEGIN00120260519200547232', 'Cerdo', 'MEDIAKG23220260519200547232', 13.94, 39.78900000, 6.00000000, 35.00000000, 1),
('INGR000420260611', 'CATEGIN00120260519200547232', 'Pescado', 'MEDIAKG23220260519200547232', 5.77, 16.19700000, 2.00000000, NULL, 1),
('INGR000520260611', 'CATEGIN00820260519200547232', 'Camarones', 'MEDIAKG23220260519200547232', 25.46, 18.50200000, 3.00000000, NULL, 1),
('INGR000620260611', 'CATEGIN00220260519200547232', 'Tomate', 'MEDIAKG23220260519200547232', 21.15, 36.00000000, 4.00000000, NULL, 1),
('INGR000720260611', 'CATEGIN00220260519200547232', 'Cebolla', 'MEDIAKG23220260519200547232', 28.63, 45.00000000, 2.00000000, 10.00000000, 1),
('INGR000820260611', 'CATEGIN00220260519200547232', 'Ajo', 'MEDIAKG23220260519200547232', 28.74, 36.00000000, 4.00000000, NULL, 1),
('INGR000920260611', 'CATEGIN00220260519200547232', 'Pimentón', 'MEDIAKG23220260519200547232', 27.77, 16.00000000, 3.00000000, NULL, 1),
('INGR001020260611', 'CATEGIN00220260519200547232', 'Zanahoria', 'MEDIAKG23220260519200547232', 9.19, 3.00000000, 4.00000000, NULL, 1),
('INGR001120260611', 'CATEGIN00220260519200547232', 'Lechuga', 'MEDIAKG23220260519200547232', 16.48, 31.00000000, 4.00000000, NULL, 1),
('INGR001220260611', 'CATEGIN00220260519200547232', 'Pepino', 'MEDIAKG23220260519200547232', 28.43, 20.50000000, 4.00000000, NULL, 1),
('INGR001320260611', 'CATEGIN00720260519200547232', 'Aguacate', 'MEDIAKG23220260519200547232', 19.82, 6.00000000, 4.00000000, NULL, 1),
('INGR001420260611', 'CATEGIN00220260519200547232', 'Papa', 'MEDIAKG23220260519200547232', 20.66, 31.76000000, 2.00000000, NULL, 1),
('INGR001520260611', 'CATEGIN00220260519200547232', 'Yuca', 'MEDIAKG23220260519200547232', 15.99, 36.00000000, 2.00000000, NULL, 1),
('INGR001620260611', 'CATEGIN00320260519200547232', 'Queso mozzarella', 'MEDIAKG23220260519200547232', 10.75, 12.00000000, 1.00000000, NULL, 1),
('INGR001720260611', 'CATEGIN00320260519200547232', 'Queso parmesano', 'MEDIAKG23220260519200547232', 10.60, 33.00000000, 1.00000000, NULL, 1),
('INGR001820260611', 'CATEGIN00320260519200547232', 'Leche', 'MEDIALL23220260519200547232', 19.53, 1.36200000, 4.00000000, NULL, 1),
('INGR001920260611', 'CATEGIN00320260519200547232', 'Mantequilla', 'MEDIAKG23220260519200547232', 12.50, 13.00000000, 3.00000000, NULL, 1),
('INGR002020260611', 'CATEGIN00920260519200547232', 'Huevos', 'MEDIAUN23220260519200547232', 11.27, 21.00000000, 3.00000000, NULL, 1),
('INSUM016320260611173920163', 'CATEGIN00820260519200547232', 'Pulpo', 'MEDIAKG23220260519200547232', 10.00, 12.00000000, 6.00000000, 30.00000000, 1),
('INSUM091820260614222320918', 'CATEGIN01020260519200547232', 'Chocolate', 'MEDIAKG23220260519200547232', 2.00, 9.00000000, 1.00000000, 10.00000000, 1),
('INSUM1273202606111800111273', 'CATEGIN00220260519200547232', 'Cebolla Roja', 'MEDIAKG23220260519200547232', 0.50, 11.20000000, 3.00000000, 20.00000000, 1),
('INSUM4414202606221655044414', 'CATEGIN00220260519200547232', 'Calabaza', 'MEDIAKG23220260519200547232', 2.00, 33.00000000, 5.00000000, 30.00000000, 1),
('INSUM8670202606111657288670', 'CATEGIN00720260519200547232', 'Cereza', 'MEDIAKG23220260519200547232', 2.00, 20.00000000, 2.00000000, 30.00000000, 1),
('INSUM8722202606111753388722', 'CATEGIN00420260519200547232', 'Guisantes', 'MEDIAKG23220260519200547232', 1.00, 4.00000000, 1.00000000, 20.00000000, 1),
('INSUM8798202606111736388798', 'CATEGIN00420260519200547232', 'Caraotas Blancas', 'MEDIAKG23220260519200547232', 5.00, 6.00000000, 2.00000000, 15.00000000, 1)";

        $this->db->exec($sql);
        echo "       insumos generados.\n";
    }

    private function crearClientesActuales()
    {
        $sqlClientes = <<<'SQL'
INSERT IGNORE INTO `cliente` (`cedula`, `fecha_registro`, `estatus`) VALUES 
            ('V-1111111', '2026-07-09 09:54:19', '1'),
            ('V-11603703', '2026-05-04 09:20:51', '1'),
            ('V-16922393', '2024-10-07 17:13:55', '1'),
            ('V-21017978', '2024-12-11 21:17:34', '1'),
            ('V-22284399', '2025-05-16 23:01:49', '1'),
            ('V-27095670', '2024-12-04 01:42:21', '1'),
            ('V-27944092', '2025-02-02 22:46:43', '1'),
            ('V-28165452', '2026-07-09 09:17:16', '1'),
            ('V-32858359', '2025-07-07 14:55:50', '1'),
            ('V-33920706', '2026-04-12 18:20:55', '1'),
            ('V-34233516', '2026-03-04 02:16:42', '1'),
            ('V-52088190', '2024-11-22 00:01:13', '1'),
            ('V-64132633', '2026-06-11 01:50:36', '1'),
            ('V-68183467', '2025-12-07 05:39:34', '1'),
            ('V-92108587', '2025-02-06 07:34:49', '1'),
            ('V-92187658', '2024-07-15 16:25:48', '1'),
            ('V-96679533', '2025-08-24 09:59:59', '1')
SQL;
        $this->db->exec($sqlClientes);
        echo "       Clientes actuales creados.\n";
    }

    private function crearProveedoresFalsos($cantidad)
    {
        $count = $this->db->query("SELECT COUNT(*) FROM proveedor")->fetchColumn();
        if ($count > 0) {
            return;
        }

        $sql = "INSERT INTO `proveedor` (`documento_legal`, `nombre`, `telefono`, `correo`, `direccion`, `estatus`) VALUES
        ('J-29851737', 'Importadora Selecta', '0416-1340029', 'murillo.mohamed@zuniga.net', 'Av. Natalia Cobo, 104, Casa 41, Valle Zulayde Asis Edo. Carabobo', 1),
        ('J-33273667', 'Comercial El Buen Sabor', '0426-2003726', 'jesus72@quintanilla.co.ve', 'Vereda Africa, Casa 29, Jon del Valle Edo. Barinas', 1),
        ('J-57449969', 'Alimentos La Granja', '0412-1133672', 'blanca.menchaca@esquivel.com.ve', 'Carretera Lucia Plaza, 66, Casa 52, El Sebastiande Asis Edo. Amazonas, 9925', 1),
        ('J-76989485', 'Alimentos Del Campo', '0414-9287022', 'Victor81@grijalva.com', 'Cl. Mayorga, Apto 2, San Miriam Edo. Vargas, 7759', 1),
        ('J-85831458', 'Comercial El Buen Sabor', '0424-1483022', 'izan36@raya.com', 'Avenida Chapa, 81, Apto 7, San Marco Edo. Cojedes', 1)";
        $this->db->exec($sql);
        echo "       5 proveedores generados.\n";
    }

    private function crearAsociacionProveedor($cantidad)
    {


        $sql = "INSERT INTO `entrada_insumo` (`id_entrada`, `id_insumo`, `documento_proveedor`, `estatus`) VALUES
        ('ENTRA020720260611173920207', 'INSUM016320260611173920163', 'J-76989485', 1),
        ('ENTRA1001202606142223211001', 'INSUM091820260614222320918', 'J-33273667', 1),
        ('ENTRA1322202606111800111322', 'INSUM1273202606111800111273', 'J-76989485', 1),
        ('ENTRA4473202606221655044473', 'INSUM4414202606221655044414', 'J-57449969', 1),
        ('ENTRA8706202606111657288706', 'INSUM8670202606111657288670', 'J-85831458', 1),
        ('ENTRA8773202606111753388773', 'INSUM8722202606111753388722', 'J-57449969', 1),
        ('ENTRA8865202606111736388865', 'INSUM8798202606111736388798', 'J-76989485', 1),
        ('ENTRA020720260611173920208', 'INGR000120260611', 'J-76989485', 1),
        ('ENTRA1001202606142223211002', 'INGR000220260611', 'J-33273667', 1),
        ('ENTRA1322202606111800111323', 'INGR000320260611', 'J-76989485', 1),
        ('ENTRA4473202606221655044471', 'INGR000420260611', 'J-57449969', 1),
        ('ENTRA8706202606111657288701', 'INGR000520260611', 'J-85831458', 1),
        ('ENTRA8773202606111753388771', 'INGR000620260611', 'J-57449969', 1),
        ('ENTRA8865202606111736388861', 'INGR000720260611', 'J-76989485', 1),
        ('ENTRA020720260611173920203', 'INGR000820260611', 'J-76989485', 1),
        ('ENTRA1001202606142223211003', 'INGR000920260611', 'J-33273667', 1),
        ('ENTRA1322202606111800111321', 'INGR001020260611', 'J-76989485', 1),
        ('ENTRA4473202606221655044472', 'INGR001120260611', 'J-57449969', 1),
        ('ENTRA8706202606111657288702', 'INGR001220260611', 'J-85831458', 1),
        ('ENTRA8773202606111753388772', 'INGR001320260611', 'J-57449969', 1),
        ('ENTRA8865202606111736388862', 'INGR001420260611', 'J-76989485', 1),
        ('ENTRA020720260611173920201', 'INGR001520260611', 'J-76989485', 1),
        ('ENTRA1001202606142223211004', 'INGR001620260611', 'J-33273667', 1),
        ('ENTRA1322202606111800111329', 'INGR001720260611', 'J-76989485', 1),
        ('ENTRA4473202606221655044474', 'INGR001820260611', 'J-57449969', 1),
        ('ENTRA8706202606111657288703', 'INGR001920260611', 'J-85831458', 1),
        ('ENTRA8773202606111753388774', 'INGR002020260611', 'J-57449969', 1)";
        $this->db->exec($sql);
    }
}