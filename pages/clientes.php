<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. IMPORTAR CLASES
require_once '../clases/DB.php';
require_once '../clases/Cliente.php';
require_once '../includes/TokenAntiCSRF.php';

// 2. INICIALIZAR
$database = new DB();
$db = $database->conectar();
$clienteObj = new Cliente($db);

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    $folder = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario') ? 'mis_citas.php' : '../login.php';
    header("Location: $folder");
    exit();
}

require '../includes/header.php'; 

$mensaje = '';
$error = '';

// 3. PROCESAR ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    if (isset($_POST['crear'])) {
        if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['telefono']) || empty($_POST['correo'])) {
            $error = "Nombre, Apellido, Teléfono y Correo son obligatorios.";
        } elseif (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
            $error = "Correo inválido.";
        } else {
            if($clienteObj->crear($_POST)) {
                $mensaje = "✅ Cliente guardado correctamente";
            }
        }
    }

    if (isset($_POST['actualizar'])) {
        if($clienteObj->actualizar($_POST)) {
            $mensaje = "✅ Cliente actualizado correctamente";
        }
    }
}

// 4. PROCESAR ACCIONES (GET)
if (isset($_GET['eliminar'])) {
    if($clienteObj->eliminar((int)$_GET['eliminar'])) {
        $mensaje = "Cliente eliminado";
    }
}

$editData = null;
if (isset($_GET['editar'])) {
    $editData = $clienteObj->obtenerPorId((int)$_GET['editar']);
}

$listaClientes = $clienteObj->leerTodos();
?>

<h2><i class="fas fa-users"></i> Gestión de Clientes</h2>

<?php if($mensaje) echo "<div class='alert alert-success'>$mensaje</div>"; ?>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5><?= $editData ? 'Editar Cliente' : 'Nuevo Cliente' ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

            <?php if ($editData): ?>
                <input type="hidden" name="id_cliente" value="<?= $editData['id_cliente'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($editData['nombre'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Apellido <span class="text-danger">*</span></label>
                    <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($editData['apellido'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Teléfono <span class="text-danger">*</span></label>
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($editData['telefono'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Correo <span class="text-danger">*</span></label>
                    <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($editData['correo'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Dirección</label>
                    <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($editData['direccion'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" name="<?= $editData ? 'actualizar' : 'crear' ?>" class="btn btn-success mt-3">
                <?= $editData ? 'Actualizar' : 'Guardar' ?> Cliente
            </button>
            <?php if ($editData): ?>
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
        <?php while ($row = $listaClientes->fetch(PDO::FETCH_ASSOC)): ?>
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