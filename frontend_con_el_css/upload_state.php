<?php
header('Content-Type: text/plain');

// Configura AWS (asegúrate de que las credenciales estén disponibles)
putenv('AWS_SHARED_CREDENTIALS_FILE=/var/www/.aws/credentials');
putenv('AWS_PROFILE=default');

$bucket = 'estado-terraform-alvaro';
$state_file = '/var/www/terraform/terraform.tfstate';
$s3_path = 'terraform/terraform.tfstate';

// Verificar si el archivo existe
if (!file_exists($state_file)) {
    die("❌ Archivo terraform.tfstate no encontrado");
}

// Comando AWS CLI para subir el archivo
$command = "aws s3 cp $state_file s3://$bucket/$s3_path 2>&1";
exec($command, $output, $return_var);

echo "Ejecutando: $command\n";
echo implode("\n", $output) . "\n";

if ($return_var === 0) {
    echo "✅ Estado subido correctamente a S3\n";
} else {
    echo "❌ Error al subir el estado (Código: $return_var)\n";
}
?>
