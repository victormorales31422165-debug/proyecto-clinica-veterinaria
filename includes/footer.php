</div> <!-- Cierre del contenedor principal abierto en el header o páginas -->

     <footer class="text-center mt-auto py-3 bg-white border-top shadow-sm">
        <div class="container-fluid">
            <!-- Usamos text-dark para que el mensaje sea bien visible -->
            <p class="mb-0 text-dark fw-bold">
                &copy; 2026 Clínica Veterinaria El Colibrí - Todos los derechos reservados
            </p>
        </div>
    </footer>

    <!-- MODAL DE DETALLES (Se mantiene diseño intacto) -->
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
                            <!-- El contenido se carga vía JS -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border: none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
    /**
     * Lógica para el Modal de Motivo
     * Se activa cuando un botón tiene: data-bs-toggle="modal" data-bs-target="#modalMotivo"
     */
    const modalMotivo = document.getElementById('modalMotivo');
    if (modalMotivo) {
        modalMotivo.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const motivo = button.getAttribute('data-motivo');
            const mascota = button.getAttribute('data-mascota');

            const modalMotivoBody = modalMotivo.querySelector('#view-motivo');
            const modalMascotaTitle = modalMotivo.querySelector('#view-mascota');

            modalMotivoBody.textContent = motivo ? motivo : "No se especificó un motivo.";
            modalMascotaTitle.textContent = mascota ? mascota : "Mascota no identificada";
        });
    }

    /**
     * Lógica de Búsqueda en Tablas (searchInput)
     */
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const value = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('table tbody tr');

                tableRows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    // Si el texto de la fila coincide con la búsqueda o la búsqueda está vacía
                    row.style.display = rowText.includes(value) ? '' : 'none';
                });
            });
        }
    });
    </script>

    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>