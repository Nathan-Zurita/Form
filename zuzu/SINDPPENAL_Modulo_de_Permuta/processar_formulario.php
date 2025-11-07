<?php
/**
 * Processa o formulário de solicitação de permuta
 */

session_start();

require_once __DIR__ . '/admin/config/autoload_public.php';

class SolicitacaoPermuta {
    private $conn;
    private $table_name = "contatos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Salva uma nova solicitação no banco de dados
     */
    public function salvar($dados) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (nome, num_funcional, telefone, email, sexo, origem, destino, created, updated) 
                     VALUES (:nome, :num_funcional, :telefone, :email, :sexo, :origem, :destino, :created, :updated)";

            $stmt = $this->conn->prepare($query);

            // Sanitizar os dados
            $nome = htmlspecialchars(strip_tags($dados['nome']));
            $num_funcional = htmlspecialchars(strip_tags($dados['num_funcional']));
            $telefone = isset($dados['telefone']) ? htmlspecialchars(strip_tags($dados['telefone'])) : '';
            $email = isset($dados['email']) ? filter_var($dados['email'], FILTER_SANITIZE_EMAIL) : '';
            $sexo = isset($dados['sexo']) && in_array($dados['sexo'], ['M', 'F']) ? $dados['sexo'] : null;
            
            // Origem é única (string simples)
            $origem = isset($dados['origem']) ? htmlspecialchars(strip_tags($dados['origem'])) : '';
            
            // Converter array de destinos para string separada por vírgula
            $destino = '';
            if (isset($dados['destino']) && is_array($dados['destino'])) {
                $destino = implode(', ', $dados['destino']);
            } elseif (isset($dados['destino'])) {
                $destino = $dados['destino'];
            }

            $created = date('Y-m-d H:i:s');
            $updated = date('Y-m-d H:i:s');

            // Bind dos parâmetros
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':num_funcional', $num_funcional);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':sexo', $sexo);
            $stmt->bindParam(':origem', $origem);
            $stmt->bindParam(':destino', $destino);
            $stmt->bindParam(':created', $created);
            $stmt->bindParam(':updated', $updated);

            if ($stmt->execute()) {
                $novoId = $this->conn->lastInsertId();
                
                // Verificar se há novas combinações após inserir este contato
                $this->verificarENotificarNovasCombinacoes($novoId);
                
                return [
                    'success' => true,
                    'tipo' => 'success',
                    'texto' => 'Sua solicitação de permuta foi enviada com sucesso! Em breve entraremos em contato.'
                ];
            } else {
                return [
                    'success' => false,
                    'tipo' => 'danger',
                    'texto' => 'Erro ao salvar a solicitação no banco de dados.'
                ];
            }

        } catch (PDOException $e) {
            return [
                'success' => false,
                'tipo' => 'danger',
                'texto' => 'Erro no banco de dados. Tente novamente.'
            ];
        }
    }

    /**
     * Lista todas as solicitações
     */
    public function listarTodas() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY created DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca solicitação por número funcional
     */
    public function buscarPorNumeroFuncional($num_funcional) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE num_funcional = :num_funcional 
                     ORDER BY created DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':num_funcional', $num_funcional);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Verifica se há novas combinações envolvendo o contato recém-inserido
     * e envia notificação por email se encontrar
     */
    private function verificarENotificarNovasCombinacoes($novoContatoId) {
        try {
            // Instanciar AdminContatos para usar suas funcionalidades
            $adminContatos = new AdminContatos();
            
            // Buscar o contato recém-inserido
            $novoContato = $adminContatos->buscarPorId($novoContatoId);
            if (!$novoContato) {
                error_log("Novo contato não encontrado com ID: {$novoContatoId}");
                return;
            }
            
            // Buscar todas as combinações atuais
            $todasCombinacoes = $adminContatos->buscarCombinacoes();
            
            // Filtrar apenas as combinações que envolvem o novo contato
            $novasCombinacoes = [];
            foreach ($todasCombinacoes as $combinacao) {
                // Comparar por ID do contato é mais confiável que por nome
                $idContato1 = $combinacao['id1'] ?? null;
                $idContato2 = $combinacao['id2'] ?? null;
                
                if ($idContato1 == $novoContatoId || $idContato2 == $novoContatoId) {
                    $novasCombinacoes[] = $combinacao;
                }
            }
            
            // Se encontrou combinações, enviar notificações automáticas
            if (!empty($novasCombinacoes)) {
                $emailNotification = new EmailNotification();
                
                foreach ($novasCombinacoes as $combinacao) {
                    // Enviar notificações automáticas (admin + filiados)
                    $resultados = $emailNotification->enviarNotificacoesAutomaticas($combinacao);
                    
                    // Log dos resultados
                    if ($resultados['admin']['success']) {
                        error_log("Notificação completa enviada ao administrador para combinação envolvendo contato ID {$novoContatoId}");
                    } else {
                        error_log("Falha ao enviar notificação ao administrador: " . $resultados['admin']['message']);
                    }
                    
                    if (isset($resultados['filiado1'])) {
                        if ($resultados['filiado1']['success']) {
                            error_log("Notificação simplificada enviada ao filiado 1 ({$combinacao['nome1']}) para combinação");
                        } else {
                            error_log("Falha ao enviar notificação ao filiado 1: " . $resultados['filiado1']['message']);
                        }
                    }
                    
                    if (isset($resultados['filiado2'])) {
                        if ($resultados['filiado2']['success']) {
                            error_log("Notificação simplificada enviada ao filiado 2 ({$combinacao['nome2']}) para combinação");
                        } else {
                            error_log("Falha ao enviar notificação ao filiado 2: " . $resultados['filiado2']['message']);
                        }
                    }
                }
                
                error_log("Total de " . count($novasCombinacoes) . " combinação(ões) encontrada(s) para o novo contato ID {$novoContatoId} ({$novoContato['nome']}) - notificações enviadas");
            } else {
                error_log("Nenhuma combinação encontrada para o novo contato ID {$novoContatoId} ({$novoContato['nome']}) via formulário público");
            }
            
        } catch (Exception $e) {
            error_log("Erro ao verificar novas combinações para contato ID {$novoContatoId}: " . $e->getMessage());
        }
    }
}

