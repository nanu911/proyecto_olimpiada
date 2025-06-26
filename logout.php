<?php
require_once 'config/database.php';

// Destruir sesión
session_destroy();

// Redirigir al inicio
redirectTo('index.php');
?>
