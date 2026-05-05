import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            Swal.fire({
                title: 'Confirmer la suppression',
                text: form.dataset.confirm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});