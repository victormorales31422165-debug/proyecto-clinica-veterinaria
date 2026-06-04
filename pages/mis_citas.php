<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. IMPORTAR CLASES
require_once '../clases/DB.php';
require_once '../clases/Cita.php';
require_once '../includes/TokenAntiCSRF.php';

// 2. SEGURIDAD Y OBJETOS
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: ../login.php"); exit();
}

$database = new DB();
$db = $database->conectar();
$citaObj = new Cita($db);

$id_vet = $_SESSION['user']['id_veterinario'];
$mensaje = '';

// 3. PROCESAR DIAGNÓSTICO (POST)
if (isset($_POST['guardar_diag'])) {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $id_cita = (int)$_POST['id_cita'];
    $diagnostico = $_POST['diagnostico'];

    // Usamos el método que ya definimos en Cita.php anteriormente
    if($citaObj->agregarDiagnostico($id_cita, $diagnostico)) {
        $mensaje = "✅ Diagnóstico guardado y cita finalizada correctamente.";
    }
}

// 4. OBTENER CITAS ASIGNADAS
$resultado = $citaObj->leerPorVeterinario($id_vet);
// Convertimos a array para poder contar los registros como hacías antes
$data_citas = $resultado->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
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
                            <td>
                                <span class="badge rounded-pill <?= $c['estado'] == 'pendiente' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                    <?= ucfirst($c['estado']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <!-- Enlace a la página detallada (Se mantiene igual) -->
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