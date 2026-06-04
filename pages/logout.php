<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
session_unset();
session_destroy();

// Redirigimos al login que está en la misma carpeta
header("Location: login.php");
exit;
?>