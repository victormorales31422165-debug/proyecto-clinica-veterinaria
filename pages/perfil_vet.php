<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. IMPORTAR CLASES Y LIBRERÍAS
require_once '../clases/DB.php';
require_once '../clases/Usuario.php';
require_once '../includes/TokenAntiCSRF.php';

// 2. SEGURIDAD Y OBJETOS
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: ../login.php"); exit();
}

$database = new DB();
$db = $database->conectar();
$usuarioObj = new Usuario($db);

$id_vet = $_SESSION['user']['id_veterinario'];
$mensaje = '';

// 3. PROCESAR ACTUALIZACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido o sesión expirada.");
    }

    // Recopilamos los datos del POST
    $datos_actualizar = [
        'nombre' => trim($_POST['nombre']),
        'correo' => trim($_POST['correo']),
        'especialidad' => trim($_POST['especialidad']),
        'password' => trim($_POST['password']), // Puede estar vacío
    ];

    // Llamamos al método de la clase para actualizar
    if ($usuarioObj->actualizarPerfil($id_vet, $datos_actualizar)) {
        $mensaje = "✅ Datos actualizados correctamente.";
        
        // Actualizamos la sesión si se cambió el nombre
        $_SESSION['user']['nombre'] = $datos_actualizar['nombre'];
    } else {
        $mensaje = "Ocurrió un error al actualizar los datos.";
    }
}

// 4. OBTENER DATOS ACTUALES DEL VETERINARIO
// Usamos el método de la clase para obtener los datos
$datos_vet = $usuarioObj->obtenerPerfil($id_vet); 
// Esto es crucial para que el value de los inputs no se pierda.
// Si tu sesión ya tiene todos los datos necesarios, puedes seguir usando $_SESSION['user']
// para la mayoría de los campos y solo obtener los que cambian o no están en sesión.
// Para este caso, vamos a usar el resultado de la consulta para asegurar que sea el más actual:
$datos_actuales = $datos_vet ?? $_SESSION['user']; // Usamos lo obtenido o lo de sesión si falla la consulta

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
                        <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos_actuales['nombre']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Especialidad</label>
                            <input type="text" name="especialidad" class="form-control" value="<?= htmlspecialchars($datos_actuales['especialidad']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mi Correo</label>
                            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($datos_actuales['correo']) ?>" required>
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