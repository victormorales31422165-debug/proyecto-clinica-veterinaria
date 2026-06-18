<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


if (isset($_SESSION['user'])) {
    
    
    if ($_SESSION['rol'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: mis_citas.php");
    }
    exit;
}


header("Location: login.php");
exit;
?>