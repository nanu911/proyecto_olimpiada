<?php
require_once '../config/database.php';

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Debug: Log de la petición
error_log("add-to-cart.php: Iniciando script");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

if (!isLoggedIn()) {
    error_log("Usuario no logueado");
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
    exit;
}

if (!isset($_POST['producto_id']) || empty($_POST['producto_id'])) {
    error_log("Producto ID no especificado");
    echo json_encode(['success' => false, 'message' => 'Producto no especificado']);
    exit;
}

$producto_id = (int)$_POST['producto_id'];
$usuario_id = $_SESSION['usuario_id'];

error_log("Procesando: producto_id=$producto_id, usuario_id=$usuario_id");

$database = new Database();
$db = $database->getConnection();

try {
    // Verificar que el producto existe y está disponible
    $query = "SELECT id, precio FROM productos WHERE id = ? AND disponible = 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        error_log("Producto no encontrado: ID $producto_id");
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    error_log("Producto encontrado: " . print_r($producto, true));
    
    // Verificar si ya está en el carrito
    $query = "SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$usuario_id, $producto_id]);
    $carrito_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($carrito_item) {
        // Actualizar cantidad
        $query = "UPDATE carrito SET cantidad = cantidad + 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$carrito_item['id']]);
        error_log("Actualizando cantidad existente: " . ($result ? "OK" : "ERROR"));
    } else {
        // Agregar nuevo item
        $query = "INSERT INTO carrito (usuario_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, 1, ?)";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$usuario_id, $producto_id, $producto['precio']]);
        error_log("Agregando nuevo item: " . ($result ? "OK" : "ERROR"));
    }
    
    error_log("Producto agregado exitosamente");
    echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito']);
    
} catch (Exception $e) {
    error_log("Error en add-to-cart.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
