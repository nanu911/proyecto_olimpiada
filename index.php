Hola hola hola
$query = "SELECT * FROM productos WHERE disponible = 1 ORDER BY fecha_creacion DESC LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de consulta
$consulta_success = '';
$consulta_error = '';

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'consulta') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $asunto = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);
    
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        $consulta_error = 'Por favor complete todos los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $consulta_error = 'Por favor ingrese un email válido';
    } else {
        try {
            // Guardar consulta en base de datos
            $query = "INSERT INTO consultas (nombre, email, telefono, asunto, mensaje, fecha_consulta) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            $stmt->execute([$nombre, $email, $telefono, $asunto, $mensaje]);
            
            // Simular envío de email
            // $resultado_email = enviarEmailConsulta($email, $nombre, $asunto);

            if ($resultado_email['success']) {
                $consulta_success = 'Consulta enviada correctamente. ' . 
                                   ($resultado_email['email_real_enviado'] ? 
                                    'Recibirás una confirmación por email.' : 
                                    'Hemos guardado tu consulta y te responderemos pronto.') . 
                                   ' Te responderemos en la brevedad.';
            } else {
                $consulta_success = 'Consulta enviada correctamente. Te responderemos en la brevedad.';
            }
        } catch (Exception $e) {
            $consulta_error = 'Error al enviar la consulta. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantum Tour - Experiencias de Viaje Extraordinarias</title>
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

<!-- Hero Section -->
   <section class="hero">
        <div class="cosmic-background"></div>
        <div class="hero-content">
            <div class="planet-container">
                <div class="planet">
                    <div class="rings"></div>
                </div>
                <div class="travel-path"></div>
            </div>
            
            <h1 class="hero-title">
                <span class="title-part animate_animated animatefadeInUp animate_delay-1s">Explora el mundo</span>
                <span class="title-part highlight animate_animated animatefadeInUp animate_delay-2s">con Quantum Tour</span>
            </h1>
            
            <p class="hero-subtitle animate_animated animatefadeInUp animate_delay-3s">
                Donde los viajes de tus sueños se hacen realidad
            </p>
            
            <div class="hero-cta animate_animated animatefadeInUp animate_delay-4s">
                <a href="productos.php" class="cta-button">
                    <span>Descubrir Paquetes</span>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section id="featured" class="featured-products" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-gem"></i> Paquetes Destacados
            </h2>
            <div class="products-grid">
                <?php foreach ($productos_destacados as $index => $producto): ?>
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
        </div>
    </section>

    <!-- Features -->
    <section class="features" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-award"></i> ¿Por Qué Elegir Quantum Tour?
            </h2>
            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3>Itinerarios Personalizados</h3>
                    <p>Diseñamos cada viaje según tus preferencias y presupuesto para una experiencia única</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Alianzas Estratégicas</h3>
                    <p>Trabajamos con los mejores hoteles y aerolíneas para garantizar calidad y mejores precios</p>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>15 Años de Experiencia</h3>
                    <p>Más de una década creando experiencias inolvidables para miles de viajeros</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Formulario de Consultas -->
    <section id="consultas" class="contact-section" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-envelope"></i> ¿Tienes Alguna Consulta?
            </h2>
            <div class="contact-container" style= "margin-bottom: 50px;">
               
                <div class="contact-form-container" data-aos="fade-left">
                    <?php if ($consulta_success): ?>
                        <div class="alert alert-success animate__animated animate__bounceIn">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($consulta_success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($consulta_error): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($consulta_error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form" id="consultaForm">
                        <input type="hidden" name="action" value="consulta">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre"><i class="fas fa-user"></i> Nombre Completo *</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" 
                                       placeholder="Tu nombre completo" required 
                                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       placeholder="tu@email.com" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" class="form-control" 
                                       placeholder="+54 11 1234-5678" 
                                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="asunto"><i class="fas fa-tag"></i> Asunto *</label>
                                <select id="asunto" name="asunto" class="form-control" required>
                                    <option value="">Selecciona un asunto</option>
                                    <option value="Información de paquetes" <?= ($_POST['asunto'] ?? '') === 'Información de paquetes' ? 'selected' : '' ?>>Información de paquetes</option>
                                    <option value="Cotización personalizada" <?= ($_POST['asunto'] ?? '') === 'Cotización personalizada' ? 'selected' : '' ?>>Cotización personalizada</option>
                                    <option value="Modificar reserva" <?= ($_POST['asunto'] ?? '') === 'Modificar reserva' ? 'selected' : '' ?>>Modificar reserva</option>
                                    <option value="Cancelación" <?= ($_POST['asunto'] ?? '') === 'Cancelación' ? 'selected' : '' ?>>Cancelación</option>
                                    <option value="Reclamo" <?= ($_POST['asunto'] ?? '') === 'Reclamo' ? 'selected' : '' ?>>Reclamo</option>
                                    <option value="Otros" <?= ($_POST['asunto'] ?? '') === 'Otros' ? 'selected' : '' ?>>Otros</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensaje"><i class="fas fa-comment"></i> Mensaje *</label>
                            <textarea id="mensaje" name="mensaje" class="form-control" rows="5" 
                                      placeholder="Cuéntanos en qué podemos ayudarte..." required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-50">
                            <span class="btn-text"><i class="fas fa-paper-plane"></i> Enviar Consulta</span>
                            <span class="loading" style="display: none;"></span>
                        </button>
                    </form>
                </div>
            </div>
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
    
    <script>
        // Formulario de consulta
        $('#consultaForm').on('submit', function() {
            $('.btn-text').hide();
            $('.loading').show();
        });
    </script>
</body>
</html>
