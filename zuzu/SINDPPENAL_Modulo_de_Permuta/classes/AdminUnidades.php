<?php
/**
 * Classe para gerenciar unidades no painel administrativo
 */

class AdminUnidades {
    private $conn;
    private $table_name = "unidades";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Lista todas as unidades com paginação
     */
    public function listarTodos($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            if (!empty($search)) {
                // Busca em campos de texto
                $query = "SELECT * FROM " . $this->table_name . " 
                         WHERE codigo LIKE :search1 
                            OR nome LIKE :search2 
                            OR cidade LIKE :search3
                         ORDER BY codigo ASC LIMIT :limit OFFSET :offset";
                
                $stmt = $this->conn->prepare($query);
                $searchParam = "%$search%";
                $stmt->bindParam(':search1', $searchParam);
                $stmt->bindParam(':search2', $searchParam);
                $stmt->bindParam(':search3', $searchParam);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                
                $unidades = $stmt->fetchAll();
                
                // Contar total para paginação
                $countQuery = "SELECT COUNT(*) as total 
                              FROM " . $this->table_name . " 
                              WHERE codigo LIKE :search1 
                                 OR nome LIKE :search2 
                                 OR cidade LIKE :search3";
                $countStmt = $this->conn->prepare($countQuery);
                $countStmt->bindParam(':search1', $searchParam);
                $countStmt->bindParam(':search2', $searchParam);
                $countStmt->bindParam(':search3', $searchParam);
            } else {
                // Sem busca
                $query = "SELECT * FROM " . $this->table_name . " 
                         ORDER BY codigo ASC LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $unidades = $stmt->fetchAll();
                
                // Contar total
                $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
                $countStmt = $this->conn->prepare($countQuery);
            }
            
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            return [
                'unidades' => $unidades,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            return [
                'unidades' => [],
                'total' => 0,
                'pages' => 0
            ];
        }
    }

    /**
     * Busca uma unidade por ID
     */
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Busca uma unidade por código
     */
    public function buscarPorCodigo($codigo) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE codigo = :codigo";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Cria uma nova unidade
     */
    public function criar($dados) {
        try {
            // Validações básicas
            if (empty($dados['codigo'])) {
                return ['success' => false, 'message' => 'Código é obrigatório.'];
            }
            if (empty($dados['nome'])) {
                return ['success' => false, 'message' => 'Nome é obrigatório.'];
            }
            if (empty($dados['cidade'])) {
                return ['success' => false, 'message' => 'Cidade é obrigatória.'];
            }

            // Verificar se código já existe
            $existente = $this->buscarPorCodigo($dados['codigo']);
            if ($existente) {
                return ['success' => false, 'message' => 'Código já existe no sistema.'];
            }

            $query = "INSERT INTO " . $this->table_name . " 
                     (codigo, nome, cidade, ativo, created, updated) 
                     VALUES (:codigo, :nome, :cidade, :ativo, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $dados['codigo']);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':cidade', $dados['cidade']);
            $ativo = isset($dados['ativo']) ? 1 : 0;
            $stmt->bindParam(':ativo', $ativo);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Unidade criada com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Erro ao criar unidade.'];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Código já existe no sistema.'];
            }
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Atualiza uma unidade existente
     */
    public function atualizar($id, $dados) {
        try {
            // Validações básicas
            if (empty($dados['codigo'])) {
                return ['success' => false, 'message' => 'Código é obrigatório.'];
            }
            if (empty($dados['nome'])) {
                return ['success' => false, 'message' => 'Nome é obrigatório.'];
            }
            if (empty($dados['cidade'])) {
                return ['success' => false, 'message' => 'Cidade é obrigatória.'];
            }

            // Verificar se unidade existe
            $unidade = $this->buscarPorId($id);
            if (!$unidade) {
                return ['success' => false, 'message' => 'Unidade não encontrada.'];
            }

            // Verificar se código já existe em outra unidade
            $existente = $this->buscarPorCodigo($dados['codigo']);
            if ($existente && $existente['id'] != $id) {
                return ['success' => false, 'message' => 'Código já existe em outra unidade.'];
            }

            $query = "UPDATE " . $this->table_name . " 
                     SET codigo = :codigo, nome = :nome, cidade = :cidade, 
                         ativo = :ativo, updated = NOW()
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':codigo', $dados['codigo']);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':cidade', $dados['cidade']);
            $ativo = isset($dados['ativo']) ? 1 : 0;
            $stmt->bindParam(':ativo', $ativo);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Unidade atualizada com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Erro ao atualizar unidade.'];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Código já existe em outra unidade.'];
            }
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Exclui uma unidade
     */
    public function excluir($id) {
        try {
            // Verificar se unidade existe
            $unidade = $this->buscarPorId($id);
            if (!$unidade) {
                return ['success' => false, 'message' => 'Unidade não encontrada.'];
            }

            // Verificar se unidade está sendo usada em contatos
            $query_check = "SELECT COUNT(*) as total FROM contatos WHERE origem = :codigo OR destino LIKE CONCAT('%', :codigo2, '%')";
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->bindParam(':codigo', $unidade['codigo']);
            $stmt_check->bindParam(':codigo2', $unidade['codigo']);
            $stmt_check->execute();
            $em_uso = $stmt_check->fetch()['total'];

            if ($em_uso > 0) {
                return ['success' => false, 'message' => 'Não é possível excluir esta unidade pois está sendo utilizada em contatos.'];
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Unidade excluída com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Erro ao excluir unidade.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Lista todas as unidades ativas para uso em selects
     */
    public function listarAtivas() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE ativo = 1 ORDER BY codigo ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ativa/Desativa uma unidade
     */
    public function alterarStatus($id, $ativo) {
        try {
            $query = "UPDATE " . $this->table_name . " SET ativo = :ativo, updated = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ativo', $ativo);
            
            if ($stmt->execute()) {
                $status = $ativo ? 'ativada' : 'desativada';
                return ['success' => true, 'message' => "Unidade $status com sucesso!"];
            } else {
                return ['success' => false, 'message' => 'Erro ao alterar status da unidade.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Obtém estatísticas das unidades
     */
    public function getEstatisticas() {
        try {
            $stats = [];
            
            // Total de unidades
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_unidades'] = $stmt->fetch()['total'];
            
            // Unidades ativas
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE ativo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['unidades_ativas'] = $stmt->fetch()['total'];
            
            // Unidades inativas
            $stats['unidades_inativas'] = $stats['total_unidades'] - $stats['unidades_ativas'];
            
            // Cidades com mais unidades
            $query = "SELECT cidade, COUNT(*) as total FROM " . $this->table_name . " 
                     WHERE ativo = 1 GROUP BY cidade ORDER BY total DESC LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['cidades_populares'] = $stmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            return [
                'total_unidades' => 0,
                'unidades_ativas' => 0,
                'unidades_inativas' => 0,
                'cidades_populares' => []
            ];
        }
    }
}