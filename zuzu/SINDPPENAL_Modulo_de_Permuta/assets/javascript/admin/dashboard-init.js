/**
 * Inicialização dos gráficos do dashboard
 * SINDPPENAL - Sistema de Módulo de Permuta
 */

// Função para inicializar os gráficos com dados do backend
function inicializarGraficos(dadosDestinos, dadosCidades) {
    // Verificar se o módulo de gráficos está disponível
    if (!window.DashboardCharts) {
        console.error('Módulo DashboardCharts não encontrado');
        return;
    }

    // Inicializar gráfico de destinos
    if (dadosDestinos && dadosDestinos.labels && dadosDestinos.values && dadosDestinos.labels.length > 0) {
        window.DashboardCharts.criarGraficoDestinos(dadosDestinos.labels, dadosDestinos.values);
    }

    // Inicializar gráfico de cidades
    if (dadosCidades && dadosCidades.labels && dadosCidades.values && dadosCidades.labels.length > 0) {
        window.DashboardCharts.criarGraficoCidades(dadosCidades.labels, dadosCidades.values);
    }
}

// Expor função globalmente
window.inicializarGraficos = inicializarGraficos;