<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';
// 1. Importar la clase de seguridad
require_once '../includes/TokenAntiCSRF.php';

// Seguridad: Solo veterinarios
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: ../login.php"); exit();
}

$id_vet = $_SESSION['user']['id_veterinario'];
$mensaje = '';

// LÓGICA PARA ACTUALIZAR DATOS Y CONTRASEÑA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. VALIDACIÓN DEL TOKEN CSRF
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido o sesión expirada.");
    }

    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $correo = mysqli_real_escape_string($con, $_POST['correo']);
    $especialidad = mysqli_real_escape_string($con, $_POST['especialidad']);
    $pass_nueva = trim($_POST['password']);

    // Si el médico escribe una contraseña, la actualizamos. Si no, se queda la anterior.
    $sql_pass = !empty($pass_nueva) ? ", password = '$pass_nueva'" : "";

    $query = "UPDATE usuario SET nombre='$nombre', correo='$correo', especialidad='$especialidad' $sql_pass 
              WHERE id_veterinario = $id_vet";

    if (mysqli_query($con, $query)) {
        $mensaje = "✅ Datos actualizados correctamente.";
        // Actualizamos la sesión para que el nombre cambie arriba en el nav de inmediato
        $_SESSION['user']['nombre'] = $nombre;
    }
}

// Consultar datos actuales del médico
$res = mysqli_query($con, "SELECT * FROM usuario WHERE id_veterinario = $id_vet");
$datos = mysqli_fetch_assoc($res);

require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user-cog"></i> Configuración de mi Perfil</h5>
                </div>
                <div class="card-body p-4">
                    <?php if($mensaje) echo "<div class='alert alert-success'>$mensaje</div>"; ?>

                    <form method="POST">
                        <!-- 3. CAMPO OCULTO PARA EL TOKEN CSRF -->
                        <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Especialidad</label>
                            <input type="text" name="especialidad" class="form-control" value="<?= htmlspecialchars($datos['especialidad']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mi Correo</label>
                            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($datos['correo']) ?>" required>
                        </div>
                        <hr>
                        <div class="mb-3 bg-light p-3 rounded">
                            <label class="form-label fw-bold text-danger">Cambiar Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                            <small class="text-muted">Si el administrador te registró recién, cambia aquí tu clave "veterinaria".</small>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="mis_citas.php" class="btn btn-secondary px-4">Volver</a>
                            <button type="submit" class="btn btn-success px-4 fw-bold">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>