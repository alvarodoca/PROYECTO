<?php
session_start();

// Redirigir a login si no está autenticado
if (!($_SESSION['authenticated'] ?? false)) {
    header('Location: login.php');
    exit();
}

header('Content-Type: text/html; charset=UTF-8');
?>
<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despliegue FTP en AWS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-top: 4px solid #3498db;
        }
        .card.danger {
            border-top-color: #e74c3c;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }
        button:hover {
            background-color: #2980b9;
        }
        button.danger {
            background-color: #e74c3c;
        }
        button.danger:hover {
            background-color: #c0392b;
        }
        .status {
            margin-top: 30px;
            padding: 15px;
            border-radius: 4px;
            background-color: #f8f9fa;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="logout.php" style="color: #e74c3c;">Cerrar Sesión</a>
        </div>
    <div class="container">
        <h1>Sistema de Despliegue FTP en AWS</h1>
        
        <div class="card">
            <h2>Desplegar Nuevo Servidor FTP</h2>
            <p>Implementa una nueva instancia FTP en AWS usando Terraform</p>
            <form action="deploy.php" method="post">
                <button type="submit" name="deploy_ftp">Desplegar Servidor FTP</button>
            </form>
        </div>
        
        <div class="card danger">
            <h2>Destruir Servidor FTP</h2>
            <p>Elimina permanentemente la instancia FTP y todos sus recursos</p>
            <form action="destroy.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas destruir el servidor FTP? Esta acción no se puede deshacer.');">
                <button type="submit" name="destroy_ftp" class="danger">Destruir Servidor FTP</button>
            </form>
        </div>
        
        <div class="status">
            <?php
            // Puedes agregar aquí un chequeo del estado actual
            echo "Sistema listo para operar";
            ?>
        </div>
    </div>
</body>
</html>
