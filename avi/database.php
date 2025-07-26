<?php
class Database {
    private $host = "localhost";
    private $db_name = "chatbot_unac";
    private $username = "bot_user";
    private $password = "TuContraseñaSegura123";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            throw new Exception("Error al conectar con la base de datos. Por favor intente más tarde.");
        }
data
        return $this->conn;
    }
}
?>
