/**
 * JavaScript para o CRUD de Unidades
 * Sistema SINDPPENAL - Módulo de Permutação
 */

$(document).ready(function() {
    
    console.log('Admin Unidades JS carregado'); // Debug
    
    // Debug: Verificar se os formulários existem na página
    console.log('Formulários toggle-status encontrados:', $('.form-toggle-status').length);
    console.log('Formulários delete encontrados:', $('.form-delete-unidade').length);
    
    // Auto-focus no campo de busca quando não há dados sendo editados
    const isListView = window.location.search.includes('action=list') || 
                      (!window.location.search.includes('action=') && 
                       !window.location.search.includes('edit='));
    
    if (isListView) {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
    }

    // Transformar código em maiúsculo automaticamente
    const codigoInput = document.getElementById('codigo');
    if (codigoInput) {
        codigoInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Também aplicar no blur para garantir
        codigoInput.addEventListener('blur', function() {
            this.value = this.value.toUpperCase().trim();
        });
    }

    // Validação do formulário antes do envio (apenas para formulário de criar/editar)
    const formUnidade = document.querySelector('form[method="POST"]:not(.form-toggle-status):not(.form-delete-unidade)');
    if (formUnidade) {
        formUnidade.addEventListener('submit', function(e) {
            let isValid = true;
            const codigo = document.getElementById('codigo');
            const nome = document.getElementById('nome');
            const cidade = document.getElementById('cidade');

            // Remover classes de erro anteriores
            [codigo, nome, cidade].forEach(field => {
                if (field) {
                    field.classList.remove('is-invalid');
                }
            });

            // Validar código
            if (codigo && (!codigo.value || codigo.value.trim().length < 2)) {
                codigo.classList.add('is-invalid');
                isValid = false;
            }

            // Validar nome
            if (nome && (!nome.value || nome.value.trim().length < 3)) {
                nome.classList.add('is-invalid');
                isValid = false;
            }

            // Validar cidade
            if (cidade && (!cidade.value || cidade.value.trim().length < 2)) {
                cidade.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                showAlert('Por favor, preencha todos os campos obrigatórios corretamente.', 'danger');
                return false;
            }
        });
    }

    // Confirmação personalizada para exclusão usando event delegation
    $(document).on('submit', '.form-delete-unidade', function(e) {
        e.preventDefault();
        console.log('Formulário delete-unidade enviado'); // Debug
        
        const form = this;
        const codigo = $(form).closest('tr').find('td strong').text();
        
        if (confirm(`Tem certeza que deseja excluir a unidade "${codigo}"?\n\nEsta ação não pode ser desfeita e a unidade será removida permanentemente do sistema.`)) {
            console.log('Confirmado - excluindo unidade'); // Debug
            form.submit();
        }
    });

    // Versão mais simples e direta
    $(document).on('submit', '.form-toggle-status', function(e) {
        e.preventDefault();
        console.log('=== TOGGLE STATUS FORM SUBMIT ===');
        
        const form = $(this);
        const codigo = form.closest('tr').find('td:first strong').text();
        const isAtivo = form.find('input[name="ativo"]').val() === '1';
        const action = isAtivo ? 'desativar' : 'ativar';
        
        console.log('Código:', codigo);
        console.log('Status atual:', isAtivo ? 'ativo' : 'inativo');
        console.log('Ação:', action);
        
        if (confirm(`Confirmar ${action} a unidade "${codigo}"?`)) {
            console.log('Confirmado - enviando formulário');
            // Submeter o formulário real
            this.submit();
        } else {
            console.log('Cancelado pelo usuário');
        }
    });



    // Limpar busca com duplo clique no campo
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('dblclick', function() {
            if (this.value) {
                this.value = '';
                this.focus();
            }
        });
    }

    // Auto-hide alerts após 3 segundos
    const alerts = document.querySelectorAll('.auto-hide-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 3000); // 3 segundos
    });

    // Tooltip para botões de ação
    const actionButtons = document.querySelectorAll('[title]');
    actionButtons.forEach(button => {
        if (button.title) {
            new bootstrap.Tooltip(button);
        }
    });

    // Highlight da linha ao passar mouse
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Teclas de atalho
    document.addEventListener('keydown', function(e) {
        // Ctrl + N = Nova unidade
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            const newButton = document.querySelector('a[href*="action=add"]');
            if (newButton) {
                window.location.href = newButton.href;
            }
        }
        
        // ESC = Cancelar/voltar para listagem
        if (e.key === 'Escape') {
            const cancelButton = document.querySelector('a[href="unidades.php"]');
            if (cancelButton && (window.location.search.includes('action=') || window.location.search.includes('edit='))) {
                window.location.href = cancelButton.href;
            }
        }
    });
});

/**
 * Função auxiliar para mostrar alertas
 */
function showAlert(message, type = 'info') {
    const alertsContainer = document.querySelector('.container-fluid .row:first-child .col-12') || 
                           document.querySelector('.container-fluid');
    
    if (alertsContainer) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show auto-hide-alert" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHtml;
        const alertElement = tempDiv.firstElementChild;
        
        alertsContainer.appendChild(alertElement);
        
        // Auto-hide após 3 segundos
        setTimeout(() => {
            if (alertElement && alertElement.parentNode) {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 3000);
    }
}

/**
 * Função para filtrar tabela em tempo real (opcional)
 */
function filterTable(searchTerm) {
    const table = document.querySelector('table tbody');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    searchTerm = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const codigo = row.cells[0].textContent.toLowerCase();
        const nome = row.cells[1].textContent.toLowerCase();
        const cidade = row.cells[2].textContent.toLowerCase();
        
        if (codigo.includes(searchTerm) || nome.includes(searchTerm) || cidade.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}