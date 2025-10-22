<?php
/**
 * Processa o formulário de solicitação de permuta
 */

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
                     (nome, num_funcional, telefone, origem, destino, created, updated) 
                     VALUES (:nome, :num_funcional, :telefone, :origem, :destino, :created, :updated)";

            $stmt = $this->conn->prepare($query);

            // Sanitizar os dados
            $nome = htmlspecialchars(strip_tags($dados['nome']));
            $num_funcional = htmlspecialchars(strip_tags($dados['num_funcional']));
            $telefone = isset($dados['telefone']) ? htmlspecialchars(strip_tags($dados['telefone'])) : '';
            
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
            $stmt->bindParam(':origem', $origem);
            $stmt->bindParam(':destino', $destino);
            $stmt->bindParam(':created', $created);
            $stmt->bindParam(':updated', $updated);

            if ($stmt->execute()) {
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
}

// Processar o formulário se foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iniciar sessão se não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Validar dados obrigatórios
    $erros = [];
    
    if (empty($_POST['nome']) || trim($_POST['nome']) === '') {
        $erros[] = 'Nome completo é obrigatório';
    }
    
    if (empty($_POST['num_funcional']) || trim($_POST['num_funcional']) === '') {
        $erros[] = 'Número funcional é obrigatório';
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
?>