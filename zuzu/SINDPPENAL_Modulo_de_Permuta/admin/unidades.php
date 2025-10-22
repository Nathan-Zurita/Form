<?php
require_once __DIR__ . '/config/autoload.php';

Auth::requireAuth();

$adminUnidades = new AdminUnidades();
$mensagem = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $resultado = $adminUnidades->criar($_POST);
                $resultado['type'] = $resultado['success'] ? 'success' : 'danger';
                $mensagem = $resultado;
                break;
                
            case 'update':
                $resultado = $adminUnidades->atualizar($_POST['id'], $_POST);
                $resultado['type'] = $resultado['success'] ? 'warning' : 'danger';
                $mensagem = $resultado;
                break;
                
            case 'delete':
                $resultado = $adminUnidades->excluir($_POST['id']);
                // Aplicar PRG (Post-Redirect-Get) para evitar reenvio do formulário
                if (!headers_sent()) {
                    $resultado['type'] = $resultado['success'] ? 'danger' : 'danger';
                    $_SESSION['mensagem_unidades'] = $resultado;
                    header('Location: unidades.php');
                    exit;
                } else {
                    $resultado['type'] = $resultado['success'] ? 'danger' : 'danger';
                    $mensagem = $resultado; // fallback se headers já enviados
                }
                break;

            case 'toggle_status':
                $id = $_POST['id'];
                $ativo = $_POST['ativo'] == '1' ? 0 : 1; // Inverter status
                
                // Debug temporário
                error_log("Debug toggle_status - ID: $id, Ativo atual: " . $_POST['ativo'] . ", Novo ativo: $ativo");
                
                $resultado = $adminUnidades->alterarStatus($id, $ativo);
                
                // Debug resultado
                error_log("Debug resultado: " . json_encode($resultado));
                
                if (!headers_sent()) {
                    $resultado['type'] = $resultado['success'] ? 'info' : 'warning';
                    $_SESSION['mensagem_unidades'] = $resultado;
                    header('Location: unidades.php');
                    exit;
                } else {
                    $resultado['type'] = $resultado['success'] ? 'info' : 'warning';
                    $mensagem = $resultado;
                }
                break;
        }
    }
}

// Mensagens via Sessão (uma única exibição)
if (empty($mensagem) && isset($_SESSION['mensagem_unidades'])) {
    $mensagem = $_SESSION['mensagem_unidades'];
    unset($_SESSION['mensagem_unidades']); // Remove da sessão após usar
}

// Parâmetros de busca e paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Buscar dados
$dados = $adminUnidades->listarTodos($page, 50, $search);
$unidade_edit = null;