// Processar o formulário se foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar dados obrigatórios
    $erros = [];
    
    if (empty($_POST['nome']) || trim($_POST['nome']) === '') {
        $erros[] = 'Nome completo é obrigatório';
    }
    
    if (empty($_POST['num_funcional']) || trim($_POST['num_funcional']) === '') {
        $erros[] = 'Número funcional é obrigatório';
    }
    
    if (empty($_POST['email']) || trim($_POST['email']) === '') {
        $erros[] = 'Email é obrigatório';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email deve ter um formato válido';
    }
    
    if (empty($_POST['origem']) || trim($_POST['origem']) === '') {
        $erros[] = 'Local de origem é obrigatório';
    }
    
    if (empty($_POST['destino']) || (is_array($_POST['destino']) && count($_POST['destino']) === 0)) {
        $erros[] = 'Pelo menos um destino deve ser selecionado';
    }
    
    // Se há erros, retornar erro
    if (!empty($erros)) {
        $resultado = [
            'success' => false,
            'tipo' => 'danger', // Mudando de 'error' para 'danger' para Bootstrap
            'texto' => 'Erro de validação: ' . implode(', ', $erros)
        ];
    } else {
        // Validações passaram, processar dados
        $solicitacao = new SolicitacaoPermuta();
        $resultado = $solicitacao->salvar($_POST);
    }
    
    // Retornar resposta JSON para AJAX ou redirecionar
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    } else {
        // Garantir que a mensagem está no formato correto
        if (isset($resultado['tipo']) && $resultado['tipo'] === 'error') {
            $resultado['tipo'] = 'danger'; // Bootstrap usa 'danger' ao invés de 'error'
        }
        
        // Redirecionar com mensagem
        $_SESSION['mensagem'] = $resultado;
        header('Location: index.php');
        exit;
    }
}
