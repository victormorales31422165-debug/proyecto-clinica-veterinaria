<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


require_once '../vendor/autoload.php';
require_once '../clases/DB.php';
require_once '../clases/Cita.php';
require_once '../clases/Mascota.php';
require_once '../clases/Usuario.php';
require_once '../includes/TokenAntiCSRF.php';

use PHPMailer\PHPMailer\PHPMailer;


if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php"); exit();
}


$database = new DB();
$db = $database->conectar();
$citaObj = new Cita($db);
$mascotaObj = new Mascota($db);
$usuarioObj = new Usuario($db);

require_once '../includes/header.php';



 
function enviarAviso($usuarioObj, $mascotaObj, $id_v, $fh, $id_m) {
    $v = $usuarioObj->obtenerPerfil($id_v);
    $m = $mascotaObj->obtenerPorId($id_m);
    
    if ($v && !empty($v['correo'])) {
        $mail = new PHPMailer(true);
        try {
            
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'clinicaveterinariaelcolibri1@gmail.com'; 
            $mail->Password = 'zyfn atzg ygau jmrs';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

           
            $mail->setFrom('clinicaveterinariaelcolibri1@gmail.com', 'Clínica Veterinaria El Colibrí');
            $mail->addAddress($v['correo'], $v['nombre']);

            
            $mail->isHTML(true);                                  
            $mail->Subject = 'NOTIFICACIÓN MÉDICA: Cita programada para:' . htmlspecialchars($m['nombre']);

            $fechaF = date('d/m/Y', strtotime($fh));
            $horaF = date('h:i A', strtotime($fh));

            $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #ddd;'>
                    <div style='background-color: #198754; padding: 25px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0;'>El <span style='color: #FF7F50;'>Colibrí</span></h1>
                        <p style='color: #e0e0e0; margin: 5px 0 0 0;'>Clínica Veterinaria</p>
                    </div>
                    <div style='padding: 30px; color: #333;'>
                        <h2 style='color: #198754;'>Hola, Dr. " . htmlspecialchars($v['nombre']) . "</h2>
                        <p>Se le ha asignado una nueva cita médica. Detalles del paciente:</p>
                        <div style='background-color: #f9f9f9; border-left: 5px solid #FF7F50; padding: 15px; margin: 20px 0;'>
                            <p><strong>🐾 Mascota:</strong> " . htmlspecialchars($m['nombre']) . "</p>
                            <p><strong>📅 Fecha:</strong> $fechaF</p>
                            <p><strong>⏰ Hora:</strong> $horaF</p>
                            <p><strong>🩺 Especie:</strong> " . htmlspecialchars($m['especie']) . "</p>
                        </div>
                        </p>
                           <p style='font-size: 15px; color: #555555;'>Consulte los detalles en el panel de sus citas asignadas de la clínica.</p>
                    </div>
                    <div style='background-color: #eee; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                        &copy; " . date('Y') . " Clínica Veterinaria El Colibrí
                    </div>
                </div>
            </body>
            </html>";

            $mail->AltBody = "Nueva cita para " . $m['nombre'] . " el $fechaF a las $horaF";
            $mail->send();
        } catch (Exception $e) {
            error_log("Error PHPMailer: " . $mail->ErrorInfo);
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TokenAntiCSRF::consumirToken($_POST['token_csrf'] ?? '')) { die("CSRF Error"); }

    if (isset($_POST['crear_cita'])) {
        if($citaObj->crearCitaCompleta($_POST)) {
            
            if(!empty($_POST['id_veterinario'])) {
                enviarAviso($usuarioObj, $mascotaObj, $_POST['id_veterinario'], $_POST['fecha_hora'], $_POST['id_mascota']);
            }
        }
    }
    
    if (isset($_POST['actualizar_cita'])) {
        $citaObj->actualizarCitaCompleta($_POST);
    }
    
    header("Location: citas.php"); exit();
}


if (isset($_GET['eliminar'])) {
    $citaObj->eliminar((int)$_GET['eliminar']);
    header("Location: citas.php"); exit();
}

$editRow = null;
if (isset($_GET['editar'])) {
    $editRow = $citaObj->obtenerPorId((int)$_GET['editar']);
}


$mascotas = $mascotaObj->leerTodas();
$veterinarios = $usuarioObj->listarVeterinarios();
$citas = $citaObj->leerTodas();
?>

<div class="container-fluid mt-3">
    <h2 class="mb-4">Gestión de Citas</h2>

    
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <?= $editRow ? 'Editar Cita' : 'Programar Nueva Cita' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="token_csrf" value="<?= TokenAntiCSRF::generarToken() ?>">
                <?php if($editRow): ?>
                    <input type="hidden" name="id_cita" value="<?= $editRow['id_cita'] ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha y Hora *</label>
                        <input type="datetime-local" name="fecha_hora" class="form-control" value="<?= $editRow['fecha_hora'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mascota *</label>
                        <select name="id_mascota" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php while($m = $mascotas->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?= $m['id_mascota'] ?>" <?= (($editRow['id_mascota'] ?? '') == $m['id_mascota'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($m['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Veterinario</label>
                        <select name="id_veterinario" class="form-select">
                            <option value="">Sin asignar</option>
                            <?php 
                           
                            $vets = $usuarioObj->listarVeterinarios(); 
                            while($v = $vets->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?= $v['id_veterinario'] ?>" <?= (($editRow['id_veterinario'] ?? '') == $v['id_veterinario'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($v['nombre']) ?>
                                </option>
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
                    <tr>
                        <th class="ps-3">Fecha / Hora</th>
                        <th>Mascota</th>
                        <th>Veterinario</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($c = $citas->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td class="ps-3">
                            <?= date('d/m/Y', strtotime($c['fecha_hora'])) ?> 
                            <small class="text-muted">(<?= date('H:i', strtotime($c['fecha_hora'])) ?>)</small>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($c['nombre_mascota'] ?? 'N/A') ?></td>
                        
                        <td><?= htmlspecialchars($c['v_nom'] ?? 'Sin asignar') ?></td>
                        <td>
                            <span class="badge <?= ($c['estado'] == 'completada' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                <?= htmlspecialchars($c['estado']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalMotivo" 
                                    data-mascota="<?= htmlspecialchars($c['nombre_mascota'] ?? 'N/A') ?>" 
                                    data-motivo="<?= htmlspecialchars($c['motivo'] ?? 'Sin motivo') ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="?editar=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-pen"></i></a>
                            <a href="?eliminar=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>