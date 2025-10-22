<?php
/**
 * SINDPPENAL - Sistema de Permutação
 * Formulário para criar novos administradores
 */

require_once __DIR__ . '/config/autoload.php';

Auth::requireAuth();

$auth = new Auth();

$mensagem = '';
$tipo_mensagem = '';
$dados = ['usuario' => '', 'senha' => '', 'confirmar_senha' => '', 'nome_completo' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'usuario' => trim($_POST['usuario'] ?? ''),
        'senha' => $_POST['senha'] ?? '',
        'confirmar_senha' => $_POST['confirmar_senha'] ?? '',
        'nome_completo' => trim($_POST['nome_completo'] ?? ''),
        'email' => trim($_POST['email'] ?? '')
    ];
    
    // Validações
    $erros = [];
    
    if (empty($dados['usuario'])) {
        $erros[] = 'O usuário é obrigatório.';
    } elseif (strlen($dados['usuario']) < 3) {
        $erros[] = 'O usuário deve ter pelo menos 3 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $dados['usuario'])) {
        $erros[] = 'O usuário deve conter apenas letras, números e underscore.';
    }
    
    if (empty($dados['senha'])) {
        $erros[] = 'A senha é obrigatória.';
    } elseif (strlen($dados['senha']) < 6) {
        $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
    }
    
    if ($dados['senha'] !== $dados['confirmar_senha']) {
        $erros[] = 'As senhas não coincidem.';
    }
    
    if (empty($dados['nome_completo'])) {
        $erros[] = 'O nome completo é obrigatório.';
    }
    
    if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O e-mail informado não é válido.';
    }
    
    if (empty($erros)) {
        $resultado = $auth->criarAdmin($dados);
        
        if ($resultado['success']) {
            $mensagem = $resultado['message'] . '<br><br><strong>Administrador criado com sucesso!</strong><br><a href="login.php" class="btn btn-sm btn-outline-success mt-2">Fazer Login Agora</a>';
            $tipo_mensagem = 'success';
            $dados = ['usuario' => '', 'senha' => '', 'confirmar_senha' => '', 'nome_completo' => '', 'email' => ''];
        } else {
            $mensagem = $resultado['message'];
            $tipo_mensagem = 'danger';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipo_mensagem = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Administrador - SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg">
    <div class="container-fluid py-4">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <?php if (!empty($mensagem)): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="alert alert-<?php echo $tipo_mensagem; ?> sindppenal-alert alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card sindppenal-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="../assets/icons/dashboard/add.svg" alt="Criar" class="svg-icon-white size-md">
                            Criar Novo Administrador
                        </h5>
                    </div>
                    <div class="card-body sindppenal-form">
                        <form method="POST" action="" id="formCriarAdmin">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">
                                    Usuário:
                                </label>
                                <input type="text" 
                                       id="usuario" 
                                       name="usuario" 
                                       class="form-control" 
                                       required 
                                       placeholder="Digite o usuário"
                                       value="<?php echo htmlspecialchars($dados['usuario'] ?? ''); ?>"
                                       maxlength="50"
                                       pattern="[a-zA-Z0-9_]+"
                                       title="Apenas letras, números e underscore são permitidos">
                            </div>
                            
                            <div class="mb-3">
                                <label for="nome_completo" class="form-label">
                                    Nome Completo:
                                </label>
                                <input type="text" 
                                       id="nome_completo" 
                                       name="nome_completo" 
                                       class="form-control" 
                                       required 
                                       placeholder="Digite o nome completo"
                                       value="<?php echo htmlspecialchars($dados['nome_completo'] ?? ''); ?>"
                                       maxlength="255">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    E-mail:
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="Digite o e-mail"
                                       value="<?php echo htmlspecialchars($dados['email'] ?? ''); ?>"
                                       maxlength="255"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    Senha:
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           id="senha" 
                                           name="senha" 
                                           class="form-control" 
                                           required 
                                           placeholder="Digite a senha"
                                           minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('senha')">
                                        <img src="../assets/icons/dashboard/eye-off.svg" alt="Mostrar senha" class="svg-icon size-sm" id="senha-icon">
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">
                                    Confirmar Senha:
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           id="confirmar_senha" 
                                           name="confirmar_senha" 
                                           class="form-control" 
                                           required 
                                           placeholder="Confirme a senha"
                                           minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmar_senha')">
                                        <img src="../assets/icons/dashboard/eye-off.svg" alt="Mostrar senha" class="svg-icon size-sm" id="confirmar_senha-icon">
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <img src="../assets/icons/formulario/check.svg" alt="Criar" class="svg-icon-white size-sm">
                                Criar Administrador
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript customizado -->
    <script src="../assets/javascript/admin/admin-criar.js"></script>
</body>
</html>
