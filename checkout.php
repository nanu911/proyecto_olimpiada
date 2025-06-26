<?php
require_once 'config/database.php';
require_once 'config/email.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Verificar que hay items en el carrito
$query = "SELECT c.*, p.nombre, p.descripcion FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$carrito_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($carrito_items)) {
    redirectTo('carrito.php');
}

$total_carrito = array_sum(array_map(function($item) {
    return $item['cantidad'] * $item['precio_unitario'];
}, $carrito_items));

// Procesar pago
$pago_success = '';
$pago_error = '';

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'procesar_pago') {
    $nombre_tarjeta = trim($_POST['nombre_tarjeta']);
    $numero_tarjeta = trim($_POST['numero_tarjeta']);
    $mes_vencimiento = trim($_POST['mes_vencimiento']);
    $ano_vencimiento = trim($_POST['ano_vencimiento']);
    $cvv = trim($_POST['cvv']);
    
    if (empty($nombre_tarjeta) || empty($numero_tarjeta) || empty($mes_vencimiento) || empty($ano_vencimiento) || empty($cvv)) {
        $pago_error = 'Por favor complete todos los campos de la tarjeta';
    } elseif (strlen($numero_tarjeta) < 16) {
        $pago_error = 'Número de tarjeta inválido';
    } elseif (strlen($cvv) < 3) {
        $pago_error = 'CVV inválido';
    } else {
        // Simular procesamiento de pago
        $db->beginTransaction();
        try {
            // Generar número de pedido
            $numero_pedido = 'QT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Crear pedido
            $query = "INSERT INTO pedidos (numero_pedido, usuario_id, total, estado) VALUES (?, ?, ?, 'procesando')";
            $stmt = $db->prepare($query);
            $stmt->execute([$numero_pedido, $_SESSION['usuario_id'], $total_carrito]);
            $pedido_id = $db->lastInsertId();
            
            // Mover items del carrito al detalle del pedido
            $query = "INSERT INTO pedidos_detalle (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                      SELECT ?, producto_id, cantidad, precio_unitario, (cantidad * precio_unitario)
                      FROM carrito WHERE usuario_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$pedido_id, $_SESSION['usuario_id']]);
            
            // Obtener detalles para el email
            $query = "SELECT pd.*, p.nombre FROM pedidos_detalle pd JOIN productos p ON pd.producto_id = p.id WHERE pd.pedido_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$pedido_id]);
            $productos_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Limpiar carrito
            $query = "DELETE FROM carrito WHERE usuario_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['usuario_id']]);
            
            $db->commit();
            
            // Enviar email de confirmación
            $resultado_email = enviarEmailCompra(
                $_SESSION['usuario_email'], 
                $_SESSION['usuario_nombre'], 
                $numero_pedido, 
                $total_carrito, 
                $productos_pedido
            );

            $pago_success = "¡Pago procesado exitosamente! Número de pedido: $numero_pedido. " . 
                           ($resultado_email['email_real_enviado'] ? 
                            "Recibirás un email de confirmación." : 
                            "Hemos guardado los detalles de tu compra.");
            
        } catch (Exception $e) {
            $db->rollback();
            $pago_error = "Error al procesar el pago. Intente nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Quantum Tour</title>
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
                    <li><a href="carrito.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                    <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Checkout -->
    <section class="checkout-section" style="margin-top: 80px; padding: 3rem 0; min-height: 70vh;">
        <div class="container">
            <h1 class="section-title animate__animated animate__fadeInUp">
                <i class="fas fa-credit-card"></i> Finalizar Compra
            </h1>
            
            <?php if ($pago_success): ?>
                <div class="success-container animate__animated animate__bounceIn">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>¡Compra Exitosa!</h2>
                    <p><?= htmlspecialchars($pago_success) ?></p>
                    <div class="success-actions">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                        <a href="productos.php" class="btn btn-secondary">
                            <i class="fas fa-compass"></i> Seguir Explorando
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="checkout-container animate__animated animate__fadeInUp">
                    <div class="payment-form-container">
                        <h3><i class="fas fa-credit-card"></i> Información de Pago</h3>
                        
                        <?php if ($pago_error): ?>
                            <div class="alert alert-danger animate__animated animate__shakeX">
                                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($pago_error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="payment-form" id="paymentForm">
                            <input type="hidden" name="action" value="procesar_pago">
                            
                            <div class="form-group">
                                <label for="nombre_tarjeta"><i class="fas fa-user"></i> Nombre en la Tarjeta *</label>
                                <input type="text" id="nombre_tarjeta" name="nombre_tarjeta" class="form-control" 
                                       placeholder="Como aparece en la tarjeta" required 
                                       value="<?= htmlspecialchars($_POST['nombre_tarjeta'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="numero_tarjeta"><i class="fas fa-credit-card"></i> Número de Tarjeta *</label>
                                <input type="text" id="numero_tarjeta" name="numero_tarjeta" class="form-control" 
                                       placeholder="1234 5678 9012 3456" required maxlength="19"
                                       value="<?= htmlspecialchars($_POST['numero_tarjeta'] ?? '') ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="mes_vencimiento"><i class="fas fa-calendar"></i> Mes *</label>
                                    <select id="mes_vencimiento" name="mes_vencimiento" class="form-control" required>
                                        <option value="">Mes</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= sprintf('%02d', $i) ?>" <?= ($_POST['mes_vencimiento'] ?? '') === sprintf('%02d', $i) ? 'selected' : '' ?>>
                                                <?= sprintf('%02d', $i) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ano_vencimiento"><i class="fas fa-calendar"></i> Año *</label>
                                    <select id="ano_vencimiento" name="ano_vencimiento" class="form-control" required>
                                        <option value="">Año</option>
                                        <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($_POST['ano_vencimiento'] ?? '') == $i ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cvv"><i class="fas fa-lock"></i> CVV *</label>
                                    <input type="text" id="cvv" name="cvv" class="form-control" 
                                           placeholder="123" required maxlength="4"
                                           value="<?= htmlspecialchars($_POST['cvv'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="payment-info">
                                <p><i class="fas fa-info-circle"></i> <strong>Datos de prueba:</strong></p>
                                <p>Tarjeta: 4111 1111 1111 1111 | CVV: 123 | Cualquier fecha futura</p>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <span class="btn-text"><i class="fas fa-lock"></i> Pagar <?= formatPrice($total_carrito) ?></span>
                                <span class="loading" style="display: none;"></span>
                            </button>
                        </form>
                    </div>
                    
                    <div class="order-summary">
                        <h3><i class="fas fa-receipt"></i> Resumen del Pedido</h3>
                        <div class="summary-items">
                            <?php foreach ($carrito_items as $item): ?>
                            <div class="summary-item">
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($item['nombre']) ?></h4>
                                    <p>Cantidad: <?= $item['cantidad'] ?></p>
                                </div>
                                <div class="item-price">
                                    <?= formatPrice($item['cantidad'] * $item['precio_unitario']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-total">
                            <div class="total-line">
                                <span>Subtotal:</span>
                                <span><?= formatPrice($total_carrito) ?></span>
                            </div>
                            <div class="total-line">
                                <span>Impuestos:</span>
                                <span>Incluidos</span>
                            </div>
                            <div class="total-line final">
                                <span><strong>Total:</strong></span>
                                <span><strong><?= formatPrice($total_carrito) ?></strong></span>
                            </div>
                        </div>
                        <div class="security-badges">
                            <div class="badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Pago Seguro</span>
                            </div>
                            <div class="badge">
                                <i class="fas fa-lock"></i>
                                <span>SSL Encriptado</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Formatear número de tarjeta
            $('#numero_tarjeta').on('input', function() {
                let value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                $(this).val(formattedValue);
            });
            
            // Solo números en CVV
            $('#cvv').on('input', function() {
                $(this).val($(this).val().replace(/[^0-9]/g, ''));
            });
            
            // Formulario de pago
            $('#paymentForm').on('submit', function() {
                $('.btn-text').hide();
                $('.loading').show();
            });
        });
    </script>
    
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin-top: 2rem;
        }
        
        .payment-form-container {
            background: var(--dark-surface);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
        }
        
        .payment-form-container h3 {
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .payment-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }
        
        .payment-info {
            background: var(--dark-card);
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        
        .payment-info p {
            margin: 0.25rem 0;
            color: var(--text-secondary);
        }
        
        .order-summary {
            background: var(--dark-surface);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .order-summary h3 {
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-items {
            margin-bottom: 1.5rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .item-details h4 {
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
        .item-details p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .item-price {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .summary-total {
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        
        .total-line.final {
            font-size: 1.2rem;
            color: var(--text-primary);
            border-top: 1px solid var(--border-color);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .security-badges {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--success-color);
            font-size: 0.9rem;
        }
        
        .success-container {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--dark-surface);
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            margin-top: 2rem;
        }
        
        .success-icon {
            font-size: 5rem;
            color: var(--success-color);
            margin-bottom: 1rem;
        }
        
        .success-container h2 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .success-container p {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .security-badges {
                flex-direction: column;
                align-items: center;
            }
            
            .success-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</body>
</html>
