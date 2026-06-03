</div> <!-- Aquí cerramos el <div class="container mt-4"> que se abre en header.php -->

    <footer class="text-center">
        <div class="container-fluid">
            <p class="mb-o">&copy; 2026 Clínica Veterinaria El Colibrí - Todos los derechos reservados</p>
        </div>
    </footer>

<div class="modal fade" id="modalMotivo" tabindex="-1" aria-labelledby="modalMotivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title" id="modalMotivoLabel">
                    <i class="fas fa-info-circle me-2"></i>Detalles de la Cita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small d-block">Mascota:</label>
                    <strong id="view-mascota" class="fs-5 text-dark"></strong>
                </div>
                <hr>
                <div>
                    <label class="text-muted small d-block mb-2">Motivo / Síntomas:</label>
                    <div id="view-motivo" class="p-3 rounded" style="background-color: #f8f9fa; border-left: 5px solid #00A86B; font-style: italic;">
                    
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border: none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    const modalMotivo = document.getElementById('modalMotivo');
    if (modalMotivo) {
        modalMotivo.addEventListener('show.bs.modal', event => {
        
            const button = event.relatedTarget;
            
            const motivo = button.getAttribute('data-motivo');
            const mascota = button.getAttribute('data-mascota');

            const modalMotivoBody = modalMotivo.querySelector('#view-motivo');
            const modalMascotaTitle = modalMotivo.querySelector('#view-mascota');

            modalMotivoBody.textContent = motivo ? motivo : "No se especificó un motivo.";
            modalMascotaTitle.textContent = mascota;
        });
    }
</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');

            tableRows.forEach(row => {
            
                const rowText = row.innerText.toLowerCase();
                
                if (rowText.includes(value)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>