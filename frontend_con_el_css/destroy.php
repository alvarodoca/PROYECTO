<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destruir Servidor FTP</title>
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #e74c3c;
            overflow-x: auto;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .command {
            background: #2c3e50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Destruir Servidor FTP</h1>
        
        <?php
        if (isset($_POST['destroy_ftp'])) {
            putenv('AWS_SHARED_CREDENTIALS_FILE=/var/www/.aws/credentials');
            putenv('AWS_PROFILE=default');
            
            $terraform_dir = "/var/www/terraform";
            $command = "cd $terraform_dir && /usr/bin/terraform destroy -auto-approve 2>&1";
            
            echo "<div class=\"command\">$command</div>";
            exec($command, $output, $return_var);
            
            echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
            
            if ($return_var === 0) {
                echo "<div class=\"success\">✅ Servidor FTP destruido correctamente.</div>";
            } else {
                echo "<div class=\"error\">❌ Error al destruir el servidor (Código: $return_var)</div>";
            }
        } else {
            header("Location: index.php");
            exit();
        }
        ?>
        
        <br>
        <a href="index.php">&larr; Volver al inicio</a>
    </div>
</body>
</html>