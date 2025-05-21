<?php
class Database {
    // Usamos la ruta absoluta del contenedor
    private $dbFile = '/var/www/db/logins.db';
    private $pdo;

    public function __construct() {
        $this->connect();
        $this->createTables();
    }

    private function connect() {
        try {
            // Aseguramos que el directorio existe
            if (!file_exists(dirname($this->dbFile))) {
                mkdir(dirname($this->dbFile), 0755, true);
            }
            
            $this->pdo = new PDO('sqlite:'.$this->dbFile);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA journal_mode=WAL;');
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos. Por favor, inténtelo más tarde.");
        }
    }

    private function createTables() {
        $query = "CREATE TABLE IF NOT EXISTS login_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            login_time DATETIME NOT NULL,
            ip_address TEXT,
            user_agent TEXT
        )";
        
        try {
            $this->pdo->exec($query);
        } catch (PDOException $e) {
            error_log("Error al crear tablas: " . $e->getMessage());
            throw $e;
        }
    }

    public function logLogin($username) {
        $query = "INSERT INTO login_logs (username, login_time, ip_address, user_agent) 
                  VALUES (:username, :login_time, :ip, :ua)";
        
        try {
            $now = (new DateTime('now', new DateTimeZone('Europe/Madrid')))->format('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':username' => $username,
                ':login_time' => $now,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Desconocida',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al registrar login: " . $e->getMessage());
            throw new Exception("Error al registrar el acceso.");
        }
    }

    public function getLoginHistory($limit = 10) {
        $query = "SELECT * FROM login_logs ORDER BY login_time DESC LIMIT :limit";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
    
    public function isConnected() {
        return $this->pdo !== null;
    }
}
?>
