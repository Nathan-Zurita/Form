/**
 * Máscara para campos de telefone
 * Formata automaticamente no padrão brasileiro: (27) 99929-9325
 */
document.addEventListener('DOMContentLoaded', function() {
    const telefoneInputs = document.querySelectorAll('input[type="tel"], input[name="telefone"]');
    
    telefoneInputs.forEach(function(telefoneInput) {
        // Evento para formatação automática
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = `(${value}`;
                } else if (value.length <= 7) {
                    value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
                } else if (value.length <= 11) {
                    value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
                } else {
                    value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7, 11)}`;
                }
            }
            
            e.target.value = value;
        });
        
        // Permite apenas números, parênteses, espaços e hífens
        telefoneInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[0-9()\ \-]/.test(char)) {
                e.preventDefault();
            }
        });
        
        // Atualiza o maxlength para acomodar a formatação
        telefoneInput.setAttribute('maxlength', '15');
    });
});