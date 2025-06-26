<?php
require_once 'config/database.php';
require_once 'config/email.php';

// Solo permitir acceso a administradores o en desarrollo
if (!isLoggedIn() || (!isAdmin() && $_SERVER['HTTP_HOST'] !== 'localhost')) {
    redirectTo('index.php');
}

$emails = listarEmailsGenerados();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emails Generados - Quantum Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li><a href="ver_emails.php" class="nav-link"><i class="fas fa-envelope"></i> Emails</a></li>
                    <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Contenido -->
    <section style="margin-top: 80px; padding: 3rem 0; min-height: 70vh;">
        <div class="container">
            <h1 class="section-title">
                <i class="fas fa-envelope"></i> Emails Generados (Simulación)
            </h1>
            
            <div class="alert alert-info" style="margin-bottom: 2rem;">
                <i class="fas fa-info-circle"></i> 
                <strong>Modo Desarrollo:</strong> Los emails se guardan como archivos HTML para visualización. 
                En producción se enviarían por email real.
            </div>
            
            <?php if (empty($emails)): ?>
                <div class="text-center" style="padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem; color: var(--text-muted);">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3>No hay emails generados</h3>
                    <p>Los emails aparecerán aquí cuando se envíen consultas o se realicen compras.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Volver al Inicio
                    </a>
                </div>
            <?php else: ?>
                <div class="emails-grid">
                    <?php foreach ($emails as $email): ?>
                    <div class="email-card">
                        <div class="email-header">
                            <div class="email-type">
                                <?php if (strpos($email['archivo'], 'consulta_') === 0): ?>
                                    <i class="fas fa-question-circle" style="color: var(--primary-color);"></i>
                                    <span>Consulta</span>
                                <?php else: ?>
                                    <i class="fas fa-shopping-cart" style="color: var(--success-color);"></i>
                                    <span>Compra</span>
                                <?php endif; ?>
                            </div>
                            <div class="email-date">
                                <i class="fas fa-clock"></i>
                                <?= $email['fecha'] ?>
                            </div>
                        </div>
                        <div class="email-info">
                            <h4><?= htmlspecialchars($email['archivo']) ?></h4>
                            <p>Tamaño: <?= number_format($email['tamaño'] / 1024, 1) ?> KB</p>
                        </div>
                        <div class="email-actions">
                            <a href="emails_simulados/<?= htmlspecialchars($email['archivo']) ?>" 
                               target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Ver Email
                            </a>
                            <button onclick="eliminarEmail('<?= htmlspecialchars($email['archivo']) ?>')" 
                                    class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center" style="margin-top: 2rem;">
                    <button onclick="limpiarTodosEmails()" class="btn btn-warning">
                        <i class="fas fa-broom"></i> Limpiar Todos los Emails
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function eliminarEmail(archivo) {
            if (confirm('¿Estás seguro de que quieres eliminar este email?')) {
                // En un entorno real, harías una petición AJAX para eliminar
                alert('Funcionalidad de eliminación no implementada en esta demo');
            }
        }
        
        function limpiarTodosEmails() {
            if (confirm('¿Estás seguro de que quieres eliminar TODOS los emails generados?')) {
                // En un entorno real, harías una petición AJAX para limpiar
                alert('Funcionalidad de limpieza no implementada en esta demo');
            }
        }
    </script>
    
    <style>
        .emails-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .email-card {
            background: var(--dark-surface);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .email-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        
        .email-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .email-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .email-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .email-info h4 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 1rem;
            word-break: break-all;
        }
        
        .email-info p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .email-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .emails-grid {
                grid-template-columns: 1fr;
            }
            
            .email-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .email-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
