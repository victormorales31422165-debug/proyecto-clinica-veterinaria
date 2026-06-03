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
    $especie = trim($_POST['especie']);
    $raza = trim($_POST['raza']);
    $edad = (int)$_POST['edad'];
    $id_cliente = (int)$_POST['id_cliente'];

    if (empty($nombre) || empty($especie) || $edad <= 0 || $id_cliente <= 0) {
        $error = "Todos los campos obligatorios deben ser llenados.";
    } else {
        $nombre = mysqli_real_escape_string($con, $nombre);
        $especie = mysqli_real_escape_string($con, $especie);
        $raza = mysqli_real_escape_string($con, $raza);
        mysqli_query($con, "INSERT INTO mascota (nombre, especie, raza, edad, id_cliente) 
                            VALUES ('$nombre', '$especie', '$raza', $edad, $id_cliente)");
        $mensaje = "✅ Mascota guardada correctamente";
    }
}

if (isset($_POST['actualizar'])) {
    // 3. VALIDACIÓN DEL TOKEN CSRF PARA ACTUALIZAR
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $id = (int)$_POST['id_mascota'];
    $nombre = mysqli_real_escape_string($con, trim($_POST['nombre']));
    $especie = mysqli_real_escape_string($con, trim($_POST['especie']));
    $raza = mysqli_real_escape_string($con, trim($_POST['raza']));
    $edad = (int)$_POST['edad'];
    $id_cliente = (int)$_POST['id_cliente'];

    mysqli_query($con, "UPDATE mascota SET nombre='$nombre', especie='$especie', raza='$raza', 
                        edad=$edad, id_cliente=$id_cliente WHERE id_mascota=$id");
    $mensaje = "✅ Mascota actualizada correctamente";
}

if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    mysqli_query($con, "DELETE FROM mascota WHERE id_mascota = $id");
    $mensaje = "Mascota eliminada";
}

$editRow = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $editRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM mascota WHERE id_mascota=$id"));
}

$clientes = mysqli_query($con, "SELECT * FROM cliente ORDER BY apellido");
$mascotas = mysqli_query($con, "SELECT m.*, c.nombre as cliente_nom, c.apellido 
                                FROM mascota m JOIN cliente c ON m.id_cliente = c.id_cliente");
?>

<h2><i class="fas fa-paw"></i> Gestión de Mascotas</h2>

<?php if($mensaje) echo "<div class='alert alert-success'>$mensaje</div>"; ?>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5><?= $editRow ? 'Editar Mascota' : 'Nueva Mascota' ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <!-- 4. CAMPO OCULTO PARA EL TOKEN CSRF -->
            <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

            <?php if ($editRow): ?>
                <input type="hidden" name="id_mascota" value="<?= $editRow['id_mascota'] ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Nombre -->
                <div class="col-md-3">
                    <label>Nombre Mascota <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($editRow['nombre'] ?? '') ?>" required>
                </div>

                <div class="col-md-3">
                    <label>Especie <span class="text-danger">*</span></label>
                    <select name="especie" class="form-select" required>
                        <option value="" disabled <?= !isset($editRow['especie']) ? 'selected' : '' ?>>Seleccione una especie...</option>
                        <option value="Perro" <?= (($editRow['especie'] ?? '') == 'Perro' ? 'selected' : '') ?>>Perro</option>
                        <option value="Gato" <?= (($editRow['especie'] ?? '') == 'Gato' ? 'selected' : '') ?>>Gato</option>
                        <option value="Ave" <?= (($editRow['especie'] ?? '') == 'Ave' ? 'selected' : '') ?>>Ave</option>
                        <option value="Roedor" <?= (($editRow['especie'] ?? '') == 'Roedor' ? 'selected' : '') ?>>Roedor</option>
                        <option value="Reptil" <?= (($editRow['especie'] ?? '') == 'Reptil' ? 'selected' : '') ?>>Reptil</option>
                        <option value="Otro" <?= (($editRow['especie'] ?? '') == 'Otro' ? 'selected' : '') ?>>Otro</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Raza</label>
                    <input type="text" name="raza" class="form-control" value="<?= htmlspecialchars($editRow['raza'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label>Edad (años) <span class="text-danger">*</span></label>
                    <input type="number" name="edad" class="form-control" value="<?= $editRow['edad'] ?? '' ?>" required>
                </div>
            </div>

            <div class="mt-3">
                <label>Dueño <span class="text-danger">*</span></label>
                <select name="id_cliente" id="id_cliente" class="form-select" required>
                    <option value=""></option> 
                    <?php while ($c = mysqli_fetch_assoc($clientes)): ?>
                    <option value="<?= $c['id_cliente'] ?>" <?= (($editRow['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="<?= $editRow ? 'actualizar' : 'crear' ?>" class="btn btn-success mt-3">
                <?= $editRow ? 'Actualizar Mascota' : 'Guardar Mascota' ?>
            </button>
            <?php if ($editRow): ?>
                <a href="mascotas.php" class="btn btn-secondary mt-3">Cancelar</a>
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
            <th>Nombre</th>
            <th>Especie</th>
            <th>Raza</th>
            <th>Edad</th>
            <th>Dueño</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($m = mysqli_fetch_assoc($mascotas)): ?>
        <tr>
            <td><?= htmlspecialchars($m['nombre']) ?></td>
            <td><?= htmlspecialchars($m['especie']) ?></td>
            <td><?= htmlspecialchars($m['raza']) ?></td>
            <td><?= $m['edad'] ?> años</td>
            <td><?= htmlspecialchars($m['cliente_nom'].' '.$m['apellido']) ?></td>
            <td>
                <a href="?editar=<?= $m['id_mascota'] ?>" class="btn btn-warning btn-sm">Editar</a>
                <a href="?eliminar=<?= $m['id_mascota'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#id_cliente').select2({
        placeholder: "Escribe el nombre del dueño para buscar...",
        allowClear: true,
        width: '100%'
    });
});
</script>

<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        padding: 5px !important;
        border: 1px solid #ced4da !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 37px !important;
    }
</style>

<?php require '../includes/footer.php'; ?>