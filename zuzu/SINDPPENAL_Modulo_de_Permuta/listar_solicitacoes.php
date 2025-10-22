<?php
require_once 'processar_formulario.php';
require_once __DIR__ . '/admin/config/autoload_public.php';

$solicitacao = new SolicitacaoPermuta();
$contatos = $solicitacao->listarTodas();

// Instanciar AdminContatos para usar as funções de resolução
$adminContatos = new AdminContatos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contatos de Permuta - SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/sindppenal-theme.css">
    <link rel="stylesheet" href="./assets/css/listar-solicitacoes.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg" style="background-image: url('./assets/images/background-verde.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header text-center bg-primary text-white">
                        <h2 class="mb-0">
                            <img src="./assets/icons/dashboard/Users.svg" alt="Contatos" class="svg-icon-white size-lg">
                            Contatos de Permuta
                        </h2>
                        <a href="./admin/index.php" class="btn btn-light btn-sm mt-2">
                            <img src="./assets/icons/dashboard/seta.svg" alt="Voltar" class="svg-icon size-sm">
                            Voltar ao dashboard
                        </a>
                    </div>
                    <div class="card-body">

                        <?php if (empty($contatos)): ?>
                            <div class="text-center py-5">
                                <p class="text-muted">Nenhum contato encontrado.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($contatos as $contato): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5 class="card-title text-sindppenal mb-0">
                                                <img src="./assets/icons/formulario/User.svg" alt="Nome" class="svg-icon size-sm">
                                                <?php echo htmlspecialchars($contato['nome']); ?>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">
                                                <img src="./assets/icons/formulario/card.svg" alt="Número" class="svg-icon size-sm">
                                                <strong>Número Funcional:</strong> <?php echo htmlspecialchars($contato['num_funcional']); ?><br>
                                                <img src="./assets/icons/formulario/Phone.svg" alt="Telefone" class="svg-icon size-sm">
                                                <strong>Telefone:</strong> <?php echo htmlspecialchars($contato['telefone'] ?: 'Não informado'); ?><br>
                                                <img src="./assets/icons/dashboard/Eye.svg" alt="Origem" class="svg-icon size-sm">
                                                <strong>Origem:</strong> <?php echo htmlspecialchars($adminContatos->resolverUnidade($contato['origem'])); ?><br>
                                                <img src="./assets/icons/formulario/combination.svg" alt="Destino" class="svg-icon size-sm">
                                                <strong>Destino:</strong> <span class="text-success fw-bold"><?php echo htmlspecialchars($adminContatos->resolverDestinos($contato['destino'])); ?></span>
                                            </p>
                                        </div>
                                        <div class="card-footer text-muted small">
                                            Data: <?php 
                                                $data = $contato['created'] ?? $contato['data_cadastro'] ?? null;
                                                echo $data ? date('d/m/Y H:i', strtotime($data)) : 'Não informado';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>