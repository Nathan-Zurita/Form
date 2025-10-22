<?php
require_once __DIR__ . '/config/autoload.php';

Auth::requireAuth();

$adminContatos = new AdminContatos();
$mensagem = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $resultado = $adminContatos->criar($_POST);
                $mensagem = $resultado;
                break;
                
            case 'update':
                $resultado = $adminContatos->atualizar($_POST['id'], $_POST);
                $mensagem = $resultado;
                break;
                
            case 'delete':
                $resultado = $adminContatos->excluir($_POST['id']);
                // Aplicar PRG (Post-Redirect-Get) para evitar reenvio do formulário
                if (!headers_sent()) {
                    if ($resultado['success']) {
                        header('Location: contatos.php?msg=deleted');
                    } else {
                        // Encodar mensagem de erro para exibição
                        $m = urlencode($resultado['message'] ?? 'Erro ao excluir contato.');
                        header('Location: contatos.php?msg=delete_error&detail=' . $m);
                    }
                    exit;
                } else {
                    $mensagem = $resultado; // fallback se headers já enviados
                }
                break;
        }
    }
}

// Mensagens via PRG
if (empty($mensagem) && isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $mensagem = ['success' => true, 'message' => 'Contato excluído com sucesso!'];
    } elseif ($_GET['msg'] === 'delete_error') {
        $detalhe = isset($_GET['detail']) ? urldecode($_GET['detail']) : 'Erro ao excluir contato.';
        $mensagem = ['success' => false, 'message' => $detalhe];
    }
}

// Parâmetros de busca e paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Buscar dados
$dados = $adminContatos->listarTodos($page, 50, $search);
$contato_edit = null;

