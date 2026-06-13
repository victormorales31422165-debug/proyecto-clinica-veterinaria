<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. IMPORTAMOS LAS CLASES
require_once '../clases/DB.php';
require_once '../clases/Cita.php';
require_once '../includes/TokenAntiCSRF.php';

// 2. CONEXIÓN Y OBJETOS
$database = new DB();
$db = $database->conectar();
$citaObj = new Cita($db);

// Verificación de rol admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    $dest = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario') ? 'mis_citas.php' : '../login.php';
    header("Location: $dest");
    exit();
}

require_once '../includes/header.php';

// 3. LÓGICA PARA ELIMINAR (POO + CSRF)
if (isset($_GET['eliminar'])) {
    if (isset($_GET['token']) && TokenAntiCSRF::consumirToken($_GET['token'])) {
        $id_del = (int)$_GET['eliminar'];
        $citaObj->eliminar($id_del);
        header("Location: dashboard.php");
        exit();
    }
}

// 4. OBTENER DATOS (POO)
$resumen_citas = $citaObj->leerResumenDashboard();
$citas_data = $resumen_citas->fetchAll(PDO::FETCH_ASSOC);

$tokenEliminar = TokenAntiCSRF::generarToken();
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Panel Administrativo</h2>
    
    <!-- Cards de Navegación -->
    <div class="row g-4 text-center mb-5">
        <div class="col-md-3">
            <a href="clientes.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-success text-white p-4">
                    <i class="fas fa-users fa-3x mb-2"></i>
                    <h5 class="fw-bold">Clientes</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="mascotas.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-primary text-white p-4">
                    <i class="fas fa-paw fa-3x mb-2"></i>
                    <h5 class="fw-bold">Mascotas</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="citas.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-warning text-dark p-4">
                    <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                    <h5 class="fw-bold">Citas Globales</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="veterinarios.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm bg-info text-white p-4">
                    <i class="fas fa-user-md fa-3x mb-2"></i>
                    <h5 class="fw-bold">Veterinarios</h5>
                </div>
            </a>
        </div>
    </div>

    <!-- Tabla de Actividades -->
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
                            <td><span class="badge rounded-pill <?= ($rc['estado'] == 'completada' ? 'bg-success' : 'bg-warning text-dark') ?>"><?= htmlspecialchars($rc['estado']) ?></span></td>
                            <td class="text-center">
                                <!-- BOTÓN ACTUALIZADO: Usa el modal dinámico del footer -->
                                <button class="btn btn-sm btn-outline-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalMotivo" 
                                        data-mascota="<?= htmlspecialchars($rc['mascota_nombre']) ?>" 
                                        data-motivo="<?= htmlspecialchars($rc['motivo'] ?? 'Sin motivo registrado') ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <a href="citas.php?editar=<?= $rc['id_cita'] ?>" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="dashboard.php?eliminar=<?= $rc['id_cita'] ?>&token=<?= $tokenEliminar ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?')">
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

<?php 
// Eliminamos el bucle de modales que estaba aquí, 
// ya que ahora usamos el modal único en el footer.php
require_once '../includes/footer.php'; 
?>