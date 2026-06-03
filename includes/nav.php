<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-3" href="/clinica-colibri/dashboard.php"
            🪶 El <span style="color:#FF7F50;">Colibrí</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
    <!-- Inicio: El admin va al dashboard, el veterinario a sus citas -->
    <li class="nav-item">
        <a class="nav-link" href="/clinica-colibri/<?= $_SESSION['rol'] === 'admin' ? 'dashboard.php' : 'pages/mis_citas.php' ?>">Inicio</a>
    </li>

    <?php if ($_SESSION['rol'] === 'admin'): ?>
        <!-- Solo el Administrador ve estos botones -->
        <li class="nav-item"><a class="nav-link" href="/clinica-colibri/pages/clientes.php">Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="/clinica-colibri/pages/mascotas.php">Mascotas</a></li>
        <li class="nav-item"><a class="nav-link" href="/clinica-colibri/pages/citas.php">Citas</a></li>
        <li class="nav-item"><a class="nav-link" href="/clinica-colibri/pages/veterinarios.php">Veterinarios</a></li>
    <?php endif; ?>

    <?php if ($_SESSION['rol'] === 'veterinario'): ?>
        <!-- Solo el Veterinario ve este botón -->
        <li class="nav-item">
            <a class="nav-link" href="/clinica-colibri/pages/mis_citas.php">Mis Citas</a>
        </li>
    <?php endif; ?>
</ul>
            <div class="d-flex align-items-center">
    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'veterinario'): ?>
        <!-- Botón de Perfil exclusivo para Veterinarios -->
        <a href="/clinica-colibri/pages/perfil_vet.php" class="btn btn-sm btn-outline-light me-2">
            <i class="fas fa-user-circle"></i> Mi Perfil
        </a>
    <?php endif; ?>
    
        <span class="text-white me-3 small">
        <i class="fas fa-user border rounded-circle p-1"></i> 
        <?= $_SESSION['user']['nombre'] ?>
        </span>
    
        <a href="/clinica-colibri/logout.php" class="btn btn-sm btn-danger shadow-sm">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
    </div>
</nav>