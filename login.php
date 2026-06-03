<?php
// 1. Iniciamos sesión de forma segura
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config/db.php';

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Consultamos en la tabla única 'usuario' que fusionamos anteriormente
    $stmt = mysqli_prepare($con, "SELECT * FROM usuario WHERE usuario = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // 2. Validación de credenciales
    if ($user && $password === $user['password']) {
        
        // Guardamos los datos del usuario en la sesión
        $_SESSION['user'] = $user;
        $_SESSION['rol'] = $user['rol']; 

        // 3. Redirección inteligente según el ROL guardado en la base de datos
        if ($user['rol'] === 'admin') {
            // El administrador va al panel de control
            header("Location: dashboard.php");
        } else {
            // Cualquier otro rol (veterinario) va a sus citas
            header("Location: pages/mis_citas.php");
        }
        exit;

    } else {
        $error = "Acceso denegado: Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Colibrí - Iniciar Sesión</title>
    <!-- CSS Externos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex align-items-center min-vh-100 body-login">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="card shadow p-4 login-card">
                    <div class="text-center mb-4">
                        <h4 class="text-muted small">Clínica Veterinaria</h4>
                        <h1 class="logo h2" style="color: #198754; font-weight: bold;">El <span style="color: #ff7f50;">Colibrí</span></h1>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger py-2 small text-center">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Usuario</label>
                            <input type="text" name="username" class="form-control" placeholder="Nombre de usuario" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                            Iniciar Sesión
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted small">© 2026 Clínica El Colibrí</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>