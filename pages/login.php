<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


require_once '../clases/DB.php';
require_once '../clases/Usuario.php';
require_once '../includes/TokenAntiCSRF.php';

$error = '';


if ($_POST) {
    
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $database = new DB();
    $db = $database->conectar();
    $usuarioObj = new Usuario($db);

    $user_input = trim($_POST['username']);
    $pass_input = trim($_POST['password']);

    
    $user_data = $usuarioObj->login($user_input, $pass_input);

    if ($user_data) {
        $_SESSION['user'] = $user_data;
        $_SESSION['rol'] = $user_data['rol'];

   
        if ($user_data['rol'] === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: mis_citas.php");
        }
        exit();
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

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    
    <link rel="stylesheet" href="../assets/css/style.css"> 
</head>
<body class="d-flex align-items-center min-vh-100 body-login">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow p-4 login-card">
                    
                    <div class="text-center mb-4">
                        <h4 class="text-muted small">Clínica Veterinaria</h4>
                        <h1 class="logo h2" style="color: #198754; font-weight: bold;">
                            El <span style="color: #ff7f50;">Colibrí</span>
                        </h1>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger py-2 small text-center">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                      
                        <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Usuario</label>
                            <input type="text" name="username" class="form-control" placeholder="Nombre de usuario" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="........" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                            Iniciar Sesión
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted small">© 2024 Clínica El Colibrí</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>