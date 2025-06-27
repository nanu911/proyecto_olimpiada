<?php
class Database {
    private $host = 'dpg-d1f0637fte5s73aa3cl0-a';
    private $db_name = 'turismo_db_3qxb';
    private $username = 'tecnica4';
    private $password = 'K51Kdg9Cfv6ftSETX3uZfcpy5ALbXgg5';
    private $port =  '5432';
    private $conn;

    public function getConnection() {
        $this->conn = null;
    try {
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Conexión exitosa"; // Opcional para debug
    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        exit;
    }
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
