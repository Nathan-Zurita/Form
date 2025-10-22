$(document).ready(function() {
    // Auto-hide mensagens após 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);

    // Confirmação para exclusões
    $('.btn-danger[data-action="delete"]').on('click', function(e) {
        e.preventDefault();
        const nome = $(this).data('nome') || 'este item';
        if (confirm(`Tem certeza que deseja excluir ${nome}?`)) {
            $(this).closest('form').submit();
        }
    });

    // Máscara para telefone em qualquer campo de telefone
    $('input[name="telefone"], input[type="tel"]').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            if (value.length < 14) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
        }
        this.value = value;
    });

    // Animações suaves para cards
    $('.sindppenal-card').hover(
        function() {
            $(this).css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
});