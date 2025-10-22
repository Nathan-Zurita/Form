<?php
require_once __DIR__ . '/config/autoload.php';

Auth::requireAuth();

$adminContatos = new AdminContatos();
$combinacoes = $adminContatos->buscarCombinacoes();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combinações de Permuta - Administração SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg">
    <div class="container-fluid py-4">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <!-- Stats Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card sindppenal-card">
                    <div class="card-body text-center">
                        <div class="display-4 text-success fw-bold"><?php echo count($combinacoes); ?></div>
                        <p class="card-text text-muted">Combinações Encontradas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matches Content -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($combinacoes)): ?>
                <div class="card sindppenal-card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h4 class="text-muted">Nenhuma combinação encontrada</h4>
                        <p class="text-muted">Ainda não há contatos com origens e destinos que se combinem.</p>
                        <p class="text-muted">Quando houver pessoas interessadas em trocar de locais de trabalho mutuamente, elas aparecerão aqui.</p>
                        
                        <div class="mt-4">
                            <a href="contatos.php?action=add" class="btn btn-primary">
                                <img src="../assets/icons/dashboard/add.svg" alt="Adicionar" class="svg-icon-white size-sm">
                                Adicionar Primeiro Contato
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                
                <?php foreach ($combinacoes as $index => $match): ?>
                <div class="card sindppenal-card mb-4"">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0 text-center">
                            <img src="../assets/icons/formulario/combination.svg" alt="Combinação" class="svg-icon size-sm">
                            Combinação #<?php echo $index + 1; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Pessoa 1 -->
                            <div class="col-lg-5 col-md-12 mb-3 mb-lg-0">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-sindppenal"><?php echo htmlspecialchars($match['nome1']); ?></h6>
                                        <p class="card-text">
                                            <strong>Funcional:</strong> <?php echo htmlspecialchars($match['func1']); ?><br>
                                            <?php if (!empty($match['tel1'])): ?>
                                            <strong>Telefone:</strong> <?php echo htmlspecialchars($match['tel1']); ?><br>
                                            <?php endif; ?>
                                            <strong>Trabalha em:</strong> <?php echo htmlspecialchars($match['origem1']); ?><br>
                                            <strong>Quer ir para:</strong> <?php echo htmlspecialchars($match['destinos1']); ?><br>
                                            <small class="text-muted"><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($match['data1'])); ?></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seta de Match -->
                            <div class="col-lg-2 col-md-12 text-center mb-3 mb-lg-0">
                                <div class="py-3">
                                    <div class="fs-1 text-success">⇄</div>
                                    <div class="badge bg-success fs-6 mt-2">
                                        <?php echo htmlspecialchars($match['match_unidade']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pessoa 2 -->
                            <div class="col-lg-5 col-md-12">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-sindppenal"><?php echo htmlspecialchars($match['nome2']); ?></h6>
                                        <p class="card-text">
                                            <strong>Funcional:</strong> <?php echo htmlspecialchars($match['func2']); ?><br>
                                            <?php if (!empty($match['tel2'])): ?>
                                            <strong>Telefone:</strong> <?php echo htmlspecialchars($match['tel2']); ?><br>
                                            <?php endif; ?>
                                            <strong>Trabalha em:</strong> <?php echo htmlspecialchars($match['origem2']); ?><br>
                                            <strong>Quer ir para:</strong> <?php echo htmlspecialchars($match['destinos2']); ?><br>
                                            <small class="text-muted"><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($match['data2'])); ?></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Info Box -->
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>