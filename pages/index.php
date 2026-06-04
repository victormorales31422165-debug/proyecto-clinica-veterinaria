<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Si el usuario ya está logueado
if (isset($_SESSION['user'])) {
    
    // Redirección inteligente según el rol
    if ($_SESSION['rol'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: mis_citas.php");
    }
    exit;
}

// Si no está logueado, al login
header("Location: login.php");
exit;
?>