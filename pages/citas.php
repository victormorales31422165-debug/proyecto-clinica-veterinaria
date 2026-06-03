<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../vendor/autoload.php';
require_once '../includes/TokenAntiCSRF.php';
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php"); exit();
}
require_once '../includes/header.php';

// --- LOGICA DE CORREO ---
function enviarAviso($con, $id_v, $fh, $m_nom) {
    $res = mysqli_query($con, "SELECT nombre, correo FROM usuario WHERE id_veterinario = $id_v");
    $v = mysqli_fetch_assoc($res);
    if ($v && !empty($v['correo'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8'; $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
            $mail->Username = 'clinicaveterinariaelcolibri1@gmail.com'; $mail->Password = 'zyfn atzg ygau jmrs';
            $mail->SMTPSecure = 'tls'; $mail->Port = 587;
            $mail->setFrom('clinica@elcolibri.com', 'El Colibrí');
            $mail->addAddress($v['correo']);
            $mail->isHTML(true);
            $mail->Subject = 'Nueva Cita Asignada';
            $mail->Body = "Cita para $m_nom programada el $fh";
            $mail->send();
        } catch (Exception $e) {}
    }
}

// --- PROCESAR FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) { die("CSRF Error"); }

    $fh = $_POST['fecha_hora']; // Formato: YYYY-MM-DDTHH:MM
    $mot = mysqli_real_escape_string($con, $_POST['motivo']);
    $id_m = (int)$_POST['id_mascota'];
    $id_v = !empty($_POST['id_veterinario']) ? (int)$_POST['id_veterinario'] : "NULL";

    if (isset($_POST['crear_cita'])) {
        mysqli_query($con, "INSERT INTO cita (fecha_hora, motivo, id_mascota, id_veterinario, estado) VALUES ('$fh', '$mot', $id_m, $id_v, 'pendiente')");
        if($id_v !== "NULL") {
            $m_res = mysqli_query($con, "SELECT nombre FROM mascota WHERE id_mascota = $id_m");
            $m_data = mysqli_fetch_assoc($m_res);
            enviarAviso($con, $id_v, $fh, $m_data['nombre']);
        }
    }
    if (isset($_POST['actualizar_cita'])) {
        $id_c = (int)$_POST['id_cita'];
        $est = $_POST['estado'];
        mysqli_query($con, "UPDATE cita SET fecha_hora='$fh', motivo='$mot', id_veterinario=$id_v, estado='$est' WHERE id_cita=$id_c");
    }
    header("Location: citas.php"); exit();
}

if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    mysqli_query($con, "DELETE FROM cita WHERE id_cita = $id");
}

$editRow = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $editRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM cita WHERE id_cita=$id"));
}

$mascotas = mysqli_query($con, "SELECT id_mascota, nombre FROM mascota ORDER BY nombre ASC");
$veterinarios = mysqli_query($con, "SELECT id_veterinario, nombre FROM usuario WHERE rol='veterinario' ORDER BY nombre ASC");
$citas = mysqli_query($con, "SELECT c.*, m.nombre as m_nom, u.nombre as v_nom FROM cita c JOIN mascota m ON c.id_mascota=m.id_mascota LEFT JOIN usuario u ON c.id_veterinario=u.id_veterinario ORDER BY c.fecha_hora DESC");
?>

<div class="container-fluid mt-3">
    <h2 class="mb-4">Gestión de Citas</h2>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold"><?= $editRow ? 'Editar Cita' : 'Programar Nueva Cita' ?></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">
                <?php if($editRow): ?><input type="hidden" name="id_cita" value="<?= $editRow['id_cita'] ?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha y Hora *</label>
                        <input type="datetime-local" name="fecha_hora" class="form-control" value="<?= $editRow['fecha_hora'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mascota *</label>
                        <select name="id_mascota" class="form-select">
                            <?php while($m = mysqli_fetch_assoc($mascotas)): ?>
                                <option value="<?= $m['id_mascota'] ?>" <?= (($editRow['id_mascota'] ?? '') == $m['id_mascota'] ? 'selected' : '') ?>><?= htmlspecialchars($m['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Veterinario</label>
                        <select name="id_veterinario" class="form-select">
                            <option value="">Sin asignar</option>
                            <?php mysqli_data_seek($veterinarios, 0); while($v = mysqli_fetch_assoc($veterinarios)): ?>
                                <option value="<?= $v['id_veterinario'] ?>" <?= (($editRow['id_veterinario'] ?? '') == $v['id_veterinario'] ? 'selected' : '') ?>><?= htmlspecialchars($v['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="form-control" rows="2"><?= htmlspecialchars($editRow['motivo'] ?? '') ?></textarea>
                    </div>
                    <?php if($editRow): ?>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="pendiente" <?= $editRow['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="completada" <?= $editRow['estado'] == 'completada' ? 'selected' : '' ?>>Completada</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" name="<?= $editRow ? 'actualizar_cita' : 'crear_cita' ?>" class="btn btn-primary mt-3 px-5 fw-bold">Guardar Cita</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success text-dark">
                    <tr><th class="ps-3">Fecha / Hora</th><th>Mascota</th><th>Veterinario</th><th>Estado</th><th class="text-center">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php while($c = mysqli_fetch_assoc($citas)): ?>
                    <tr>
                        <td class="ps-3">
                            <?= date('d/m/Y', strtotime($c['fecha_hora'])) ?> 
                            <small class="text-muted">(<?= date('H:i', strtotime($c['fecha_hora'])) ?>)</small>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($c['m_nom']) ?></td>
                        <td><?= htmlspecialchars($c['v_nom'] ?? 'Sin asignar') ?></td>
                        <td><span class="badge bg-warning text-dark"><?= $c['estado'] ?></span></td>
                        <td class="text-center">
                            <a href="?editar=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                            <a href="?eliminar=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?')">Borrar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>