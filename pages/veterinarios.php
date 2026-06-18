<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


require_once '../clases/DB.php';
require_once '../clases/Usuario.php';
require_once '../includes/TokenAntiCSRF.php';


if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    $dest = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario') ? 'mis_citas.php' : '../login.php';
    header("Location: $dest");
    exit();
}

$database = new DB();
$db = $database->conectar();
$usuarioObj = new Usuario($db);

$mensaje = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    
    if (isset($_POST['crear'])) {
        if ($usuarioObj->existeUsuario($_POST['usuario'])) {
            $error = "El usuario '" . htmlspecialchars($_POST['usuario']) . "' ya existe.";
        } else {
            if ($usuarioObj->crearVeterinarioDesdeAdmin($_POST)) {
                $mensaje = "✅ Veterinario registrado. Contraseña asignada: <b>veterinaria</b>";
            }
        }
    }

    
    if (isset($_POST['actualizar'])) {
        $id = (int)$_POST['id_veterinario'];
        if ($usuarioObj->actualizarVeterinarioDesdeAdmin($id, $_POST)) {
            $mensaje = "✅ Información actualizada con éxito.";
        }
    }
}


if (isset($_GET['eliminar'])) {
    if ($usuarioObj->eliminarVeterinario((int)$_GET['eliminar'])) {
        $mensaje = "Registro eliminado del sistema.";
    }
}

$editRow = null;
if (isset($_GET['editar'])) {
    $editRow = $usuarioObj->obtenerPerfil((int)$_GET['editar']);
}


$resultadoVet = $usuarioObj->listarVeterinarios();
$listado = $resultadoVet->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-user-md text-success"></i> Gestión de Veterinarios (Admin)</h2>

    <?php if($mensaje) echo "<div class='alert alert-success alert-dismissible fade show shadow-sm'>$mensaje<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
    <?php if($error) echo "<div class='alert alert-danger shadow-sm'>$error</div>"; ?>

    
    <div class="card mb-5 shadow-sm border-0">
        <div class="card-header <?= $editRow ? 'bg-warning text-dark' : 'bg-success text-white' ?> fw-bold">
            <i class="fas <?= $editRow ? 'fa-edit' : 'fa-plus-circle' ?>"></i> 
            <?= $editRow ? 'Modificar Médico: ' . htmlspecialchars($editRow['nombre']) : 'Registrar Nuevo Veterinario' ?>
        </div>
        <div class="card-body bg-light">
            <form method="POST" class="row g-3">
                <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">
                
                <?php if($editRow): ?>
                    <input type="hidden" name="id_veterinario" value="<?= $editRow['id_veterinario'] ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Nombre Completo *</label>
                    <input type="text" name="nombre" class="form-control" value="<?= $editRow['nombre'] ?? '' ?>" placeholder="Ej: Dr. Fernando Pérez" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Especialidad *</label>
                    <input type="text" name="especialidad" class="form-control" value="<?= $editRow['especialidad'] ?? '' ?>" placeholder="Ej: Cardiología" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Correo Electrónico *</label>
                    <input type="email" name="correo" class="form-control" value="<?= $editRow['correo'] ?? '' ?>" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre de Usuario *</label>
                    <input type="text" name="usuario" class="form-control" value="<?= $editRow['usuario'] ?? '' ?>" placeholder="Usuario de acceso" required>
                </div>

                <div class="col-md-6">
                    <?php if($editRow): ?>
                        <label class="form-label fw-bold">Nueva Contraseña (Opcional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Dejar vacío para no cambiar">
                    <?php else: ?>
                        <label class="form-label fw-bold text-muted">Contraseña Inicial</label>
                        <input type="text" class="form-control bg-white" value="veterinaria" disabled>
                        <small class="text-muted">Se asignará automáticamente al crear.</small>
                    <?php endif; ?>
                </div>

                <div class="col-12 text-end mt-4">
                    <?php if($editRow): ?>
                        <a href="veterinarios.php" class="btn btn-secondary px-4">Cancelar Edición</a>
                        <button type="submit" name="actualizar" class="btn btn-warning px-4 fw-bold">Guardar Cambios</button>
                    <?php else: ?>
                        <button type="submit" name="crear" class="btn btn-success px-5 fw-bold shadow-sm">Registrar Veterinario</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted"><i class="fas fa-list"></i> Personal Médico Registrado</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Especialidad</th>
                        <th>Correo</th>
                        <th>Usuario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($listado) > 0): ?>
                        <?php foreach ($listado as $v): ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= htmlspecialchars($v['nombre']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($v['especialidad']) ?></span></td>
                            <td><small><?= htmlspecialchars($v['correo'] ?? 'Sin correo') ?></small></td>
                            <td><code><?= htmlspecialchars($v['usuario']) ?></code></td>
                            <td class="text-center">
                                <a href="?editar=<?= $v['id_veterinario'] ?>" class="btn btn-sm btn-outline-warning mx-1">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <a href="?eliminar=<?= $v['id_veterinario'] ?>" class="btn btn-sm btn-outline-danger mx-1" onclick="return confirm('¿Eliminar registro?')">
                                    <i class="fas fa-trash"></i> Borrar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-5 text-muted">No hay médicos registrados actualmente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>