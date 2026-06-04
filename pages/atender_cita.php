<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Cargamos las clases necesarias
require_once '../clases/DB.php';
require_once '../clases/Cita.php';
require_once '../includes/TokenAntiCSRF.php';

// 2. Inicializamos conexión y objeto
$database = new DB();
$db = $database->conectar();
$citaObj = new Cita($db);

// Verificación de rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: ../login.php"); 
    exit();
}

$id_cita = (int)$_GET['id'];
$mensaje = '';
$guardado = false;

// 3. Lógica para finalizar la cita (POO)
if (isset($_POST['finalizar'])) {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido o expirado.");
    }

    $diag = $_POST['diagnostico'];
    
    // Usamos el método de la clase para actualizar
    if($citaObj->agregarDiagnostico($id_cita, $diag)) {
        $mensaje = "✅ Diagnóstico guardado exitosamente.";
        $guardado = true;
    }
}

// 4. Obtenemos los datos para mostrar en la vista
$cita = $citaObj->obtenerDetallesCompletos($id_cita);

// Si no se encuentra la cita, podríamos redirigir o mostrar error
if (!$cita) {
    die("Cita no encontrada.");
}

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-medical text-success"></i> Atención Médica Detallada</h2>
        <a href="mis_citas.php" class="btn btn-outline-secondary btn-sm">Volver a la lista</a>
    </div>

    <?php if($mensaje) echo "<div class='alert alert-success shadow'>$mensaje</div>"; ?>

    <div class="row">
        <!-- Columna de Datos del Paciente -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">Datos del Paciente</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Mascota:</strong> <?= htmlspecialchars($cita['mascota']) ?></p>
                    <p class="mb-1 text-muted small"><?= $cita['especie'] ?> - <?= $cita['raza'] ?> (<?= $cita['edad'] ?> años)</p>
                    <hr>
                    <p class="mb-1"><strong>Propietario:</strong> <?= htmlspecialchars($cita['dueño'] . ' ' . $cita['apellido']) ?></p>
                    <p class="mb-1 text-muted small">Tel: <?= $cita['telefono'] ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white fw-bold">Motivo de Consulta</div>
                <div class="card-body bg-light">
                    <p class="mb-0 italic">"<?= htmlspecialchars($cita['motivo']) ?>"</p>
                </div>
            </div>
        </div>

        <!-- Columna del Formulario de Diagnóstico -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <form method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">

                        <label class="form-label fw-bold h5">Informe de Diagnóstico y Tratamiento:</label>
                        <textarea name="diagnostico" class="form-control mb-4" rows="12" placeholder="Escriba aquí los hallazgos médicos, tratamiento y recomendaciones..." required <?= $guardado ? 'readonly' : '' ?>><?= htmlspecialchars($cita['diagnostico'] ?? '') ?></textarea>

                        <?php if(!$guardado): ?>
                            <button type="submit" name="finalizar" class="btn btn-success btn-lg w-100 fw-bold">
                                <i class="fas fa-save"></i> Finalizar y Guardar Atención
                            </button>
                        <?php else: ?>
                            <div class="bg-light p-4 rounded border border-success text-center">
                                <h5 class="text-success fw-bold mb-3">¿Qué desea hacer ahora?</h5>
                                <div class="d-grid gap-2 d-md-block">
                                    <a href="generar_pdf.php?id=<?= $id_cita ?>" target="_blank" class="btn btn-dark px-4">
                                        <i class="fas fa-file-pdf"></i> Descargar Informe PDF
                                    </a>
                                    <a href="mis_citas.php" class="btn btn-outline-primary px-4">
                                        Terminar y salir
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>