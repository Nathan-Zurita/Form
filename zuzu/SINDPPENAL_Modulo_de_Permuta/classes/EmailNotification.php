<?php
/**
 * Classe para gerenciar notificações por email usando PHPMailer + Mailtrap SMTP
 */

// Carregar o autoloader do Composer para PHPMailer
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailNotification {
    private $adminEmail;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    
    public function __construct() {
        // Email do administrador - atualizado para Nathan Zurita Barreto
        $this->adminEmail = 'nathanzuritabarreto2@gmail.com';
        
        // Configuração SMTP do Mailtrap atualizada
        $this->smtpHost = 'sandbox.smtp.mailtrap.io';
        $this->smtpPort = 2525;
        $this->smtpUsername = '3b857ad7432601';
        $this->smtpPassword = '0a7108a80a1d5b';
    }
    
    /**
     * Envia notificação de nova combinação por email
     */
    public function enviarNotificacaoCombinacao($combinacao) {
        $assunto = '[SINDPPENAL] Nova Combinação de Permuta Encontrada!';
        
        $corpo = $this->montarCorpoEmail($combinacao);
        
        return $this->enviarEmail($this->adminEmail, $assunto, $corpo);
    }
    
    /**
     * Envia notificação para o filiado sobre nova combinação encontrada
     */
    public function enviarNotificacaoFiliado($emailFiliado, $nomeFiliado, $combinacao) {
        $assunto = '[SINDPPENAL] Combinação de Permuta Encontrada para Você!';
        
        $corpo = $this->montarCorpoEmailFiliado($nomeFiliado, $combinacao);
        
        return $this->enviarEmail($emailFiliado, $assunto, $corpo);
    }
    
    /**
     * Envia notificações automáticas quando uma nova combinação é encontrada
     * Envia para o administrador (completo) e para os filiados envolvidos (simplificado)
     */
    public function enviarNotificacoesAutomaticas($combinacao) {
        $resultados = [];
        
        // 1. Enviar para o administrador (email completo)
        $resultadoAdmin = $this->enviarNotificacaoCombinacao($combinacao);
        $resultados['admin'] = $resultadoAdmin;
        
        // 2. Enviar para o filiado 1 (se tiver email)
        if (!empty($combinacao['email1'])) {
            $resultadoFiliado1 = $this->enviarNotificacaoFiliado(
                $combinacao['email1'], 
                $combinacao['nome1'], 
                $combinacao
            );
            $resultados['filiado1'] = $resultadoFiliado1;
        }
        
        // 3. Enviar para o filiado 2 (se tiver email)
        if (!empty($combinacao['email2'])) {
            $resultadoFiliado2 = $this->enviarNotificacaoFiliado(
                $combinacao['email2'], 
                $combinacao['nome2'], 
                $combinacao
            );
            $resultados['filiado2'] = $resultadoFiliado2;
        }
        
        return $resultados;
    }
    
    /**
     * Carrega um template HTML e substitui as variáveis
     */
    private function carregarTemplate($nomeTemplate, $variaveis = []) {
        $caminhoTemplate = __DIR__ . '/../templates/email/' . $nomeTemplate . '.html';
        
        if (!file_exists($caminhoTemplate)) {
            throw new Exception("Template não encontrado: {$nomeTemplate}");
        }
        
        $conteudo = file_get_contents($caminhoTemplate);
        
        // Substituir as variáveis no template
        foreach ($variaveis as $chave => $valor) {
            $conteudo = str_replace('{{' . $chave . '}}', $valor, $conteudo);
        }
        
        return $conteudo;
    }
    
    /**
     * Monta o corpo do email para uma combinação usando template
     */
    private function montarCorpoEmail($combinacao) {
        $data = date('d/m/Y');
        
        // Preparar telefones (se existirem)
        $telefone1 = '';
        if (!empty($combinacao['tel1']) || !empty($combinacao['telefone1'])) {
            $tel1 = $combinacao['tel1'] ?? $combinacao['telefone1'] ?? '';
            $telefone1 = "<strong>Telefone:</strong> {$tel1}<br>";
        }
        
        $telefone2 = '';
        if (!empty($combinacao['tel2']) || !empty($combinacao['telefone2'])) {
            $tel2 = $combinacao['tel2'] ?? $combinacao['telefone2'] ?? '';
            $telefone2 = "<strong>Telefone:</strong> {$tel2}<br>";
        }
        
        $variaveis = [
            'DATA' => $data,
            'NOME1' => htmlspecialchars($combinacao['nome1']),
            'FUNCIONAL1' => htmlspecialchars($combinacao['func1'] ?? $combinacao['num_funcional1'] ?? 'N/A'),
            'TELEFONE1' => $telefone1,
            'EMAIL1' => htmlspecialchars($combinacao['email1'] ?? 'Não informado'),
            'ORIGEM1' => htmlspecialchars($combinacao['origem1']),
            'DESTINOS1' => htmlspecialchars($combinacao['destinos1'] ?? $combinacao['destino1'] ?? 'N/A'),
            'NOME2' => htmlspecialchars($combinacao['nome2']),
            'FUNCIONAL2' => htmlspecialchars($combinacao['func2'] ?? $combinacao['num_funcional2'] ?? 'N/A'),
            'TELEFONE2' => $telefone2,
            'EMAIL2' => htmlspecialchars($combinacao['email2'] ?? 'Não informado'),
            'ORIGEM2' => htmlspecialchars($combinacao['origem2']),
            'DESTINOS2' => htmlspecialchars($combinacao['destinos2'] ?? $combinacao['destino2'] ?? 'N/A')
        ];
        
        return $this->carregarTemplate('combinacao-admin', $variaveis);
    }
    
    /**
     * Monta o corpo do email para o filiado (versão simplificada) usando template
     */
    private function montarCorpoEmailFiliado($nomeFiliado, $combinacao) {
        $data = date('d/m/Y');
        
        // Determinar a origem do filiado (sem revelar informações da outra pessoa)
        $minhaOrigem = ($combinacao['nome1'] === $nomeFiliado) ? $combinacao['origem1'] : $combinacao['origem2'];
        
        $variaveis = [
            'NOME_FILIADO' => htmlspecialchars($nomeFiliado),
            'MINHA_ORIGEM' => htmlspecialchars($minhaOrigem),
            'DATA' => $data
        ];
        
        return $this->carregarTemplate('combinacao-filiado', $variaveis);
    }
    
    /**
     * Envia o email usando PHPMailer com SMTP do Mailtrap
     */
    private function enviarEmail($destinatario, $assunto, $corpo) {
        try {
            // Criar instância do PHPMailer com novas credenciais
            $phpmailer = new PHPMailer();
            $phpmailer->isSMTP();
            $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = 2525;
            $phpmailer->Username = '3b857ad7432601';
            $phpmailer->Password = '0a7108a80a1d5b';
            $phpmailer->CharSet = 'UTF-8';
            
            // Configurar remetente e destinatário
            $phpmailer->setFrom('noreply@sindppenal.org.br', 'Sistema SINDPPENAL');
            $phpmailer->addAddress($destinatario);
            
            // Configurar o email
            $phpmailer->isHTML(true);
            $phpmailer->Subject = $assunto;
            $phpmailer->Body = $corpo;
            
            // Enviar o email
            $phpmailer->send();
            
            error_log("Email enviado com sucesso via Mailtrap para: {$destinatario}");
            return [
                'success' => true,
                'message' => 'Email enviado com sucesso via Mailtrap'
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email via Mailtrap: " . $e->getMessage());
            
            // Fallback para PHP mail() nativo
            error_log("Tentando fallback com PHP mail()");
            return $this->enviarEmailFallback($destinatario, $assunto, $corpo);
        }
    }
    
    /**
     * Configura o email do administrador
     */
    public function setAdminEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->adminEmail = $email;
            return true;
        }
        return false;
    }
    
    /**
     * Obtém o email do administrador configurado
     */
    public function getAdminEmail() {
        return $this->adminEmail;
    }
    
    /**
     * Configura as credenciais SMTP do Mailtrap
     */
    public function setSmtpCredentials($host, $port, $username, $password) {
        $this->smtpHost = $host;
        $this->smtpPort = $port;
        $this->smtpUsername = $username;
        $this->smtpPassword = $password;
    }
    
    /**
     * Obtém informações das credenciais SMTP (mascaradas para segurança)
     */
    public function getSmtpInfo() {
        return [
            'host' => $this->smtpHost,
            'port' => $this->smtpPort,
            'username' => substr($this->smtpUsername, 0, 4) . '...',
            'password' => '***',
            'configured' => $this->isSmtpConfigured()
        ];
    }
    
    /**
     * Envia email usando a função mail() nativa como fallback
     */
    private function enviarEmailFallback($destinatario, $assunto, $corpo) {
        try {
            // Headers para email HTML
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: Sistema SINDPPENAL <noreply@sindppenal.org.br>',
                'Reply-To: noreply@sindppenal.org.br',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $headersString = implode("\r\n", $headers);
            
            // Tentar enviar o email
            $resultado = mail($destinatario, $assunto, $corpo, $headersString);
            
            if ($resultado) {
                error_log("Email enviado com sucesso via PHP mail() para: {$destinatario}");
                return [
                    'success' => true,
                    'message' => 'Email enviado com sucesso via PHP mail()'
                ];
            } else {
                error_log("Falha ao enviar email via PHP mail() para: {$destinatario}");
                return [
                    'success' => false,
                    'message' => 'Falha ao enviar email via PHP mail()'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email via PHP mail(): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar se o SMTP está configurado
     */
    public function isSmtpConfigured() {
        return !empty($this->smtpHost) && !empty($this->smtpUsername) && !empty($this->smtpPassword);
    }

    /**
     * Verificar conexão SMTP
     */
    public function verificarConexaoSmtp() {
        if (!$this->isSmtpConfigured()) {
            return [
                'success' => false,
                'message' => 'SMTP não está configurado'
            ];
        }

        try {
            $mail = new PHPMailer(true);
            
            // Configuração do servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            $mail->Timeout = 10; // Timeout de 10 segundos para teste
            
            // Testar conexão
            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                return [
                    'success' => true,
                    'message' => 'Conexão SMTP estabelecida com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Falha ao conectar com o servidor SMTP'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão SMTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envia um relatório consolidado com todas as combinações para o administrador
     */
    public function enviarRelatorioConsolidado($combinacoes) {
        if (empty($combinacoes)) {
            return [
                'success' => false,
                'message' => 'Não há combinações para enviar no relatório.'
            ];
        }

        $assunto = "Relatório Consolidado de Combinações de Permuta - " . date('d/m/Y');
        $corpo = $this->montarCorpoRelatorioConsolidado($combinacoes);
        
        return $this->enviarEmail($this->adminEmail, $assunto, $corpo);
    }

    /**
     * Monta o corpo do email do relatório consolidado usando template
     */
    private function montarCorpoRelatorioConsolidado($combinacoes) {
        $totalCombinacoes = count($combinacoes);
        $dataHora = date('d/m/Y');
        
        // Gerar HTML das combinações
        $combinacoesHtml = '';
        foreach ($combinacoes as $index => $combinacao) {
            $numeroCombi = $index + 1;
            
            // Dados da pessoa 1
            $nome1 = htmlspecialchars($combinacao['nome1'] ?? 'Nome não informado');
            $funcional1 = htmlspecialchars($combinacao['func1'] ?? $combinacao['num_funcional1'] ?? 'N/A');
            $telefone1 = '';
            if (!empty($combinacao['tel1']) || !empty($combinacao['telefone1'])) {
                $tel1 = htmlspecialchars($combinacao['tel1'] ?? $combinacao['telefone1'] ?? '');
                $telefone1 = "<strong>Telefone:</strong> {$tel1}<br>";
            }
            $email1 = htmlspecialchars($combinacao['email1'] ?? 'Não informado');
            $origem1 = htmlspecialchars($combinacao['origem1'] ?? 'N/A');
            $destinos1 = htmlspecialchars($combinacao['destinos1'] ?? $combinacao['destino1'] ?? 'N/A');
            $data1 = date('d/m/Y H:i', strtotime($combinacao['data1'] ?? $combinacao['created'] ?? date('Y-m-d H:i:s')));
            
            // Dados da pessoa 2
            $nome2 = htmlspecialchars($combinacao['nome2'] ?? 'Nome não informado');
            $funcional2 = htmlspecialchars($combinacao['func2'] ?? $combinacao['num_funcional2'] ?? 'N/A');
            $telefone2 = '';
            if (!empty($combinacao['tel2']) || !empty($combinacao['telefone2'])) {
                $tel2 = htmlspecialchars($combinacao['tel2'] ?? $combinacao['telefone2'] ?? '');
                $telefone2 = "<strong>Telefone:</strong> {$tel2}<br>";
            }
            $email2 = htmlspecialchars($combinacao['email2'] ?? 'Não informado');
            $origem2 = htmlspecialchars($combinacao['origem2'] ?? 'N/A');
            $destinos2 = htmlspecialchars($combinacao['destinos2'] ?? $combinacao['destino2'] ?? 'N/A');
            $data2 = date('d/m/Y H:i', strtotime($combinacao['data2'] ?? $combinacao['created'] ?? date('Y-m-d H:i:s')));
            
            // Match entre unidades
            $matchUnidade = htmlspecialchars($combinacao['match_unidade'] ?? "{$origem1} ↔ {$origem2}");
            
            $variaveisItem = [
                'NUMERO_COMBINACAO' => $numeroCombi,
                'NOME1' => $nome1,
                'FUNCIONAL1' => $funcional1,
                'TELEFONE1' => $telefone1,
                'EMAIL1' => $email1,
                'ORIGEM1' => $origem1,
                'DESTINOS1' => $destinos1,
                'DATA1' => $data1,
                'MATCH_UNIDADE' => $matchUnidade,
                'NOME2' => $nome2,
                'FUNCIONAL2' => $funcional2,
                'TELEFONE2' => $telefone2,
                'EMAIL2' => $email2,
                'ORIGEM2' => $origem2,
                'DESTINOS2' => $destinos2,
                'DATA2' => $data2
            ];
            
            $combinacoesHtml .= $this->carregarTemplate('relatorio-item', $variaveisItem);
        }
        
        $variaveis = [
            'DATA_HORA' => $dataHora,
            'TOTAL_COMBINACOES' => $totalCombinacoes,
            'COMBINACOES_HTML' => $combinacoesHtml
        ];
        
        return $this->carregarTemplate('relatorio-consolidado', $variaveis);
    }
}