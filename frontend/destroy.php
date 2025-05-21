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
        .warning {
            color: #f39c12;
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
        // Configuración de AWS
        putenv('AWS_SHARED_CREDENTIALS_FILE=/var/www/.aws/credentials');
        putenv('AWS_PROFILE=default');

        // Configuración de rutas
        $terraform_dir = "/var/www/terraform";
        $terraform_bin = "/usr/bin/terraform";
        $bucket_s3 = "estado-terraform-alvaro";

        if (isset($_POST['destroy_ftp'])) {
            // 1. Destruir infraestructura
            $destroy_cmd = "cd $terraform_dir && $terraform_bin destroy -auto-approve 2>&1";
            
            echo "<h2>Destruyendo Infraestructura</h2>";
            echo "<div class=\"command\">$destroy_cmd</div>";
            
            exec($destroy_cmd, $destroy_output, $destroy_return);
            echo "<pre>" . htmlspecialchars(implode("\n", $destroy_output)) . "</pre>";
            
            if ($destroy_return !== 0) {
                echo "<div class=\"error\">❌ Error al destruir infraestructura (Código: $destroy_return)</div>";
                die();
            }
            
            // 2. SUBIDA DEL ESTADO A S3 (igual que en deploy.php)
            echo "<h2>Actualizando Estado en S3</h2>";
            $state_file = "$terraform_dir/terraform.tfstate";
            $s3_path = "terraform/terraform.tfstate";
            $upload_cmd = "aws s3 cp $state_file s3://$bucket_s3/$s3_path 2>&1";
            
            echo "<div class=\"command\">$upload_cmd</div>";
            
            if (file_exists($state_file)) {
                exec($upload_cmd, $upload_output, $upload_return);
                echo "<pre>" . htmlspecialchars(implode("\n", $upload_output)) . "</pre>";
                
                if ($upload_return === 0) {
                    echo "<div class=\"success\">✅ Estado de Terraform actualizado en S3</div>";
                    echo "<div class=\"success\">✅ Infraestructura destruida correctamente</div>";
                } else {
                    echo "<div class=\"warning\">⚠️ Infraestructura destruida pero falló la subida a S3</div>";
                    echo "<div class=\"error\">❌ Error al subir el estado (Código: $upload_return)</div>";
                }
            } else {
                echo "<div class=\"error\">❌ Archivo terraform.tfstate no encontrado</div>";
            }
        } else {
            echo "<div class=\"error\">Acceso no autorizado</div>";
        }
        ?>
        
        <br>
        <a href="index.php">&larr; Volver al inicio</a>
    </div>
</body>
</html>