<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    $dest = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario') ? 'pages/mis_citas.php' : 'login.php';
    header("Location: $dest");
    exit();
}

require_once 'includes/header.php';

// Lógica de eliminación
if (isset($_GET['eliminar'])) {
    $id_del = (int)$_GET['eliminar'];
    mysqli_query($con, "DELETE FROM cita WHERE id_cita = $id_del");
    header("Location: dashboard.php");
    exit();
}

// Consulta para el resumen
$query_resumen = "SELECT c.*, m.nombre as mascota_nombre, u.nombre as vet_nombre 
                  FROM cita c 
                  JOIN mascota m ON c.id_mascota = m.id_mascota 
                  LEFT JOIN usuario u ON c.id_veterinario = u.id_veterinario 
                  ORDER BY c.fecha_hora ASC LIMIT 5";

$resumen_citas = mysqli_query($con, $query_resumen);

// Guardamos los datos en un array para poder usarlos en la tabla y luego en los modales
$citas_data = [];
while($fila = mysqli_fetch_assoc($resumen_citas)) {
    $citas_data[] = $fila;
}
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Panel Administrativo</h2>
    
    <!-- Tarjetas de Acceso Rápido -->
    <div class="row g-4 text-center mb-5">
        <div class="col-md-3">
            <a href="pages/clientes.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-success text-white p-4">
                    <i class="fas fa-users fa-3x mb-2"></i>
                    <h5 class="fw-bold">Clientes</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="pages/mascotas.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-primary text-white p-4">
                    <i class="fas fa-paw fa-3x mb-2"></i>
                    <h5 class="fw-bold">Mascotas</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="pages/citas.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-warning text-dark p-4">
                    <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                    <h5 class="fw-bold">Citas Globales</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="pages/veterinarios.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-info text-white p-4">
                    <i class="fas fa-user-md fa-3x mb-2"></i>
                    <h5 class="fw-bold">Veterinarios</h5>
                </div>
            </a>
        </div>
    </div>

    <!-- Tabla de Próximas Actividades -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-muted"><i class="fas fa-clock"></i> Próximas Actividades</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mascota</th>
                        <th>Veterinario</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($citas_data) > 0): ?>
                        <?php foreach($citas_data as $rc): ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= htmlspecialchars($rc['mascota_nombre']) ?></td>
                            <td><?= htmlspecialchars($rc['vet_nombre'] ?? 'Sin asignar') ?></td>
                            <td><span class="badge rounded-pill bg-warning text-dark"><?= $rc['estado'] ?></span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#verCita<?= $rc['id_cita'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="pages/citas.php?editar=<?= $rc['id_cita'] ?>" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="dashboard.php?eliminar=<?= $rc['id_cita'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center p-4">No hay citas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECCIÓN DE MODALES (FUERA DE LA TABLA PARA EVITAR EL TEMBLOR) -->
<?php foreach($citas_data as $rc): ?>
    <!-- IMPORTANTE: SE QUITÓ EL ATRIBUTO tabindex="-1" -->
    <div class="modal fade" id="verCita<?= $rc['id_cita'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detalles de Cita: <?= htmlspecialchars($rc['mascota_nombre']) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold mb-1">Motivo / Síntomas detectados:</p>
                    <div class="p-3 bg-light rounded border mb-3">
                        <?= !empty($rc['motivo']) ? nl2br(htmlspecialchars($rc['motivo'])) : '<i>Sin síntomas registrados.</i>' ?>
                    </div>
                    
                    <p class="fw-bold mb-1">Diagnóstico Médico:</p>
                    <div class="p-3 bg-white rounded border mb-3">
                        <?= !empty($rc['diagnostico']) ? nl2br(htmlspecialchars($rc['diagnostico'])) : '<i>Pendiente por el médico.</i>' ?>
                    </div>

                    <div class="text-end border-top pt-2">
                        <small class="text-muted">
                            <strong>Fecha programada:</strong> 
                            <?= ($rc['fecha_hora'] != '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($rc['fecha_hora'])) : 'No definida' ?>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>