if ($edit_id > 0) {
    $contato_edit = $adminContatos->buscarPorId($edit_id);
    $action = 'edit';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Contatos - Administração SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery e Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg">
    <div class="container-fluid py-4">
        <!-- Header -->
        <?php include __DIR__ . '/includes/header.php'; ?>

        <?php if (!empty($mensagem)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert <?php echo $mensagem['success'] ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
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
                        <h5 class="card-title mb-0"><?php echo $action === 'add' ? 'Adicionar Novo Contato' : 'Editar Contato'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $contato_edit['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/user.svg" alt="Nome" class="svg-icon size-sm">
                                        Nome Completo:
                                    </label>
                                    <input type="text" id="nome" name="nome" class="form-control" required 
                                           placeholder="Digite o nome completo"
                                           value="<?php echo $contato_edit ? htmlspecialchars($contato_edit['nome'] ?? '') : ''; ?>">
                                    <div class="invalid-feedback">
                                        Por favor, informe o nome completo.
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="num_funcional" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/card.svg" alt="Número" class="svg-icon size-sm">
                                        Número Funcional:
                                    </label>
                                    <input type="text" id="num_funcional" name="num_funcional" class="form-control" required
                                           placeholder="Ex: 12345"
                                           value="<?php echo $contato_edit ? htmlspecialchars($contato_edit['num_funcional'] ?? '') : ''; ?>">
                                    <div class="invalid-feedback">
                                        Por favor, informe o número funcional.
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="telefone" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/phone.svg" alt="Telefone" class="svg-icon size-sm">
                                        Telefone:
                                    </label>
                                    <input type="tel" id="telefone" name="telefone" class="form-control" 
                                           placeholder="(11) 99999-9999" maxlength="15"
                                           value="<?php echo $contato_edit ? htmlspecialchars($contato_edit['telefone'] ?? '') : ''; ?>">
                                    <div class="invalid-feedback">
                                        Por favor, informe um telefone válido.
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="origem" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/location.svg" alt="Origem" class="svg-icon size-sm">
                                        Local de Origem:
                                    </label>
                                    <select id="origem" name="origem" class="form-select" required>
                                        <option value="">Selecione a unidade de origem</option>
                                        <?php
                                        // Para edição, carregar todas as unidades e marcar a selecionada
                                        if ($contato_edit):
                                            $origVal = trim($contato_edit['origem'] ?? '');
                                            $unidades = $adminContatos->buscarUnidades();
                                            
                                            // Debug - comentar após resolver
                                            // echo "<!-- DEBUG: Origem do contato: '$origVal', Total unidades: " . count($unidades) . " -->";
                                            
                                            foreach ($unidades as $unidade):
                                                $selected = ($unidade['codigo'] === $origVal) ? 'selected' : '';
                                                $text = htmlspecialchars($unidade['codigo'] . ' - ' . $unidade['nome'] . ' (' . $unidade['cidade'] . ')');
                                                echo "<option value=\"{$unidade['codigo']}\" {$selected}>{$text}</option>";
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor, selecione a unidade de origem.
                                    </div>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="destino" class="form-label fw-semibold">
                                        <img src="../assets/icons/formulario/location.svg" alt="Destino" class="svg-icon size-sm">
                                        Local(is) de Destino:
                                    </label>
                                    <select id="destino" name="destino[]" multiple="multiple" class="form-select" required>
                                        <?php
                                        // Para edição, carregar todas as unidades e marcar as selecionadas
                                        if ($contato_edit):
                                            $destinosVal = trim($contato_edit['destino'] ?? '');
                                            $destinosSelecionados = [];
                                            if (!empty($destinosVal)) {
                                                $destinosSelecionados = array_map('trim', explode(',', $destinosVal));
                                            }
                                            
                                            $unidades = $adminContatos->buscarUnidades();
                                            
                                            // Debug - comentar após resolver
                                            // echo "<!-- DEBUG: Destinos do contato: '$destinosVal', Destinos array: " . implode(', ', $destinosSelecionados) . " -->";
                                            
                                            foreach ($unidades as $unidade):
                                                $selected = in_array($unidade['codigo'], $destinosSelecionados) ? 'selected' : '';
                                                $text = htmlspecialchars($unidade['codigo'] . ' - ' . $unidade['nome'] . ' (' . $unidade['cidade'] . ')');
                                                echo "<option value=\"{$unidade['codigo']}\" {$selected}>{$text}</option>";
                                            endforeach;
                                        endif;
                                        ?>
                                    </select>
                                    <div class="form-text">
                                        <small class="text-muted">Você pode selecionar múltiplos destinos</small>
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor, selecione pelo menos um destino.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <img src="../assets/icons/formulario/check.svg" alt="Salvar" class="svg-icon-white size-sm">
                                    <?php echo $action === 'add' ? 'Criar Contato' : 'Salvar Alterações'; ?>
                                </button>
                                <a href="contatos.php" class="btn btn-danger">
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

        <?php if ($action === 'list' || empty($action)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card sindppenal-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lista de Contatos</h5>
                        <p class="text-muted mb-0">Total: <strong><?php echo $dados['total']; ?> contatos</strong></p>
                    </div>
                    <div class="card-body">
                        <!-- Busca -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="">
                                    <div class="input-group">
                                        <input type="text" 
                                            class="form-control" 
                                            name="search" 
                                            placeholder="Buscar por nome, número funcional, origem ou destino..."
                                            value="<?php echo htmlspecialchars($search); ?>">

                                        <button class="btn btn-primary" type="submit">
                                            <img src="../assets/icons/dashboard/lupa.svg" alt="Buscar" class="svg-icon-white size-sm">
                                            Buscar
                                        </button>

                                        <?php if (!empty($search)): ?>
                                            <a href="contatos.php" class="btn btn-outline-danger">
                                                <img src="../assets/icons/dashboard/cancelar.svg" alt="Limpar" class="svg-icon size-sm">
                                                Limpar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if (empty($dados['contatos'])): ?>
                            <div class="text-center py-5">
                                <p class="text-muted">
                                    <?php echo empty($search) ? 'Nenhum contato encontrado.' : 'Nenhum resultado para sua busca.'; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Nº Funcional</th>
                                            <th>Telefone</th>
                                            <th>Origem</th>
                                            <th>Destino</th>
                                            <th>Data</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dados['contatos'] as $contato): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($contato['nome'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($contato['num_funcional'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($contato['telefone'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($contato['origem_nome'] ?? $contato['origem'] ?? ''); ?></td>
                                            <td>
                                                <?php 
                                                $destinos_resolvidos = $adminContatos->resolverDestinos($contato['destino'] ?? '');
                                                if (strlen($destinos_resolvidos) > 100): 
                                                ?>
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                                          title="<?php echo htmlspecialchars($destinos_resolvidos); ?>">
                                                        <?php echo htmlspecialchars(substr($destinos_resolvidos, 0, 100) . '...'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($destinos_resolvidos); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($contato['created'])); ?></td>
                                            <td>
                                                <div class="d-flex gap-1" role="group">
                                                    <a href="?edit=<?php echo $contato['id']; ?>" class="btn btn-warning btn-sm">
                                                        <img src="../assets/icons/dashboard/editar.svg" alt="Editar" class="svg-icon-white size-sm">
                                                    </a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este contato?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $contato['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <img src="../assets/icons/dashboard/lixo.svg" alt="Excluir" class="svg-icon-white size-sm">
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($dados['pages'] > 1): ?>
                            <nav aria-label="Navegação de páginas" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $dados['pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript específico da página -->
    <script src="../assets/javascript/admin/admin-contatos.js"></script>
    <!-- Máscara de telefone -->
    <script src="../assets/javascript/admin/phone-mask.js"></script>
    
    <!-- Inicializar tooltips -->
    <script>
        $(document).ready(function() {
            // Inicializar tooltips do Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>