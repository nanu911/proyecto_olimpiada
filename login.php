<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, nombre, email, password, tipo_usuario FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_email'] = $user['email'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
            
            redirectTo('index.php');
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Quantum Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="form-container animate__animated animate__fadeInUp">
        <h2><i class="fas fa-globe-americas"></i> Iniciar Sesión en Quantum Tour</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="tu@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Tu contraseña" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Ingresar</span>
                <span class="loading" style="display: none;"></span>
            </button>
        </form>
        
        <div class="text-center">
            <p>¿No tienes cuenta? <a href="register.php" class="text-primary">Regístrate aquí</a></p>
            <p><a href="index.php" class="text-secondary"><i class="fas fa-arrow-left"></i> Volver al inicio</a></p>
        </div>
        
        <!-- <div class="mt-4 p-3" style="background: var(--dark-card); border-radius: 0.5rem; font-size: 0.875rem;">
            <strong><i class="fas fa-info-circle"></i> Usuarios de prueba:</strong><br>
            <strong>Admin:</strong> admin@turismo.com / password<br>
            <strong>Vendedor:</strong> vendedor@turismo.com / password<br>
            <strong>Cliente:</strong> cliente@email.com / password
        </div> -->
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function() {
                $('.btn-text').hide();
                $('.loading').show();
            });
        });
    </script>
</body>
</html>
