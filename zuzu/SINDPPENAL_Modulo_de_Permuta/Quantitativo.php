<?php
session_start();

try {
    require_once __DIR__ . '/admin/config/autoload.php';
    $adminContatos = new AdminContatos();
    $adminUnidades = new AdminUnidades();
    $stats = $adminContatos->getEstatisticas();
    $statsUnidades = $adminUnidades->getEstatisticas();
} catch (Exception $e) {
    $stats = ['destinos_populares' => []];
    $statsUnidades = ['cidades_populares' => []];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantitativo de Interessados - SINDPPENAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/sindppenal-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body class="sindppenal-bg" style="background-image: url('./assets/images/background-verde.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card sindppenal-card mb-4">
                    <div class="card-body text-center sindppenal-header">
                        <h1 class="h3 text-sindppenal mb-1">
                            <i class="bi bi-bar-chart-fill me-2"></i>
                            Quantitativo de Interessados em Permuta
                        </h1>
                        <p class="text-muted mb-0">SINDPPENAL - Sindicato dos Policiais Penais e Servidores do Sistema Penitenciário do Estado do Espírito Santo</p>
                    </div>
                </div>
                <div class="card sindppenal-card mt-3">
                    <div class="card-body text-center p-3">
                        <a href="index.php" class="btn btn-outline-primary">
                            <img src="./assets/icons/formulario/form.svg" alt="Formulário" class="svg-icon size-sm me-2">
                            Preencher Formulário de Permuta
                        </a>
                    </div>
                </div>
                <br>
                <div class="card sindppenal-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="./assets/icons/formulario/form.svg" alt="quantitativo" class="svg-icon-white size-md">
                            Quantitativo de Interessados
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Veja os destinos e cidades mais procurados pelos policiais penais para permuta.</p>
                        
                        <div class="row">
                            <?php if (!empty($stats['destinos_populares'])): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card sindppenal-card h-100">
                                    <div class="card-header bg-sindppenal text-white">
                                        <h6 class="card-title mb-0 text-white">
                                            <img src="./assets/icons/dashboard/graph-2.svg" alt="destinos" class="svg-icon-white size-sm">
                                            Destinos Mais Procurados
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach (array_slice($stats['destinos_populares'], 0, 1000) as $destino): ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                            <span class="fw-medium text-dark"><?php echo htmlspecialchars($destino['destino']); ?></span>
                                            <span class="badge bg-sindppenal"><?php echo $destino['total']; ?> interessados</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($statsUnidades['cidades_populares'])): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card sindppenal-card h-100">
                                    <div class="card-header bg-sindppenal text-white">
                                        <h6 class="card-title mb-0 text-white">
                                            <img src="./assets/icons/formulario/location.svg" alt="cidades" class="svg-icon-white size-sm">
                                            Cidades com Mais Unidades
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach (array_slice($statsUnidades['cidades_populares'], 0, 1000) as $cidade): ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                            <span class="fw-medium text-dark"><?php echo htmlspecialchars($cidade['cidade']); ?></span>
                                            <span class="badge bg-sindppenal"><?php echo $cidade['total']; ?> unidade(s)</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($stats['destinos_populares']) && empty($statsUnidades['cidades_populares'])): ?>
                        <div class="text-center py-5">
                            <img src="./assets/icons/formulario/search.svg" alt="Sem dados" class="svg-icon size-xl mb-3 opacity-50">
                            <h5 class="text-muted">Ainda não há dados suficientes</h5>
                            <p class="text-muted">Os quantitativos serão exibidos conforme mais pessoas se cadastrarem no sistema.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./assets/javascript/public/formulario.js"></script>
</body>
</html>