<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'turismo_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Iniciar sesión
session_start();

// Funciones auxiliares
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
}

function isVendedor() {
    return isset($_SESSION['tipo_usuario']) && ($_SESSION['tipo_usuario'] === 'vendedor' || $_SESSION['tipo_usuario'] === 'admin');
}

function redirectTo($url) {
    header("Location: $url");
    exit();
}

function formatPrice($price) {
    return '$' . number_format($price, 0, ',', '.');
}
?>
