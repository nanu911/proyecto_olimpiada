<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_POST) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor complete todos los campos obligatorios';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Verificar si el email ya existe
        $query = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Este email ya está registrado';
        } else {
            // Crear usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO usuarios (nombre, email, telefono, password, tipo_usuario) VALUES (?, ?, ?, ?, 'cliente')";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nombre, $email, $telefono, $hashed_password])) {
                $success = 'Registro exitoso. Ya puedes iniciar sesión.';
            } else {
                $error = 'Error al crear la cuenta. Intenta nuevamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Quantum Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="form-container animate__animated animate__fadeInUp">
        <h2><i class="fas fa-globe-americas"></i> Crear Cuenta en Quantum Tour</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success animate__animated animate__bounceIn">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                <br><a href="login.php" class="text-success"><strong>Iniciar sesión ahora</strong></a>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="nombre"><i class="fas fa-user"></i> Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       placeholder="Tu nombre completo" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="tu@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                <input type="tel" id="telefono" name="telefono" class="form-control" 
                       placeholder="+54 11 1234-5678" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña *</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Mínimo 6 caracteres" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirmar Contraseña *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Repite tu contraseña" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <span class="btn-text"><i class="fas fa-user-plus"></i> Crear Cuenta</span>
                <span class="loading" style="display: none;"></span>
            </button>
        </form>
        
        <div class="text-center">
            <p>¿Ya tienes cuenta? <a href="login.php" class="text-primary">Inicia sesión aquí</a></p>
            <p><a href="index.php" class="text-secondary"><i class="fas fa-arrow-left"></i> Volver al inicio</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function() {
                $('.btn-text').hide();
                $('.loading').show();
            });
            
            // Validación en tiempo real
            $('#confirm_password').on('keyup', function() {
                const password = $('#password').val();
                const confirmPassword = $(this).val();
                
                if (confirmPassword && password !== confirmPassword) {
                    $(this).css('border-color', 'var(--danger-color)');
                } else {
                    $(this).css('border-color', 'var(--border-color)');
                }
            });
        });
    </script>
</body>
</html>
