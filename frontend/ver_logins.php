<?php
require_once('/var/www/config/database.php');

$db = new Database();
$logs = $db->getLoginHistory(20);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Logins</title>
    <style>
        body { font-family: sans-serif; background: #f9f9f9; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: white; }
    </style>
</head>
<body>
    <h1>Historial de Accesos</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>IP</th>
            <th>User Agent</th>
            <th>Fecha</th>
        </tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['id']) ?></td>
            <td><?= htmlspecialchars($log['username']) ?></td>
            <td><?= htmlspecialchars($log['ip_address']) ?></td>
            <td><?= htmlspecialchars($log['user_agent']) ?></td>
            <td><?= htmlspecialchars($log['login_time']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
