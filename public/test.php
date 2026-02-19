<?php
// public/test.php
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Definir BASE_URL manualmente para pruebas
define('BASE_URL', 'http://localhost/good-vibes/public');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Good Vibes</title>
    <link href="<?php echo BASE_URL; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 20px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="mb-4">🔍 Diagnóstico del Sistema Good Vibes</h1>
        
        <!-- Información del servidor -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📌 Información del Servidor</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>BASE_URL</th>
                        <td><code><?php echo BASE_URL; ?></code></td>
                    </tr>
                    <tr>
                        <th>Document Root</th>
                        <td><code><?php echo $_SERVER['DOCUMENT_ROOT']; ?></code></td>
                    </tr>
                    <tr>
                        <th>SCRIPT_NAME</th>
                        <td><code><?php echo $_SERVER['SCRIPT_NAME']; ?></code></td>
                    </tr>
                    <tr>
                        <th>REQUEST_URI</th>
                        <td><code><?php echo $_SERVER['REQUEST_URI']; ?></code></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Verificación de archivos CSS -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🎨 Verificación de Assets</h5>
            </div>
            <div class="card-body">
                <?php
                $assets = [
                    'Bootstrap CSS' => '/assets/bootstrap/css/bootstrap.min.css',
                    'Main CSS' => '/assets/css/main.css',
                    'Style CSS' => '/assets/css/style.css',
                    'Font Awesome' => '/assets/css/all.min.css',
                    'jQuery' => '/assets/js/jquery.min.js',
                    'Main JS' => '/assets/js/main.js',
                    'Utils JS' => '/assets/js/utils.js',
                    'Logo' => '/assets/img/logo.png',
                    'Favicon' => '/assets/img/favicon.png'
                ];
                
                echo '<div class="row">';
                foreach ($assets as $nombre => $ruta) {
                    $url = BASE_URL . $ruta;
                    $headers = @get_headers($url);
                    $existe = $headers && strpos($headers[0], '200') !== false;
                    $clase = $existe ? 'ok' : 'error';
                    $icono = $existe ? '✅' : '❌';
                    
                    echo '<div class="col-md-6 mb-2">';
                    echo "<span class='$clase'>$icono $nombre</span><br>";
                    echo "<small><code>$url</code></small>";
                    echo '</div>';
                }
                echo '</div>';
                ?>
            </div>
        </div>
        
        <!-- Enlaces de prueba -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">🔗 Enlaces de Prueba</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?php echo BASE_URL; ?>/?page=login" class="list-group-item list-group-item-action" target="_blank">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=home" class="list-group-item list-group-item-action" target="_blank">
                        <i class="fas fa-home me-2"></i> Dashboard (Home)
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=productos" class="list-group-item list-group-item-action" target="_blank">
                        <i class="fas fa-box me-2"></i> Productos
                    </a>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="list-group-item list-group-item-action" target="_blank">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Variables de entorno -->
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">🔐 Variables de Entorno</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>DB_HOST</th>
                        <td><?php echo $_ENV['DB_HOST'] ?? 'No definido'; ?></td>
                    </tr>
                    <tr>
                        <th>DB_USER</th>
                        <td><?php echo $_ENV['DB_USER'] ?? 'No definido'; ?></td>
                    </tr>
                    <tr>
                        <th>DB_NAME_SYSTEM</th>
                        <td><?php echo $_ENV['DB_NAME_SYSTEM'] ?? 'No definido'; ?></td>
                    </tr>
                    <tr>
                        <th>DB_NAME_USER</th>
                        <td><?php echo $_ENV['DB_NAME_USER'] ?? 'No definido'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>