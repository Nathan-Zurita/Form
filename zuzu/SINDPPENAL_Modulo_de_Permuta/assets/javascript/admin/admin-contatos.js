/**
 * SINDPPENAL - Sistema de Permutação
 * Admin Contatos - JavaScript
 * 
 * Funcionalidades:
 * - Configuração Select2 para origem e destinos (área administrativa)
 * - Validação de formulário
 * - Processamento de múltiplas seleções
 */

// Função para remover acentos (normalização)
function removerAcentos(str) {
    if (!str) return '';
    const acentos = {
        'á': 'a', 'à': 'a', 'ã': 'a', 'â': 'a', 'ä': 'a',
        'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
        'í': 'i', 'ì': 'i', 'î': 'i', 'ï': 'i',
        'ó': 'o', 'ò': 'o', 'õ': 'o', 'ô': 'o', 'ö': 'o',
        'ú': 'u', 'ù': 'u', 'û': 'u', 'ü': 'u',
        'ç': 'c', 'ñ': 'n',
        'Á': 'A', 'À': 'A', 'Ã': 'A', 'Â': 'A', 'Ä': 'A',
        'É': 'E', 'È': 'E', 'Ê': 'E', 'Ë': 'E',
        'Í': 'I', 'Ì': 'I', 'Î': 'I', 'Ï': 'I',
        'Ó': 'O', 'Ò': 'O', 'Õ': 'O', 'Ô': 'O', 'Ö': 'O',
        'Ú': 'U', 'Ù': 'U', 'Û': 'U', 'Ü': 'U',
        'Ç': 'C', 'Ñ': 'N'
    };
    
    return str.split('').map(char => acentos[char] || char).join('');
}

$(document).ready(function() {
    // Verificar dependências
    if (typeof jQuery === 'undefined') {
        console.error('jQuery não encontrado');
        return;
    }
    
    if (typeof $.fn.select2 === 'undefined') {
        console.error('Select2 não encontrado');
        return;
    }
    
    // Só configurar Select2 se os elementos existirem (formulário de edição/criação)
    if ($('#origem').length > 0 && $('#destino').length > 0) {
        
        // Verificar se estamos em modo de edição (se há opções pré-carregadas)
        const isEditMode = $('#origem option').length > 1 || $('#destino option').length > 0;
        
        if (isEditMode) {
            // Para modo de edição, configurar Select2 simples sem AJAX
            $('#origem').select2({
                placeholder: "Selecione a unidade de origem",
                allowClear: true,
                width: '100%'
            });
            
            $('#destino').select2({
                placeholder: "Selecione os destinos desejados",
                allowClear: true,
                width: '100%'
            });
            
        } else {
            $('#origem').select2({
                placeholder: "Selecione a unidade de origem",
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '../api/api_unidades.php',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Admin Origem - Erro AJAX:', status, error);
                    }
                },
                language: {
                    noResults: function() {
                        return "Nenhuma unidade encontrada";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Configurar Select2 para destinos (múltipla seleção)
            $('#destino').select2({
                placeholder: "Selecione os destinos desejados",
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '../api/api_unidades.php',
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Admin Destinos - Erro AJAX:', status, error);
                    }
                },
                language: {
                    noResults: function() {
                        return "Nenhuma unidade encontrada";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
            
        }

        // Event listeners para debugging
        $('#origem, #destino').on('select2:open', function() {
        });

        $('#origem, #destino').on('select2:select', function(e) {
        });

        // Máscara para telefone - máximo 11 dígitos
        $('#telefone').on('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            
            // Limita a 11 dígitos
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Aplica a máscara baseado na quantidade de dígitos
            if (value.length <= 10) {
                // Telefone fixo: (11) 1234-5678
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                // Celular: (11) 91234-5678
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            
            this.value = value;
        });

        // Validação adicional no blur para garantir formato correto
        $('#telefone').on('blur', function() {
            const value = this.value.replace(/\D/g, '');
            if (value.length < 10) {
                this.setCustomValidity('Telefone deve ter pelo menos 10 dígitos');
            } else {
                this.setCustomValidity('');
            }
        });

        // Processamento do formulário para converter array em string
        $('form').on('submit', function(e) {
            
            const destinoSelect = $('#destino');
            const destinoValues = destinoSelect.val();
            
            if (destinoValues && destinoValues.length > 0) {
                
                // Criar input hidden com valores concatenados
                const destinoHidden = $('<input>').attr({
                    type: 'hidden',
                    name: 'destino',
                    value: destinoValues.join(', ')
                });
                
                // Remover name do select para não enviar array
                destinoSelect.removeAttr('name');
                
                // Adicionar input hidden ao form
                $(this).append(destinoHidden);
                
            }
            
            return true; // Permitir envio do formulário
        });
    }
    
});