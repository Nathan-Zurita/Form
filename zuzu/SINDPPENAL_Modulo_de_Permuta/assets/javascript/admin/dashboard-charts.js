/**
 * Configuração dos gráficos do dashboard
 * SINDPPENAL - Sistema de Módulo de Permuta
 */

// Cores base para os gráficos
const baseColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6c757d'];

// Função para gerar cores dinamicamente
function gerarCores(quantidade) {
    const cores = [];
    
    // Primeiro, usar as cores base (padrão do sistema)
    for (let i = 0; i < quantidade && i < baseColors.length; i++) {
        cores.push(baseColors[i]);
    }
    
    // Se precisar de mais cores, gerar dinamicamente usando HSL
    // Usa o número áureo (137.508°) para distribuição uniforme no círculo de cores
    for (let i = baseColors.length; i < quantidade; i++) {
        const hue = (i * 137.508) % 360; // Matiz variando de 0 a 360°
        const saturation = 70; // Saturação fixa para cores vibrantes
        const lightness = 50; // Luminosidade fixa para boa visibilidade
        cores.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
    }
    
    return cores;
}

// Função para criar gráfico de destinos
function criarGraficoDestinos(labels, values) {
    if (!labels || !values || labels.length === 0) {
        return;
    }

    const destinosCtx = document.getElementById('destinosChart');
    if (!destinosCtx) {
        console.error('Elemento destinosChart não encontrado');
        return;
    }

    const cores = gerarCores(labels.length);

    new Chart(destinosCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantidade de Contatos',
                data: values,
                backgroundColor: cores,
                borderColor: cores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '50%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Função para criar gráfico de cidades
function criarGraficoCidades(labels, values) {
    if (!labels || !values || labels.length === 0) {
        return;
    }

    const cidadesCtx = document.getElementById('cidadesChart');
    if (!cidadesCtx) {
        console.error('Elemento cidadesChart não encontrado');
        return;
    }

    const cores = gerarCores(labels.length);

    new Chart(cidadesCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantidade de Unidades',
                data: values,
                backgroundColor: cores,
                borderColor: cores,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}

// Expor funções globalmente
window.DashboardCharts = {
    criarGraficoDestinos: criarGraficoDestinos,
    criarGraficoCidades: criarGraficoCidades
};