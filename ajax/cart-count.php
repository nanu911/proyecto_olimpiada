<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['usuario_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $count = (int)($result['total'] ?? 0);
    echo json_encode(['count' => $count]);
    
} catch (Exception $e) {
    error_log("Error en cart-count.php: " . $e->getMessage());
    echo json_encode(['count' => 0]);
}
?>
