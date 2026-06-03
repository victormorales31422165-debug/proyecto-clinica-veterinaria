<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';
// 1. Importar la clase de seguridad
require_once '../includes/TokenAntiCSRF.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: ../login.php"); exit();
}

require_once '../includes/header.php';

$id_vet = $_SESSION['user']['id_veterinario'];
$mensaje = '';

// 2. Lógica para guardar el diagnóstico con validación CSRF
if (isset($_POST['guardar_diag'])) {
    // Validación del Token
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $id_cita = (int)$_POST['id_cita'];
    $diagnostico = mysqli_real_escape_string($con, $_POST['diagnostico']);
    mysqli_query($con, "UPDATE cita SET diagnostico = '$diagnostico', estado = 'Completada' WHERE id_cita = $id_cita");
    $mensaje = "✅ Diagnóstico guardado y cita finalizada correctamente.";
}

// Consultar citas
$query = "SELECT c.*, m.nombre as mascota, cl.nombre as dueño, cl.apellido 
          FROM cita c 
          JOIN mascota m ON c.id_mascota = m.id_mascota 
          JOIN cliente cl ON m.id_cliente = cl.id_cliente
          WHERE c.id_veterinario = $id_vet 
          ORDER BY c.fecha_hora DESC";
$mis_citas = mysqli_query($con, $query);

$data_citas = [];
while($fila = mysqli_fetch_assoc($mis_citas)) { $data_citas[] = $fila; }
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-check text-success"></i> Mis Citas Asignadas</h2>
        <span class="badge bg-primary p-2">Médico: <?= htmlspecialchars($_SESSION['user']['nombre']) ?></span>
    </div>
    
    <?php if($mensaje) echo "<div class='alert alert-success'>$mensaje</div>"; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th class="ps-3">Fecha y Hora</th>
                        <th>Mascota</th>
                        <th>Dueño</th>
                        <th>Motivo / Síntomas</th>
                        <th>Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($data_citas) > 0): ?>
                        <?php foreach($data_citas as $c): ?>
                        <tr>
                            <td class="ps-3"><?= date('d/m/Y (H:i)', strtotime($c['fecha_hora'])) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($c['mascota']) ?></td>
                            <td><?= htmlspecialchars($c['dueño'] . ' ' . $c['apellido']) ?></td>
                            <td><small><?= htmlspecialchars($c['motivo'] ?? 'Sin especificar') ?></small></td>
                            <td><span class="badge rounded-pill <?= $c['estado'] == 'pendiente' ? 'bg-warning text-dark' : 'bg-success' ?>"><?= ucfirst($c['estado']) ?></span></td>
                            <td class="text-center">
                            <!-- Enlace a la página detallada -->
                            <a href="atender_cita.php?id=<?= $c['id_cita'] ?>" class="btn btn-primary btn-sm px-3 shadow-sm">
                                <i class="fas fa-stethoscope"></i> Atender
                            </a>
                        </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center p-5 text-muted">No tienes citas programadas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php require_once '../includes/footer.php'; ?>