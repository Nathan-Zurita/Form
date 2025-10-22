
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.src = '../assets/icons/dashboard/eye.svg';
        icon.alt = 'Ocultar senha';
    } else {
        field.type = 'password';
        icon.src = '../assets/icons/dashboard/eye-off.svg';
        icon.alt = 'Mostrar senha';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCriarAdmin');
    const senhaField = document.getElementById('senha');
    const confirmarSenhaField = document.getElementById('confirmar_senha');
    
    confirmarSenhaField.addEventListener('input', function() {
        if (senhaField.value !== confirmarSenhaField.value) {
            confirmarSenhaField.setCustomValidity('As senhas não coincidem');
        } else {
            confirmarSenhaField.setCustomValidity('');
        }
    });
    
    senhaField.addEventListener('input', function() {
        if (confirmarSenhaField.value && senhaField.value !== confirmarSenhaField.value) {
            confirmarSenhaField.setCustomValidity('As senhas não coincidem');
        } else {
            confirmarSenhaField.setCustomValidity('');
        }
    });
    
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        });
    }, 5000);
});