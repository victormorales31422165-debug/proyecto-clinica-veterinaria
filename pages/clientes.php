<?php 
require '../includes/header.php'; 
// 1. Importar la clase de seguridad
require_once '../includes/TokenAntiCSRF.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Si no es admin, lo mandamos a sus citas (si es vet) o al login
    $folder = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario') ? 'mis_citas.php' : '../login.php';
    header("Location: $folder");
    exit();
}

$mensaje = '';
$error = '';

if (isset($_POST['crear'])) {
    // 2. VALIDACIÓN DEL TOKEN CSRF PARA CREAR
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);

    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($correo)) {
        $error = "Nombre, Apellido, Teléfono y Correo son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo inválido.";
    } else {
        $nombre = mysqli_real_escape_string($con, $nombre);
        $apellido = mysqli_real_escape_string($con, $apellido);
        $direccion = mysqli_real_escape_string($con, $direccion);
        $telefono = mysqli_real_escape_string($con, $telefono);
        $correo = mysqli_real_escape_string($con, $correo);

        mysqli_query($con, "INSERT INTO cliente VALUES (null, '$nombre', '$apellido', '$direccion', '$telefono', '$correo')");
        $mensaje = "✅ Cliente guardado correctamente";
    }
}

// Actualizar
if (isset($_POST['actualizar'])) {
    // 3. VALIDACIÓN DEL TOKEN CSRF PARA ACTUALIZAR
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $id = (int)$_POST['id_cliente'];
    $nombre = mysqli_real_escape_string($con, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($con, trim($_POST['apellido']));
    $direccion = mysqli_real_escape_string($con, trim($_POST['direccion']));
    $telefono = mysqli_real_escape_string($con, trim($_POST['telefono']));
    $correo = mysqli_real_escape_string($con, trim($_POST['correo']));

    mysqli_query($con, "UPDATE cliente SET nombre='$nombre', apellido='$apellido', direccion='$direccion', 
                        telefono='$telefono', correo='$correo' WHERE id_cliente=$id");
    $mensaje = "✅ Cliente actualizado correctamente";
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    mysqli_query($con, "DELETE FROM cliente WHERE id_cliente = $id");
    $mensaje = "Cliente eliminado";
}

$result = mysqli_query($con, "SELECT * FROM cliente ORDER BY apellido");
?>

<h2><i class="fas fa-users"></i> Gestión de Clientes</h2>

<?php if($mensaje) echo "<div class='alert alert-success'>$mensaje</div>"; ?>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5><?= isset($_GET['editar']) ? 'Editar Cliente' : 'Nuevo Cliente' ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <!-- 4. CAMPO OCULTO PARA EL TOKEN CSRF -->
            <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

            <?php 
            if (isset($_GET['editar'])) {
                $id = (int)$_GET['editar'];
                $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM cliente WHERE id_cliente=$id"));
            ?>
                <input type="hidden" name="id_cliente" value="<?= $row['id_cliente'] ?>">
            <?php } ?>

            <div class="row">
                <div class="col-md-4">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($row['nombre'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Apellido <span class="text-danger">*</span></label>
                    <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($row['apellido'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Teléfono <span class="text-danger">*</span></label>
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($row['telefono'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Correo <span class="text-danger">*</span></label>
                    <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($row['correo'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Dirección</label>
                    <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($row['direccion'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" name="<?= isset($_GET['editar']) ? 'actualizar' : 'crear' ?>" class="btn btn-success mt-3">
                <?= isset($_GET['editar']) ? 'Actualizar' : 'Guardar' ?> Cliente
            </button>
            <?php if (isset($_GET['editar'])): ?>
                <a href="clientes.php" class="btn btn-secondary mt-3">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Buscar en esta tabla...">
        </div>
    </div>
</div>

<table class="table table-hover">
    <thead class="table-success">
        <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['id_cliente'] ?></td>
            <td><?= htmlspecialchars($row['nombre'].' '.$row['apellido']) ?></td>
            <td><?= htmlspecialchars($row['telefono']) ?></td>
            <td><?= htmlspecialchars($row['correo']) ?></td>
            <td>
                <a href="?editar=<?= $row['id_cliente'] ?>" class="btn btn-warning btn-sm">Editar</a>
                <a href="?eliminar=<?= $row['id_cliente'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require '../includes/footer.php'; ?>