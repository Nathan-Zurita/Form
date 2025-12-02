<?php
require_once __DIR__ . '/config/autoload.php';

Auth::requireAuth();xcvhxhcjkvghxcvjhcjv

$adminContatos = new AdminContatos();
$adminUnidades = new AdminUnidades();
$stats = $adminContatos->getEstatisticas();
$statsUnidades = $adminUnidades->getEstatisticas();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administração SINDPPENAL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sindppenal-theme.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="sindppenal-bg">
    <div class="container-fluid py-4">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <!-- Stats and Actions Row -->
        <div class="row mb-4">
            <!-- Stats Cards -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="display-4 text-sindppenal fw-bold"><?php echo $stats['total_contatos'] ?? 0; ?></div>
                        <p class="card-text text-muted mb-0">Total de Contatos</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="display-4 text-primary fw-bold"><?php echo $statsUnidades['total_unidades'] ?? 0; ?></div>
                        <p class="card-text text-muted mb-0">Total de Unidades</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="display-4 text-success fw-bold"><?php echo $statsUnidades['unidades_ativas'] ?? 0; ?></div>
                        <p class="card-text text-muted mb-0">Unidades <span class="text-success">Ativas</span></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="display-4 text-danger fw-bold"><?php echo $statsUnidades['unidades_inativas'] ?? 0; ?></div>
                        <p class="card-text text-muted mb-0">Unidades <span class="text-danger">Inativas</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card sindppenal-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="../assets/icons/dashboard/fast.svg" alt="Ações Rápidas" class="svg-icon-white size-md">
                            Ações Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 sindppenal-btn-group">
                            <div class="col-lg-3 col-md-6">
                                <a href="contatos.php?action=add" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                    <div class="fw-bold">
                                        <img src="../assets/icons/dashboard/add.svg" alt="Adicionar" class="svg-icon size-md">
                                        Adicionar Contato
                                    </div>
                                    <small class="text-muted">Cadastrar novo contato</small>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <a href="contatos.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                    <div class="fw-bold">
                                        <img src="../assets/icons/dashboard/list.svg" alt="Listar" class="svg-icon size-md">
                                        Listar Contatos
                                    </div>
                                    <small class="text-muted">Ver todos os contatos</small>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <a href="unidades.php?action=add" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                    <div class="fw-bold">
                                        <img src="../assets/icons/dashboard/unidade.svg" alt="Adicionar Unidade" class="svg-icon size-md">
                                        Adicionar Unidade
                                    </div>
                                    <small class="text-muted">Cadastrar nova unidade</small>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <a href="combinacoes.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column justify-content-center align-items-center py-3">
                                    <div class="fw-bold">
                                        <img src="../assets/icons/dashboard/users-2.svg" alt="Combinações" class="svg-icon size-md">
                                        Ver Combinações
                                    </div>
                                    <small class="text-muted">Verificar matches</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Gráfico dos Destinos -->
            <div class="col-lg-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="../assets/icons/dashboard/graph-2.svg" alt="gráfico destinos" class="svg-icon-white size-md">
                            Gráfico - Destinos Mais Procurados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="destinosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico das Cidades -->
            <div class="col-lg-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="../assets/icons/formulario/location.svg" alt="gráfico cidades" class="svg-icon-white size-md">
                            Gráfico - Cidades com Mais Unidades
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="cidadesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row">
            <!-- Popular Destinations -->
            <?php if (!empty($stats['destinos_populares'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="../assets/icons/dashboard/graph-2.svg" alt="destinos" class="svg-icon-white size-md">
                            Destinos Mais Procurados
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($stats['destinos_populares'] as $destino): ?>
                        <a href="contatos.php?search=<?php echo urlencode($destino['destino']); ?>" class="dashboard-link-item">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-sindppenal hover-item rounded">
                                <span class="fw-medium text-dark"><?php echo htmlspecialchars($destino['destino']); ?></span>
                                <span class="badge bg-sindppenal"><?php echo $destino['total']; ?> contatos</span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($statsUnidades['cidades_populares'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="card sindppenal-card h-100">
                    <div class="bg-primary card-header text-white">
                        <h5 class="card-title mb-0 text-white">
                            <img src="../assets/icons/formulario/location.svg" alt="cidades" class="svg-icon-white size-md">
                            Cidades com Mais Unidades
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($statsUnidades['cidades_populares'] as $cidade): ?>
                        <a href="contatos.php?search=<?php echo urlencode($cidade['cidade']); ?>" class="dashboard-link-item">
                            <div class="align-items-center border-bottom border-sindppenal d-flex justify-content-between py-2 hover-item rounded">
                                <span class="fw-medium text-dark"><?php echo htmlspecialchars($cidade['cidade']); ?></span>
                                <span class="badge bg-sindppenal"><?php echo $cidade['total']; ?> unidade(s)</span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/javascript/admin/admin-common.js"></script>
    <script src="../assets/javascript/admin/dashboard-charts.js"></script>
    <script src="../assets/javascript/admin/dashboard-init.js"></script>

    <script>
        // Dados dos gráficos vindos do PHP
        document.addEventListener('DOMContentLoaded', function() {
            const dadosDestinos = {
                labels: <?php echo !empty($stats['destinos_populares']) ? json_encode(array_map(function($d) { return $d['destino']; }, array_slice($stats['destinos_populares'], 0, 10))) : '[]'; ?>,
                values: <?php echo !empty($stats['destinos_populares']) ? json_encode(array_map(function($d) { return $d['total']; }, array_slice($stats['destinos_populares'], 0, 10))) : '[]'; ?>
            };

            const dadosCidades = {
                labels: <?php echo !empty($statsUnidades['cidades_populares']) ? json_encode(array_map(function($c) { return $c['cidade']; }, array_slice($statsUnidades['cidades_populares'], 0, 10))) : '[]'; ?>,
                values: <?php echo !empty($statsUnidades['cidades_populares']) ? json_encode(array_map(function($c) { return $c['total']; }, array_slice($statsUnidades['cidades_populares'], 0, 10))) : '[]'; ?>
            };

            // Inicializar gráficos
            if (window.inicializarGraficos) {
                window.inicializarGraficos(dadosDestinos, dadosCidades);
            }
        });
    </script>

</body>
</html>