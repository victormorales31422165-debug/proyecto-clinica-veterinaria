<?php
$host = 'localhost';
$db   = 'clinica_colibri';
$user = 'root';
$pass = '';                  

$con = mysqli_connect($host, $user, $pass, $db);

if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8mb4");
?>