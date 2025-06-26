<?php
require_once 'config/database.php';
require_once 'config/email.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Procesar acciones del carrito
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $carrito_id = (int)$_POST['carrito_id'];
                $cantidad = (int)$_POST['cantidad'];
                
                if ($cantidad > 0) {
                    $query = "UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$cantidad, $carrito_id, $_SESSION['usuario_id']]);
                } else {
                    $query = "DELETE FROM carrito WHERE id = ? AND usuario_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$carrito_id, $_SESSION['usuario_id']]);
                }
                break;
                
            case 'remove':
                $carrito_id = (int)$_POST['carrito_id'];
                $query = "DELETE FROM carrito WHERE id = ? AND usuario_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$carrito_id, $_SESSION['usuario_id']]);
                break;
                
            case 'checkout':
                // Redirigir a página de pago
                redirectTo('checkout.php');
                break;
        }
    }
}

// Obtener items del carrito
$query = "SELECT c.*, p.nombre, p.descripcion, p.categoria, p.destino 
          FROM carrito c 
          JOIN productos p ON c.producto_id = p.id 
          WHERE c.usuario_id = ? 
          ORDER BY c.fecha_agregado DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$carrito_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_carrito = array_sum(array_map(function($item) {
    return $item['cantidad'] * $item['precio_unitario'];
}, $carrito_items));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Quantum Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <i class="fas fa-globe-americas"></i>
                    <h2>Quantum Tour</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="productos.php" class="nav-link"><i class="fas fa-suitcase-rolling"></i> Paquetes</a></li>
                    <li><a href="carrito.php" class="nav-link cart-link"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                    <?php if (isVendedor()): ?>
                        <li><a href="admin/" class="nav-link"><i class="fas fa-cog"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Salir (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a></li>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Carrito -->
    <section class="cart-section" style="margin-top: 80px; padding: 3rem 0; min-height: 70vh;">
        <div class="container">
            <h1 class="section-title animate__animated animate__fadeInUp">
                <i class="fas fa-shopping-cart"></i> Mi Carrito de Compras
            </h1>
            
            <?php if (empty($carrito_items)): ?>
                <div class="empty-cart animate__animated animate__fadeInUp">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Tu carrito está vacío</h3>
                    <p>¡Explora nuestros increíbles paquetes turísticos!</p>
                    <a href="productos.php" class="btn btn-primary">
                        <i class="fas fa-compass"></i> Ver Paquetes
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-container animate__animated animate__fadeInUp">
                    <div class="cart-items">
                        <?php foreach ($carrito_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <div class="product-bg-<?= $item['producto_id'] % 6 + 1 ?> item-bg">
                                    <span class="item-category">
                                        <i class="fas fa-tag"></i> <?= ucfirst($item['categoria']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="item-info">
                                <h4><?= htmlspecialchars($item['nombre']) ?></h4>
                                <p class="item-description"><?= htmlspecialchars($item['descripcion']) ?></p>
                                <?php if ($item['destino']): ?>
                                    <span class="item-destination">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['destino']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="item-controls">
                                <div class="quantity-controls">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="carrito_id" value="<?= $item['id'] ?>">
                                        <div class="quantity-input-group">
                                            <button type="button" class="qty-btn qty-minus"><i class="fas fa-minus"></i></button>
                                            <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" 
                                                   min="1" max="10" class="quantity-input">
                                            <button type="button" class="qty-btn qty-plus"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-sync-alt"></i> Actualizar
                                        </button>
                                    </form>
                                </div>
                                <div class="item-price">
                                    <span class="unit-price"><?= formatPrice($item['precio_unitario']) ?> c/u</span>
                                    <span class="total-price"><?= formatPrice($item['cantidad'] * $item['precio_unitario']) ?></span>
                                </div>
                                <form method="POST" class="remove-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="carrito_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Eliminar este producto del carrito?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3><i class="fas fa-receipt"></i> Resumen del Pedido</h3>
                            <div class="summary-details">
                                <div class="summary-line">
                                    <span>Productos (<?= count($carrito_items) ?>):</span>
                                    <span><?= formatPrice($total_carrito) ?></span>
                                </div>
                                <div class="summary-line">
                                    <span>Envío:</span>
                                    <span class="text-success">Gratis</span>
                                </div>
                                <div class="summary-line total">
                                    <span><strong>Total:</strong></span>
                                    <span><strong><?= formatPrice($total_carrito) ?></strong></span>
                                </div>
                            </div>
                            <form method="POST" class="checkout-form">
                                <input type="hidden" name="action" value="checkout">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-credit-card"></i> Proceder al Pago
                                </button>
                            </form>
                            <div class="security-info">
                                <p><i class="fas fa-shield-alt"></i> Compra 100% segura</p>
                                <p><i class="fas fa-undo"></i> Cancelación gratuita</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-globe-americas"></i>
                        <h3>Quantum Tour</h3>
                    </div>
                    <p>Transformamos tus sueños de viaje en experiencias extraordinarias.</p>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-phone"></i> Contacto</h4>
                    <p><i class="fas fa-envelope"></i> info@quantumtour.com</p>
                    <p><i class="fas fa-phone"></i> +54 11 4567-8900</p>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 Quantum Tour. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    
    <style>
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-cart-icon {
            font-size: 5rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .empty-cart h3 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .empty-cart p {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin-top: 2rem;
        }
        
        .cart-item {
            background: var(--dark-surface);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1.5rem;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .item-image {
            width: 120px;
            height: 80px;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .item-bg {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 0.5rem;
        }
        
        .item-category {
            background: var(--gradient-accent);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .item-info h4 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        
        .item-description {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .item-destination {
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .item-controls {
            display: flex;
            flex-direction: column;
            align-items: end;
            gap: 1rem;
        }
        
        .quantity-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-input-group {
            display: flex;
            align-items: center;
            background: var(--dark-card);
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin-bottom: 8px;
        }
        
        .qty-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            padding: 0.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .qty-btn:hover {
            color: var(--primary-color);
        }
        
        .quantity-input {
            width: 50px;
            padding: 0.5rem 0.25rem;
            border: none;
            background: transparent;
            color: var(--text-primary);
            text-align: center;
            font-family: "Montserrat", sans-serif;
        }
        
        .quantity-input:focus {
            outline: none;
        }
        
        .item-price {
            text-align: right;
        }
        
        .unit-price {
            display: block;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .total-price {
            display: block;
            color: var(--success-color);
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .summary-card {
            background: var(--dark-surface);
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid var(--border-color);
            position: sticky;
            top: 100px;
        }
        
        .summary-card h3 {
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-details {
            margin-bottom: 2rem;
        }
        
        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            color: var(--text-secondary);
        }
        
        .summary-line.total {
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.2rem;
            color: var(--text-primary);
        }
        
        .security-info {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .security-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .security-info i {
            color: var(--success-color);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .w-100 {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .cart-item {
                grid-template-columns: 1fr;
                gap: 1rem;
                text-align: center;
            }
            
            .item-controls {
                align-items: center;
                flex-direction: row;
                justify-content: space-between;
            }
            
            .quantity-controls {
                flex-direction: row;
                align-items: center;
            }
        }
        
        /* Estilos para la sección de consultas */
        .contact-section {
            padding: 5rem 0;
            background: var(--dark-surface);
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-top: 2rem;
        }
        
        .contact-info h3 {
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .contact-info p {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .contact-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--dark-card);
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
        }
        
        .contact-method i {
            font-size: 1.5rem;
            color: var(--primary-color);
            width: 30px;
            text-align: center;
        }
        
        .contact-method h4 {
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
        .contact-method p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .contact-form-container {
            background: var(--dark-card);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
        }
        
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
