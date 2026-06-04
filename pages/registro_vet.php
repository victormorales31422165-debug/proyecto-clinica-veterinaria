<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. IMPORTAMOS LAS CLASES
require_once '../clases/DB.php';
require_once '../clases/Usuario.php';
require_once '../includes/TokenAntiCSRF.php';

$error = "";

// 2. PROCESAR EL POST
if ($_POST) {
    // Validar Token CSRF
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $database = new DB();
    $db = $database->conectar();
    $usuarioObj = new Usuario($db);

    $datos = [
        'nombre' => trim($_POST['nombre']),
        'especialidad' => trim($_POST['especialidad']),
        'telefono' => trim($_POST['telefono']),
        'usuario' => trim($_POST['usuario']),
        'password' => $_POST['password']
    ];

    // Verificar si el usuario existe
    if ($usuarioObj->existeUsuario($datos['usuario'])) {
        $error = "El nombre de usuario ya está en uso.";
    } else {
        // Intentar registrar
        if ($usuarioObj->registrarVeterinario($datos)) {
            header("Location: login.php?registro=exitoso");
            exit;
        } else {
            $error = "Error al registrar en la base de datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Veterinario - El Colibrí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ajuste de ruta para el CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<!-- Se mantiene el fondo y el diseño intacto -->
<body class="d-flex align-items-center min-vh-100" style="background: url('../assets/img/background.png'); background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg p-4" style="background: rgba(255, 255, 255, 0.95); border-radius: 20px; border: none;">
                    <div class="text-center mb-4">
                        <h4 class="text-muted">Únete al equipo médico</h4>
                        <h1 class="logo" style="color: #198754; font-weight: bold;">El <span style="color: #ff7f50;">Colibrí</span></h1>
                    </div>

                    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

                    <form method="POST">
                        <!-- Token CSRF -->
                        <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Dr. Juan Pérez" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control" placeholder="Ej: Cirugía" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" placeholder="Opcional">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre de Usuario</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Para iniciar sesión" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">Completar Registro</button>
                        
                        <div class="text-center mt-3">
                            <a href="login.php" class="text-muted text-decoration-none small">¿Ya tienes cuenta? Inicia sesión aquí</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>