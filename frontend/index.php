<?php
session_start();

// Redirigir a login si no está autenticado
if (!($_SESSION['authenticated'] ?? false)) {
    header('Location: login.php');
    exit();
}

// Simular estado del servidor (puedes reemplazar con lógica real)
$serverStatus = [
    'deployed' => file_exists('/var/www/terraform/terraform.tfstate'),
    'last_deploy' => date('Y-m-d H:i:s', filemtime('/var/www/terraform/terraform.tfstate') ?: time()),
];


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control FTP - AWS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            color: var(--primary);
        }
        
        .logout-btn {
            color: var(--danger);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-icon {
            font-size: 24px;
        }
        
        .card-body {
            margin-bottom: 20px;
        }
        
        .card-footer {
            margin-top: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            border: none;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .status-card {
            grid-column: span 2;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }
        
        .status-inactive {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }
        
        .status-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .info-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .status-card {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-server"></i>
                <span>Panel de Control FTP</span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard">
            <!-- Card de Estado -->
            <div class="card status-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle card-icon"></i>
                        Estado del Servidor
                    </h2>
                </div>
                <div class="card-body">
                    <div class="status-indicator">
                        <span class="status-badge <?php echo $serverStatus['deployed'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $serverStatus['deployed'] ? 'ACTIVO' : 'INACTIVO'; ?>
                        </span>
                        <span><?php echo $serverStatus['deployed'] ? 'El servidor FTP está en funcionamiento' : 'No hay servidores FTP desplegados'; ?></span>
                    </div>
                    
                    <?php if ($serverStatus['deployed']): ?>
                    <div class="status-info">
                        <div class="info-item">
                            <div class="info-label">Último despliegue</div>
                            <div class="info-value"><?php echo $serverStatus['last_deploy']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tipo de instancia</div>
                            <div class="info-value">t2.micro</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Región AWS</div>
                            <div class="info-value">us-east-1</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Card de Despliegue -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-cloud-upload-alt card-icon"></i>
                        Desplegar Servidor
                    </h2>
                </div>
                <div class="card-body">
                    <p>Implementa una nueva instancia FTP en AWS con todos los recursos necesarios.</p>
                    <ul style="margin: 15px 0; padding-left: 20px; color: var(--gray);">
                        <li>Instancia EC2 t2.micro</li>
                        <li>Servidor FTP configurado con seguridad TLS</li>
                        <li>Bucket S3 para almacenamiento</li>
                        <li>Grupos de seguridad configurados</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <form action="deploy.php" method="post">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-rocket"></i> Desplegar Servidor FTP
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Card de Destrucción -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-trash-alt card-icon"></i>
                        Eliminar Servidor
                    </h2>
                </div>
                <div class="card-body">
                    <p>Elimina permanentemente todos los recursos asociados al servidor FTP en AWS.</p>
                    <div style="background-color: #fff8f8; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid var(--danger);">
                        <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                        <strong>Advertencia:</strong> Esta acción no se puede deshacer. Todos los datos se perderán permanentemente.
                    </div>
                </div>
                <div class="card-footer">
                    <form id="destroyForm" action="destroy.php" method="post">
                        <button type="submit" name="destroy_ftp" value="1" class="btn btn-danger">
                            <i class="fas fa-skull-crossbones"></i> Destruir Servidor
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmación -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-content">
            <h3 style="margin-top: 0;"><i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i> Confirmar Destrucción</h3>
            <p>¿Estás seguro que deseas eliminar permanentemente el servidor FTP y todos sus recursos asociados?</p>
            <div class="modal-actions">
                <button type="button" onclick="hideConfirmation()" style="background-color: var(--gray);" class="btn">Cancelar</button>
                <button type="button" onclick="submitDestroy()" class="btn btn-danger">Confirmar Destrucción</button>
            </div>
        </div>
    </div>

    <!-- Botón para ver historial de logins -->
    <div style="margin-top: 40px; text-align: center;">
        <a href="ver_logins.php" class="btn btn-primary" style="max-width: 300px; margin: 0 auto;">
            <i class="fas fa-user-check"></i> Ver Historial de Logins
        </a>
    </div>
    
    <script>
        function showConfirmation() {
            document.getElementById('confirmationModal').style.display = 'flex';
        }
        
        function hideConfirmation() {
            document.getElementById('confirmationModal').style.display = 'none';
        }
        
        function submitDestroy() {
            document.getElementById('destroyForm').submit();
        }
    </script>
</body>
</html>