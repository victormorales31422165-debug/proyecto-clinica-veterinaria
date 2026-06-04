<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// 1. CAMBIO A POO: Cargamos la nueva clase de base de datos
require_once __DIR__ . '/../clases/DB.php';

// Opcional: Si necesitas que $db esté disponible en el nav.php para 
// mostrar datos en tiempo real, puedes instanciarlo aquí:
$database = new DB();
$db = $database->conectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clínica Veterinaria El Colibrí</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome (Limpiado el duplicado que tenías) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Estilos Personalizados: Mantenemos tu ruta exacta para no cambiar colores -->
    <!-- Nota: Usamos una ruta relativa para mayor compatibilidad -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
       <body class="d-flex flex-column min-vh-90">
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6; /* Color de fondo suave usualmente usado en salud */
        }
    </style>
</head>
<body>

    <?php 
    // Incluimos la navegación (asegúrate de que nav.php también esté en /includes/)
    require_once __DIR__ . '/nav.php'; 
    ?>

    <!-- Apertura del contenedor principal -->
    <div class="contenedor mt-4">