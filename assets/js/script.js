document.addEventListener('DOMContentLoaded', function() {

    const deleteButtons = document.querySelectorAll('a.btn-danger, button.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                e.preventDefault();
            }
        });
    });

    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = '🔍 Buscar en esta tabla...';
    searchInput.className = 'form-control mb-3';

    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const cardBody = table.parentElement;
        if (cardBody) {
            cardBody.insertBefore(searchInput.cloneNode(true), table);
        }
    });

    document.querySelectorAll('input[placeholder*="Buscar"]').forEach(input => {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const table = this.parentElement.querySelector('table');
            if (!table) return;

            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let row of rows) {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            }
        });
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor completa todos los campos obligatorios.');
            }
        });
    });

    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        input.setAttribute('min', today);
    });

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px)';
            card.style.boxShadow = '0 15px 30px rgba(0, 168, 107, 0.25)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '';
        });
    });

    console.log('%c✅ Sistema El Colibrí cargado correctamente 🪶', 'color: #00A86B; font-weight: bold');
});