<?php
/**
 * Sistema de Gerenciamento de Notificações por Email
 * Permite o envio manual e verificação de notificações de permuta
 * Acesse: /admin/nosdvkhsvkçjhfvjhfvjtificacao_email.php
 */

require_once __DIR__ . '/config/autoload.php';

// Verificar se o usuário está logado
Auth::requireAuth();

$mensagem = '';
$estatisticas = [];

// Buscar estatísticas das combinações
try {
    $adminContatos = new AdminContatos();
    $emailNotification = new EmailNotification();
    
    $combinacoesDisponiveis = $adminContatos->buscarCombinacoes();
    $totalContatos = $adminContatos->contarTotalContatos();
    $contatosComEmail = $adminContatos->contarContatosComEmail();
    
    $estatisticas = [
        'total_combinacoes' => count($combinacoesDisponiveis),
        'total_contatos' => $totalContatos,
        'contatos_com_email' => $contatosComEmail,
        'admin_email' => $emailNotification->getAdminEmail(),
        'smtp_configurado' => $emailNotification->isSmtpConfigured()
    ];
} catch (Exception $e) {
    $mensagem = [
        'success' => false,
        'message' => 'Erro ao carregar estatísticas: ' . $e->getMessage()
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'enviar_combinacoes_existentes':
                try {
                    $adminContatos = new AdminContatos();
                    $combinacoes = $adminContatos->buscarCombinacoes();
                    
                    if (!empty($combinacoes)) {
                        $emailNotification = new EmailNotification();
                        
                        // Enviar relatório consolidado para o administrador
                        $resultado = $emailNotification->enviarRelatorioConsolidado($combinacoes);
                        
                        if ($resultado['success']) {
                            $mensagem = [
                                'success' => true,
                                'message' => "Relatório consolidado enviado com sucesso! Total de " . count($combinacoes) . " combinação(ões) incluídas no relatório enviado para o email administrativo."
                            ];
                        } else {
                            $mensagem = [
                                'success' => false,
                                'message' => "Falha ao enviar relatório: " . $resultado['message']
                            ];
                        }
                    } else {
                        $mensagem = [
                            'success' => false,
                            'message' => 'Não há combinações disponíveis no momento para gerar o relatório.'
                        ];
                    }
                } catch (Exception $e) {
                    $mensagem = [
                        'success' => false,
                        'message' => 'Erro ao enviar relatório: ' . $e->getMessage()
                    ];
                }
                break;
                
            case 'verificar_status_email':
                try {
                    $emailNotification = new EmailNotification();
                    $statusSmtp = $emailNotification->verificarConexaoSmtp();
                    
                    if ($statusSmtp['success']) {
                        $mensagem = [
                            'success' => true,
                            'message' => 'Conexão SMTP verificada com sucesso. Sistema de email operacional.'
                        ];
                    } else {
                        $mensagem = [
                            'success' => false,
                            'message' => 'Falha na conexão SMTP: ' . $statusSmtp['message']
                        ];
                    }
                } catch (Exception $e) {
                    $mensagem = [
                        'success' => false,
                        'message' => 'Erro ao verificar conexão: ' . $e->getMessage()
                    ];
                }
                break;
                
            case 'reprocessar_notificacoes':
                try {
                    $adminContatos = new AdminContatos();
                    $resultado = $adminContatos->reprocessarCombinacoes();
                    
                    if ($resultado['success']) {
                        $mensagem = [
                            'success' => true,
                            'message' => $resultado['message']
                        ];
                    } else {
                        $mensagem = [
                            'success' => false,
                            'message' => $resultado['message']
                        ];
                    }
                } catch (Exception $e) {
                    $mensagem = [
                        'success' => false,
                        'message' => 'Erro ao reprocessar: ' . $e->getMessage()
                    ];
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Notificações - SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg">
    <div class="container-fluid py-4">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <?php if (!empty($mensagem)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert <?php echo $mensagem['success'] ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $mensagem['success'] ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $mensagem['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estatísticas do Sistema -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <i class="bi bi-people-fill text-primary fs-1"></i>
                        <h5 class="card-title"><?php echo $estatisticas['total_contatos'] ?? 0; ?></h5>
                        <p class="card-text text-muted">Total de Contatos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <i class="bi bi-envelope-fill text-success fs-1"></i>
                        <h5 class="card-title"><?php echo $estatisticas['contatos_com_email'] ?? 0; ?></h5>
                        <p class="card-text text-muted">Com Email</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <i class="bi bi-shuffle text-warning fs-1"></i>
                        <h5 class="card-title"><?php echo $estatisticas['total_combinacoes'] ?? 0; ?></h5>
                        <p class="card-text text-muted">Combinações Ativas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <i class="bi bi-gear-fill text-info fs-1"></i>
                        <h5 class="card-title">
                            <span class="badge bg-<?php echo ($estatisticas['smtp_configurado'] ?? false) ? 'success' : 'danger'; ?>">
                                <?php echo ($estatisticas['smtp_configurado'] ?? false) ? 'OK' : 'ERRO'; ?>
                            </span>
                        </h5>
                        <p class="card-text text-muted">Status SMTP</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sistema de Notificações -->
        <div class="row">
            <div class="col-12">
                <div class="card sindppenal-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="bi bi-envelope-gear me-2"></i>
                            Sistema de Gerenciamento de Notificações por Email
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Ações Principais -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <i class="bi bi-file-earmark-text-fill text-primary fs-1 mb-3"></i>
                                        <h6 class="card-title">Relatório Consolidado</h6>
                                        <p class="card-text flex-grow-1">
                                            Enviar um relatório completo com todas as combinações de permuta para o email administrativo.
                                        </p>
                                        <form method="POST" class="mt-auto">
                                            <input type="hidden" name="action" value="enviar_combinacoes_existentes">
                                            <button type="submit" class="btn btn-primary w-100" 
                                                    <?php echo ($estatisticas['total_combinacoes'] ?? 0) == 0 ? 'disabled' : ''; ?>>
                                                <i class="bi bi-file-earmark-arrow-up me-2"></i>
                                                Gerar Relatório
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <i class="bi bi-arrow-clockwise text-success fs-1 mb-3"></i>
                                        <h6 class="card-title">Reprocessar Sistema</h6>
                                        <p class="card-text flex-grow-1">
                                            Verificar novamente todo o sistema em busca de novas combinações de permuta.
                                        </p>
                                        <form method="POST" class="mt-auto">
                                            <input type="hidden" name="action" value="reprocessar_notificacoes">
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="bi bi-arrow-clockwise me-2"></i>
                                                Reprocessar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <i class="bi bi-gear-wide-connected text-warning fs-1 mb-3"></i>
                                        <h6 class="card-title">Verificar Sistema</h6>
                                        <p class="card-text flex-grow-1">
                                            Testar a conexão SMTP e verificar se o sistema de email está funcionando.
                                        </p>
                                        <form method="POST" class="mt-auto">
                                            <input type="hidden" name="action" value="verificar_status_email">
                                            <button type="submit" class="btn btn-warning w-100">
                                                <i class="bi bi-gear-wide-connected me-2"></i>
                                                Verificar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-atualizar estatísticas a cada 30 segundos
        setInterval(function() {
            // Opcional: implementar atualização via AJAX das estatísticas
        }, 30000);
        
        // Confirmação antes de gerar relatório
        document.querySelector('form input[value="enviar_combinacoes_existentes"]').closest('form').addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja gerar e enviar o relatório consolidado para o email administrativo?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
