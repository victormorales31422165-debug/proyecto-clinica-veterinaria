<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. IMPORTAR CLASES Y DEPENDENCIAS
require_once '../vendor/autoload.php';
require_once '../clases/DB.php';
require_once '../clases/Cita.php';
require_once '../clases/Mascota.php';
require_once '../clases/Usuario.php';
require_once '../includes/TokenAntiCSRF.php';

use PHPMailer\PHPMailer\PHPMailer;

// Seguridad de Rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

// 2. INICIALIZAR OBJETOS
$database = new DB();
$db = $database->conectar();
$citaObj = new Cita($db);
$mascotaObj = new Mascota($db);
$usuarioObj = new Usuario($db);

require_once '../includes/header.php';

// Función de envío de correos profesional
function enviarAviso($usuarioObj, $mascotaObj, $id_v, $fh, $id_m) {
    $v = $usuarioObj->obtenerPerfil($id_v);
    $m = $mascotaObj->obtenerPorId($id_m);
    
    if ($v && !empty($v['correo'])) {
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'clinicaveterinariaelcolibri1@gmail.com'; 
            $mail->Password = 'zyfn atzg ygau jmrs';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Destinatarios
            $mail->setFrom('clinica@elcolibri.com', 'Clínica Veterinaria El Colibrí');
            $mail->addAddress($v['correo'], $v['nombre']);

            // Contenido del Correo
            $mail->isHTML(true);                                  
            $mail->Subject = '📋 Nueva Cita Asignada - ' . htmlspecialchars($m['nombre']);

            // Formateamos la fecha y hora para que sea legible
            $fechaFormateada = date('d/m/Y', strtotime($fh));
            $horaFormateada = date('h:i A', strtotime($fh));

            // DISEÑO DE LA PLANTILLA HTML PROFESIONAL
            $cuerpo = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
                    <div style='background-color: #198754; padding: 25px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 1px;'>El <span style='color: #FF7F50;'>Colibrí</span></h1>
                        <p style='color: #e0e0e0; margin: 5px 0 0 0; font-size: 14px; text-transform: uppercase;'>Clínica Veterinaria</p>
                    </div>
                    <div style='padding: 35px; color: #333333;'>
                        <h2 style='color: #198754; margin-top: 0;'>Hola, Dr. " . htmlspecialchars($v['nombre']) . "</h2>
                        <p style='font-size: 16px; line-height: 1.6; color: #555555;'>
                            Le informamos que se ha programado una nueva cita médica y usted ha sido asignado como el médico responsable.
                        </p>
                        <div style='background-color: #fcfcfc; border: 1px solid #eeeeee; border-left: 5px solid #FF7F50; padding: 20px; margin: 25px 0; border-radius: 4px;'>
                            <h3 style='margin-top: 0; font-size: 16px; color: #198754;'>Detalles del Paciente:</h3>
                            <p style='margin: 8px 0; font-size: 15px;'><strong>🐾 Mascota:</strong> " . htmlspecialchars($m['nombre']) . "</p>
                            <p style='margin: 8px 0; font-size: 15px;'><strong>📅 Fecha:</strong> " . $fechaFormateada . "</p>
                            <p style='margin: 8px 0; font-size: 15px;'><strong>⏰ Hora:</strong> " . $horaFormateada . "</p>
                            <p style='margin: 8px 0; font-size: 15px;'><strong>🩺 Especie/Raza:</strong> " . htmlspecialchars($m['especie']) . " - " . htmlspecialchars($m['raza']) . "</p>
                        </div>
                        <p style='font-size: 15px; color: #555555;'>Recuerde revisar el historial clínico en el panel administrativo antes de la atención.</p>
                        <div style='text-align: center; margin-top: 35px;'>
                            <a href='http://localhost/clinica-colibri/' style='background-color: #FF7F50; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>Acceder al Sistema</a>
                        </div>
                    </div>
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; color: #999999; font-size: 12px; border-top: 1px solid #eeeeee;'>
                        <p style='margin: 0;'>Este es un mensaje automático generado por el Sistema Colibrí.</p>
                        <p style='margin: 5px 0 0 0;'>&copy; " . date('Y') . " Clínica Veterinaria El Colibrí</p>
                    </div>
                </div>
            </body>
            </html>";

            $mail->Body = $cuerpo;
            $mail->AltBody = "Nueva cita para " . $m['nombre'] . " el " . $fechaFormateada . " a las " . $horaFormateada;
            $mail->send();
        } catch (Exception $e) {}
    }
}

// 3. PROCESAR ACCIONES (POST)
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

// 4. PROCESAR ACCIONES (GET)
if (isset($_GET['eliminar'])) {
    $citaObj->eliminar((int)$_GET['eliminar']);
    header("Location: citas.php"); exit();
}

$editRow = null;
if (isset($_GET['editar'])) {
    $editRow = $citaObj->obtenerPorId((int)$_GET['editar']);
}

// 5. OBTENER LISTADOS PARA LA VISTA
$mascotas = $mascotaObj->leerTodas();
$veterinarios = $usuarioObj->listarVeterinarios();
$citas = $citaObj->leerTodas();
?>

<div class="container-fluid mt-3">
    <h2 class="mb-4">Gestión de Citas</h2>

    <!-- Formulario (Diseño intacto) -->
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
                            <?php while($v = $veterinarios->fetch(PDO::FETCH_ASSOC)): ?>
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

    <!-- Tabla (Diseño intacto con la corrección de color en estado) -->
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
                        <td class="fw-bold"><?= htmlspecialchars($c['nombre_mascota'] ?? $c['m_nom'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($c['v_nom'] ?? 'Sin asignar') ?></td>
                        <!-- CAMBIO AQUÍ: Color verde si está completada -->
                        <td>
                            <span class="badge <?= ($c['estado'] == 'completada' ? 'bg-success' : 'bg-warning text-dark') ?>">
                                <?= htmlspecialchars($c['estado']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalMotivo" 
                                    data-mascota="<?= htmlspecialchars($c['nombre_mascota'] ?? $c['m_nom'] ?? 'N/A') ?>" 
                                    data-motivo="<?= htmlspecialchars($c['motivo'] ?? 'Sin motivo registrado') ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="?editar=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="?eliminar=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>