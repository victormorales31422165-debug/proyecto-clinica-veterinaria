<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container-fluid">
        <!-- El logo ahora apunta dinámicamente al inicio según el rol -->
        <a class="navbar-brand fw-bold fs-3" href="<?= $_SESSION['rol'] === 'admin' ? 'dashboard.php' : 'mis_citas.php' ?>">
            🪶 El <span style="color:#FF7F50;">Colibrí</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                
                <!-- Link de Inicio dinámico -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= $_SESSION['rol'] === 'admin' ? 'dashboard.php' : 'mis_citas.php' ?>">Inicio</a>
                </li>

                <!-- Menú para Administrador (Rutas corregidas) -->
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="mascotas.php">Mascotas</a></li>
                    <li class="nav-item"><a class="nav-link" href="citas.php">Citas</a></li>
                    <li class="nav-item"><a class="nav-link" href="veterinarios.php">Veterinarios</a></li>
                <?php endif; ?>

                <!-- Menú para Veterinario -->
                <?php if ($_SESSION['rol'] === 'veterinario'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="mis_citas.php">Mis Citas</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center">
                <!-- Perfil (Solo para veterinarios) -->
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario'): ?>
                    <a href="perfil_vet.php" class="btn btn-sm btn-outline-light me-2">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </a>
                <?php endif; ?>
                
                <!-- Nombre del usuario logueado -->
                <span class="text-white me-3 small">
                    <i class="fas fa-user border rounded-circle p-1"></i> 
                    <?= htmlspecialchars($_SESSION['user']['nombre']) ?>
                </span>
            
                <!-- Botón de Cerrar Sesión (Ruta corregida) -->
                <a href="logout.php" class="btn btn-sm btn-danger shadow-sm">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>