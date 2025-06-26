<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Filtros
$categoria = $_GET['categoria'] ?? '';
$buscar = $_GET['buscar'] ?? '';

// Construir query
$where_conditions = ["disponible = 1"];
$params = [];

if ($categoria) {
    $where_conditions[] = "categoria = ?";
    $params[] = $categoria;
}

if ($buscar) {
    $where_conditions[] = "(nombre LIKE ? OR descripcion LIKE ? OR destino LIKE ?)";
    $search_term = "%$buscar%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);
$query = "SELECT * FROM productos WHERE $where_clause ORDER BY fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el filtro
$query_cat = "SELECT DISTINCT categoria FROM productos WHERE disponible = 1";
$stmt_cat = $db->prepare($query_cat);
$stmt_cat->execute();
$categorias = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paquetes Turísticos - Quantum Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo animate__animated animate__fadeInLeft">
                    <i class="fas fa-globe-americas"></i>
                    <h2>Quantum Tour</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="productos.php" class="nav-link"><i class="fas fa-suitcase-rolling"></i> Paquetes</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="carrito.php" class="nav-link cart-link"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                        <?php if (isVendedor()): ?>
                            <li><a href="admin/" class="nav-link"><i class="fas fa-cog"></i> Admin</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Salir (<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link"><i class="fas fa-sign-in-alt"></i> Ingresar</a></li>
                        <li><a href="register.php" class="nav-link"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Filtros -->
    <section class="filters-section" style="margin-top: 80px; padding: 2rem 0; background: var(--dark-surface);">
        <div class="container">
            <div class="filters-container" data-aos="fade-up">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <input type="text" name="buscar" placeholder="Buscar destino, paquete..." 
                               class="form-control" value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="filter-group">
                        <select name="categoria" class="form-control">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat ?>" <?= $categoria === $cat ? 'selected' : '' ?>>
                                    <?= ucfirst($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <?php if ($categoria || $buscar): ?>
                        <a href="productos.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- Productos -->
    <section class="products-section" style="padding: 3rem 0;">
        <div class="container">
            <h1 class="section-title" data-aos="fade-up">
                <i class="fas fa-suitcase-rolling"></i> Nuestros Paquetes Turísticos
            </h1>
            
            <?php if (empty($productos)): ?>
                <div class="text-center" data-aos="fade-up">
                    <div style="font-size: 4rem; margin-bottom: 1rem;"><i class="fas fa-search"></i></div>
                    <h3>No se encontraron productos</h3>
                    <p>Intenta con otros filtros de búsqueda</p>
                    <a href="productos.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver todos los productos
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($productos as $index => $producto): ?>
                    <div class="product-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="product-image product-bg-<?= $producto['id'] % 6 + 1 ?>">
                            <div class="product-overlay">
                                <span class="product-category">
                                    <i class="fas fa-tag"></i> <?= ucfirst($producto['categoria']) ?>
                                </span>
                                <span class="product-duration">
                                    <i class="fas fa-calendar-alt"></i> <?= $producto['duracion_dias'] ?> días
                                </span>
                            </div>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars($producto['descripcion']) ?></p>
                            <?php if ($producto['destino']): ?>
                                <p class="product-destination">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($producto['destino']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="product-footer">
                                <span class="product-price">
                                    <?= formatPrice($producto['precio']) ?>
                                </span>
                                <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-secondary add-to-cart" data-id="<?= $producto['id'] ?>">
                                        <i class="fas fa-cart-plus"></i> Agregar
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-secondary">
                                        <i class="fas fa-sign-in-alt"></i> Ingresar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
                    <p>Transformamos tus sueños de viaje en experiencias extraordinarias. Más de 15 años creando momentos inolvidables.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-map"></i> Destinos</h4>
                    <ul>
                        <li><a href="#">Europa</a></li>
                        <li><a href="#">Asia</a></li>
                        <li><a href="#">América</a></li>
                        <li><a href="#">África</a></li>
                        <li><a href="#">Oceanía</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-suitcase"></i> Servicios</h4>
                    <ul>
                        <li><a href="productos.php">Paquetes Turísticos</a></li>
                        <li><a href="#">Vuelos</a></li>
                        <li><a href="#">Hoteles</a></li>
                        <li><a href="#">Alquiler de Autos</a></li>
                        <li><a href="#">Seguros de Viaje</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-phone"></i> Contacto</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-envelope"></i> info@quantumtour.com</p>
                        <p><i class="fas fa-phone"></i> +54 11 4567-8900</p>
                        <p><i class="fas fa-whatsapp"></i> +54 9 11 1234-5678</p>
                        <p><i class="fas fa-map-marker-alt"></i> Av. Corrientes 1234, CABA</p>
                    </div>
                    
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 Quantum Tour. Todos los derechos reservados.</p>
                    <div class="footer-links">
                        <a href="#">Términos y Condiciones</a>
                        <a href="#">Política de Privacidad</a>
                        <a href="#">Política de Cancelación</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    
    <style>
        .filters-section {
            border-bottom: 1px solid var(--border-color);
        }
        
        .filters-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        @media (max-width: 768px) {
            .filters-form {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>
</body>
</html>