if ($edit_id > 0) {
    $unidade_edit = $adminUnidades->buscarPorId($edit_id);
    $action = 'edit';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Unidades - Administração SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Estilização dos botões de ação da tabela */
        .btn-group .btn {
            border-radius: 6px !important;
            margin: 0 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        
        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn-group .btn:first-child {
            margin-left: 0;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        /* Garantir alinhamento perfeito dos ícones */
        .btn img.svg-icon-white {
            filter: brightness(0) invert(1);
            vertical-align: middle;
        }
        
        /* Cores consistentes para os botões */
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        /* Hover states */
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
        }
        
        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
        
        /* Garantir que todos os botões tenham o mesmo tamanho */
        .btn-group .btn {
            min-width: 38px !important;
            max-width: 38px !important;
            width: 38px !important;
            height: 38px !important;
            position: relative;
            z-index: 1;
        }
        
        /* Garantir que os botões sejam clicáveis */
        .btn-group .btn, .btn-group .btn * {
            pointer-events: auto !important;
        }
        
        /* Centralizar a coluna de ações */
        .table td:last-child {
            text-align: center;
            vertical-align: middle;
        }
        
        /* Debug: destacar área clicável */
        .form-toggle-status {
            border: 2px solid red !important;
            background: rgba(255,0,0,0.1) !important;
        }
        
        .form-toggle-status button {
            border: 2px solid blue !important;
            background: rgba(0,0,255,0.2) !important;
        }
        
        /* Responsividade dos botões */
        @media (max-width: 768px) {
            .btn-group .btn {
                width: 35px !important;
                height: 35px !important;
                margin: 0 1px;
            }
            
            .btn-group .btn img {
                width: 14px !important;
                height: 14px !important;
            }
        }
    </style>
</head>
<body class="sindppenal-bg">
    <div class="container-fluid py-4">
        <!-- Header -->
        <?php include __DIR__ . '/includes/header.php'; ?>

        <?php if (!empty($mensagem)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-<?php echo isset($mensagem['type']) ? $mensagem['type'] : ($mensagem['success'] ? 'success' : 'danger'); ?> alert-dismissible fade show auto-hide-alert" role="alert">
                        <?php echo htmlspecialchars($mensagem['message'] ?? ''); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card sindppenal-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <img src="../assets/icons/dashboard/unidade.svg" alt="Unidade" class="svg-icon size-sm me-2">
                            <?php echo $action === 'add' ? 'Adicionar Nova Unidade' : 'Editar Unidade'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $unidade_edit['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="codigo" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/card.svg" alt="Código" class="svg-icon size-sm">
                                        Código (Sigla):
                                    </label>
                                    <input type="text" id="codigo" name="codigo" class="form-control" required 
                                           placeholder="Ex: UCTP, CPFC"
                                           value="<?php echo $unidade_edit ? htmlspecialchars($unidade_edit['codigo'] ?? '') : ''; ?>">
                                    <div class="invalid-feedback">
                                        Por favor, informe o código da unidade.
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="nome" class="form-label fw-semibold">
                                        <img src="../assets/icons/dashboard/unidade.svg" alt="Nome" class="svg-icon size-sm">
                                        Nome da Unidade:
                                    </label>
                                    <input type="text" id="nome" name="nome" class="form-control" required
                                           placeholder="Nome completo da unidade"
                                           value="<?php echo $unidade_edit ? htmlspecialchars($unidade_edit['nome'] ?? '') : ''; ?>">
                                    <div class="invalid-feedback">
                                        Por favor, informe o nome da unidade.
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="cidade" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/location.svg" alt="Cidade" class="svg-icon size-sm">
                                        Cidade:
                                    </label>
                                    <input type="text" id="cidade" name="cidade" class="form-control" required
                                           placeholder="Cidade onde fica a unidade"
                                           value="<?php echo $unidade_edit ? htmlspecialchars($unidade_edit['cidade'] ?? '') : ''; ?>">
                                    <div class="invalid-feedback">
                                        Por favor, informe a cidade.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"
                                               <?php echo (!$unidade_edit || $unidade_edit['ativo']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold" for="ativo">
                                            Unidade Ativa
                                        </label>
                                        <div class="form-text">Marque para manter a unidade ativa no sistema.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <img src="../assets/icons/formulario/enviar.svg" alt="Salvar" class="svg-icon-white size-sm">
                                    <?php echo $action === 'add' ? 'Criar Unidade' : 'Atualizar Unidade'; ?>
                                </button>
                                <a href="unidades.php" class="btn btn-danger">
                                    <img src="../assets/icons/dashboard/cancelar.svg" alt="Cancelar" class="svg-icon-white size-sm">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Listagem -->
        <div class="row">
            <div class="col-12">
                <div class="card sindppenal-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <img src="../assets/icons/dashboard/unidade.svg" alt="Unidades" class="svg-icon size-sm me-2">
                            Unidades Cadastradas
                        </h5>
                        <a href="unidades.php?action=add" class="btn btn-primary btn-sm">
                            <img src="../assets/icons/dashboard/add.svg" alt="Adicionar" class="svg-icon-white size-sm">
                            Nova Unidade
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Busca -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Buscar por código, nome ou cidade..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <img src="../assets/icons/dashboard/lupa.svg" alt="Buscar" class="svg-icon-white size-sm">
                                            Buscar
                                        </button>
                                        <?php if (!empty($search)): ?>
                                            <a href="unidades.php" class="btn btn-outline-danger">
                                                <img src="../assets/icons/dashboard/cancelar.svg" alt="Limpar" class="svg-icon-white size-sm">
                                                Limpar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    Total: <?php echo $dados['total']; ?> unidade(s)
                                </small>
                            </div>
                        </div>

                        <?php if (!empty($dados['unidades'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nome</th>
                                        <th>Cidade</th>
                                        <th>Status</th>
                                        <th>Criado em</th>
                                        <th width="180" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dados['unidades'] as $unidade): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($unidade['codigo']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($unidade['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($unidade['cidade']); ?></td>
                                        <td>
                                            <?php if ($unidade['ativo']): ?>
                                                <span class="badge bg-success">Ativa</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inativa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($unidade['created']) {
                                                echo date('d/m/Y H:i', strtotime($unidade['created']));
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm d-flex justify-content-center" role="group">
                                                <a href="unidades.php?edit=<?php echo $unidade['id']; ?>" 
                                                   class="btn btn-primary d-flex align-items-center justify-content-center" 
                                                   title="Editar" style="width: 38px; height: 38px;">
                                                    <img src="../assets/icons/dashboard/editar.svg" alt="Editar" 
                                                         class="svg-icon-white" style="width: 16px; height: 16px;">
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      >
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo $unidade['id']; ?>">
                                                    <input type="hidden" name="ativo" value="<?php echo $unidade['ativo']; ?>">
                                                    <button type="submit" 
                                                            class="btn btn-<?php echo $unidade['ativo'] ? 'secondary' : 'success'; ?> d-flex align-items-center justify-content-center" 
                                                            title="<?php echo $unidade['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                                            style="width: 38px; height: 38px;">
                                                        <img src="../assets/icons/<?php echo $unidade['ativo'] ? 'dashboard/disabled' : 'formulario/check'; ?>.svg" 
                                                             alt="<?php echo $unidade['ativo'] ? 'Desativar' : 'Ativar'; ?>" 
                                                             class="svg-icon-white" style="width: 16px; height: 16px;">
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      class="form-delete-unidade">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $unidade['id']; ?>">
                                                    <button type="submit" 
                                                            class="btn btn-danger d-flex align-items-center justify-content-center" 
                                                            title="Excluir" style="width: 38px; height: 38px;">
                                                        <img src="../assets/icons/dashboard/lixo.svg" alt="Excluir" 
                                                             class="svg-icon-white" style="width: 16px; height: 16px;">
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <?php if ($dados['pages'] > 1): ?>
                        <nav aria-label="Paginação">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($dados['pages'], $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $dados['pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Próxima</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="text-center py-4">
                            <img src="../assets/icons/dashboard/unidade.svg" alt="Sem unidades" class="svg-icon size-lg mb-3 opacity-50">
                            <p class="text-muted">
                                <?php if (!empty($search)): ?>
                                    Nenhuma unidade encontrada para "<strong><?php echo htmlspecialchars($search); ?></strong>".
                                <?php else: ?>
                                    Nenhuma unidade cadastrada ainda.
                                <?php endif; ?>
                            </p>
                            <?php if (empty($search)): ?>
                                <a href="unidades.php?action=add" class="btn btn-sindppenal">
                                    <img src="../assets/icons/dashboard/add.svg" alt="Adicionar" class="svg-icon size-sm">
                                    Cadastrar Primeira Unidade
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript customizado -->
    <script src="../assets/javascript/admin/admin-unidades.js"></script>
</body>
</html>