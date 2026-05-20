<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Define constants needed by the helpers
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('BASE_URL', 'http://localhost/SICGOV/');
define('DS', DIRECTORY_SEPARATOR);

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Controllers\PerfilController;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Mock logged-in admin user session
$_SESSION['user'] = [
    'cedula' => 'V-00000000',
    'username' => 'admin_root',
    'nombres' => 'Admin',
    'apellidos' => 'Principal',
    'rol' => 'ADMINISTRADOR',
    'tema' => 0
];

echo "=== MOCKING USER SESSION FOR V-00000000 ===\n";

try {
    // 2. Test fetching user details via Helper
    echo "\n[TEST] getDatosUsuario()...\n";
    $datos = Helper::getDatosUsuario();
    print_r($datos);

    // 3. Test RegexHelper formats for PerfilController fields
    echo "\n[TEST] RegexHelper validations...\n";
    $fields = [
        ['val' => 'Admin', 'type' => 'Persona'],
        ['val' => 'Principal', 'type' => 'Persona'],
        ['val' => 'admin@goodvibes.com', 'type' => 'Correo'],
        ['val' => '0412-0000000', 'type' => 'Telefono'],
        ['val' => 'Caracas, Venezuela', 'type' => 'Direccion'],
        ['val' => 'M', 'type' => 'Sexo']
    ];

    foreach ($fields as $field) {
        $res = RegexHelper::ValidarFormatos($field['val'], $field['type']);
        echo "Validating '{$field['val']}' as {$field['type']}: " . ($res ? "VALID" : "INVALID") . "\n";
    }

    // 4. Run database updating logic simulation
    echo "\n[TEST] Simulating 'actualizar-perfil' database execution...\n";
    $db = Database::getConnection('business');
    $cedula = 'V-00000000';

    // Fetch current values
    $stmtSelect = $db->prepare("SELECT nombre, apellido, correo, telefono, direccion, sexo, fecha_nacimiento FROM persona WHERE cedula = :cedula");
    $stmtSelect->execute(['cedula' => $cedula]);
    $old_data = $stmtSelect->fetch(PDO::FETCH_ASSOC);
    echo "Current persona data in DB:\n";
    print_r($old_data);

    // Perform validation and simulation update
    $nombre = 'Admin Editado';
    $apellido = 'Principal Editado';
    $correo = 'admin_edit@goodvibes.com';
    $telefono = '0412-1111111';
    $direccion = 'Distrito Capital, Caracas, Av Urdaneta';
    $sexo = 'M';
    $fecha_nacimiento = '1995-10-10';

    $stmt = $db->prepare("UPDATE persona SET nombre = :nombre, apellido = :apellido, correo = :correo, telefono = :telefono, direccion = :direccion, sexo = :sexo, fecha_nacimiento = :fecha_nacimiento WHERE cedula = :cedula");
    $stmt->execute([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'correo' => $correo,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'sexo' => $sexo,
        'fecha_nacimiento' => $fecha_nacimiento,
        'cedula' => $cedula
    ]);

    echo "Successfully updated persona data in DB!\n";

    // Restore original values to prevent dirty DB state
    $stmtRestore = $db->prepare("UPDATE persona SET nombre = :nombre, apellido = :apellido, correo = :correo, telefono = :telefono, direccion = :direccion, sexo = :sexo, fecha_nacimiento = :fecha_nacimiento WHERE cedula = :cedula");
    $stmtRestore->execute([
        'nombre' => $old_data['nombre'] ?? 'Admin',
        'apellido' => $old_data['apellido'] ?? 'Principal',
        'correo' => $old_data['correo'] ?? 'admin@goodvibes.com',
        'telefono' => $old_data['telefono'] ?? '04120000000',
        'direccion' => $old_data['direccion'] ?? '',
        'sexo' => $old_data['sexo'] ?? 'M',
        'fecha_nacimiento' => $old_data['fecha_nacimiento'] ?? '1990-01-01',
        'cedula' => $cedula
    ]);
    echo "Restored original persona data in DB.\n";

    // 5. Test activity list query
    echo "\n[TEST] Simulating 'obtener-actividad' bitacora query...\n";
    $secDb = Database::getConnection('security');
    $stmtBit = $secDb->prepare("SELECT id_bitacora, modulo, accion, detalle, ip_address, fecha FROM bitacora WHERE cedula = :cedula ORDER BY fecha DESC LIMIT 15");
    $stmtBit->execute(['cedula' => $cedula]);
    $actividades = $stmtBit->fetchAll(PDO::FETCH_ASSOC);
    echo "Fetched " . count($actividades) . " timeline activities for V-00000000.\n";
    if (!empty($actividades)) {
        print_r(array_slice($actividades, 0, 3));
    }

    echo "\n=== ALL SIMULATED TESTS COMPLETED SUCCESSFULY ===\n";

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
