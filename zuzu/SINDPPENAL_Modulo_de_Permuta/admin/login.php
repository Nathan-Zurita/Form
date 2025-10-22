<?php
require_once __DIR__ . '/config/autoload.php';

// Se já estiver logado, redirecionar para o dashboard
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header('Location: dashboard.php');
    exit;
}

$mensagem = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $auth = new Auth();
    $resultado = $auth->login($_POST['usuario'], $_POST['senha']);
    
    if ($resultado['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $mensagem = $resultado['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Administração SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card sindppenal-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="h4 mb-0 text-white">
                            Administração
                        </h2>
                        <p class="mb-0 text-white-50">Sistema SINDPPENAL</p>
                    </div>
                    <div class="card-body p-4 sindppenal-form">
                        
                        <?php if (!empty($mensagem)): ?>
                            <div class="alert alert-danger sindppenal-alert"><?php echo htmlspecialchars($mensagem); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">
                                    <img src="../assets/icons/formulario/user.svg" alt="Usuário" class="svg-icon size-sm">
                                    Usuário:
                                </label>
                                <input type="text" id="usuario" name="usuario" class="form-control" required 
                                       placeholder="Digite seu usuário" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    <img src="../assets/icons/dashboard/cadeado.svg" alt="Senha" class="svg-icon size-sm">
                                    Senha:
                                </label>
                                <input type="password" id="senha" name="senha" class="form-control" required 
                                       placeholder="Digite sua senha" value="<?php echo isset($_POST['senha']) ? htmlspecialchars($_POST['senha']) : ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <img src="../assets/icons/formulario/check.svg" alt="Entrar" class="svg-icon-white size-sm">
                                Entrar
                            </button>
                        </form>
                        <div class="text-center">
                            <a href="../index.php" class="text-decoration-none small">
                                <img src="../assets/icons/formulario/form.svg" alt="Voltar" class="svg-icon size-sm">
                                Voltar ao formulário público
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>