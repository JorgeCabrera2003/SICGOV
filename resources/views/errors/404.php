<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | SICGOV</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 90%;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: #ff4757;
            line-height: 1;
            text-shadow: 3px 3px 0 rgba(255,71,87,0.3);
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .error-message {
            color: #7f8c8d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-home {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-home:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
            color: white;
        }
        
        .btn-back {
            background: #48c78e;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-back:hover {
            background: #3db87e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72,199,142,0.4);
            color: white;
        }
        
        .error-icon {
            font-size: 5rem;
            color: #ff4757;
            margin-bottom: 1rem;
        }
        
        .error-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #6c757d;
            border-left: 4px solid #ff4757;
            text-align: left;
        }
        
        .error-details code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            color: #d63384;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .btn-home, .btn-back {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Icono de error -->
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <!-- Código de error -->
        <div class="error-code">404</div>
        
        <!-- Título del error -->
        <h1 class="error-title">¡Página no encontrada!</h1>
        
        <!-- Mensaje de error -->
        <p class="error-message">
            Lo sentimos, la página que estás buscando no existe o ha sido movida.<br>
            Verifica la URL o contacta al administrador.
        </p>
        
        <!-- Acciones -->
        <div class="error-actions">
            <a href="<?= BASE_URL ?>/?page=home" class="btn-home">
                <i class="fas fa-home"></i>
                Ir al Dashboard
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver Atrás
            </a>
        </div>
        
        <!-- Detalles técnicos (solo visible para administradores) -->
        <?php if (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'ADMINISTRADOR'): ?>
        <div class="error-details">
            <p class="mb-2">
                <i class="fas fa-bug me-2"></i>
                <strong>Información de depuración:</strong>
            </p>
            <p class="mb-1">
                <code>URL solicitada: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?></code>
            </p>
            <p class="mb-1">
                <code>Página: <?= htmlspecialchars($_GET['page'] ?? '') ?></code>
            </p>
            <p class="mb-0">
                <code>Método: <?= $_SERVER['REQUEST_METHOD'] ?? '' ?></code>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS (opcional, para algunas interacciones) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>