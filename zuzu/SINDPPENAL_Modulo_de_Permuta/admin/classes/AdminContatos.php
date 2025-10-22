<?php
/**
 * Classe para gerenciar contatos no painel administrativo
 */

class AdminContatos {
    private $conn;
    private $table_name = "contatos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Lista todos os contatos com paginação
     */
    public function listarTodos($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            if (!empty($search)) {
                // Busca em campos de texto incluindo nomes das unidades
                $query = "SELECT c.*, 
                                CONCAT(u_origem.codigo, ' - ', u_origem.nome, ' (', u_origem.cidade, ')') as origem_nome
                         FROM " . $this->table_name . " c
                         LEFT JOIN unidades u_origem ON c.origem = u_origem.codigo
                         WHERE c.nome LIKE :search1 
                            OR c.num_funcional LIKE :search2 
                            OR c.origem LIKE :search3 
                            OR c.destino LIKE :search4
                            OR u_origem.nome LIKE :search5
                            OR u_origem.cidade LIKE :search6
                         ORDER BY c.created DESC LIMIT :limit OFFSET :offset";
                
                $stmt = $this->conn->prepare($query);
                $searchParam = "%$search%";
                $stmt->bindParam(':search1', $searchParam);
                $stmt->bindParam(':search2', $searchParam);
                $stmt->bindParam(':search3', $searchParam);
                $stmt->bindParam(':search4', $searchParam);
                $stmt->bindParam(':search5', $searchParam);
                $stmt->bindParam(':search6', $searchParam);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                
                $contatos = $stmt->fetchAll();
                
                // Contar total para paginação
                $countQuery = "SELECT COUNT(*) as total 
                              FROM " . $this->table_name . " c
                              LEFT JOIN unidades u_origem ON c.origem = u_origem.codigo
                              WHERE c.nome LIKE :search1 
                                 OR c.num_funcional LIKE :search2 
                                 OR c.origem LIKE :search3 
                                 OR c.destino LIKE :search4
                                 OR u_origem.nome LIKE :search5
                                 OR u_origem.cidade LIKE :search6";
                $countStmt = $this->conn->prepare($countQuery);
                $countStmt->bindParam(':search1', $searchParam);
                $countStmt->bindParam(':search2', $searchParam);
                $countStmt->bindParam(':search3', $searchParam);
                $countStmt->bindParam(':search4', $searchParam);
                $countStmt->bindParam(':search5', $searchParam);
                $countStmt->bindParam(':search6', $searchParam);
            } else {
                // Sem busca - query otimizada com JOIN
                $query = "SELECT c.*, 
                                CONCAT(u_origem.codigo, ' - ', u_origem.nome, ' (', u_origem.cidade, ')') as origem_nome
                         FROM " . $this->table_name . " c
                         LEFT JOIN unidades u_origem ON c.origem = u_origem.codigo
                         ORDER BY c.created DESC LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $contatos = $stmt->fetchAll();
                
                // Contar total
                $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
                $countStmt = $this->conn->prepare($countQuery);
            }
            
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            return [
                'contatos' => $contatos,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            return [
                'contatos' => [],
                'total' => 0,
                'pages' => 0
            ];
        }
    }

    /**
     * Busca um contato por ID
     */
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Criar novo contato
     */
    public function criar($dados) {
        try {
            // Validar dados obrigatórios
            $erros = [];
            
            if (empty($dados['nome']) || trim($dados['nome']) === '') {
                $erros[] = 'Nome completo é obrigatório';
            }
            
            if (empty($dados['num_funcional']) || trim($dados['num_funcional']) === '') {
                $erros[] = 'Número funcional é obrigatório';
            }
            
            if (empty($dados['origem']) || trim($dados['origem']) === '') {
                $erros[] = 'Local de origem é obrigatório';
            }
            
            if (empty($dados['destino']) || (is_array($dados['destino']) && count($dados['destino']) === 0)) {
                $erros[] = 'Pelo menos um destino deve ser selecionado';
            }
            
            if (!empty($erros)) {
                return [
                    'success' => false,
                    'message' => 'Erro de validação: ' . implode(', ', $erros)
                ];
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (nome, num_funcional, telefone, origem, destino, created, updated) 
                     VALUES (:nome, :num_funcional, :telefone, :origem, :destino, :created, :updated)";

            $stmt = $this->conn->prepare($query);

            $nome = htmlspecialchars(strip_tags($dados['nome']));
            $num_funcional = htmlspecialchars(strip_tags($dados['num_funcional']));
            $telefone = isset($dados['telefone']) ? htmlspecialchars(strip_tags($dados['telefone'])) : '';
            $origem = htmlspecialchars(strip_tags($dados['origem']));
            
            // Processar destino (pode vir como array)
            if (is_array($dados['destino'])) {
                $destino = implode(', ', $dados['destino']);
            } else {
                $destino = $dados['destino'];
            }
            $destino = htmlspecialchars(strip_tags($destino));
            
            $created = date('Y-m-d H:i:s');
            $updated = date('Y-m-d H:i:s');

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
                    'message' => 'Contato criado com sucesso!',
                    'id' => $this->conn->lastInsertId()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao criar contato.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro no banco de dados: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Atualizar contato
     */
    public function atualizar($id, $dados) {
        try {
            // Validar dados obrigatórios
            $erros = [];
            
            if (empty($dados['nome']) || trim($dados['nome']) === '') {
                $erros[] = 'Nome completo é obrigatório';
            }
            
            if (empty($dados['num_funcional']) || trim($dados['num_funcional']) === '') {
                $erros[] = 'Número funcional é obrigatório';
            }
            
            if (empty($dados['origem']) || trim($dados['origem']) === '') {
                $erros[] = 'Local de origem é obrigatório';
            }
            
            if (empty($dados['destino']) || (is_array($dados['destino']) && count($dados['destino']) === 0)) {
                $erros[] = 'Pelo menos um destino deve ser selecionado';
            }
            
            if (!empty($erros)) {
                return [
                    'success' => false,
                    'message' => 'Erro de validação: ' . implode(', ', $erros)
                ];
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET nome = :nome, num_funcional = :num_funcional, telefone = :telefone, 
                         origem = :origem, destino = :destino, updated = :updated 
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            $nome = htmlspecialchars(strip_tags($dados['nome']));
            $num_funcional = htmlspecialchars(strip_tags($dados['num_funcional']));
            $telefone = isset($dados['telefone']) ? htmlspecialchars(strip_tags($dados['telefone'])) : '';
            $origem = htmlspecialchars(strip_tags($dados['origem']));
            
            // Processar destino (pode vir como array)
            if (is_array($dados['destino'])) {
                $destino = implode(', ', $dados['destino']);
            } else {
                $destino = $dados['destino'];
            }
            $destino = htmlspecialchars(strip_tags($destino));
            
            $updated = date('Y-m-d H:i:s');

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':num_funcional', $num_funcional);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':origem', $origem);
            $stmt->bindParam(':destino', $destino);
            $stmt->bindParam(':updated', $updated);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Contato atualizado com sucesso!'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao atualizar contato.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro no banco de dados: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Excluir contato
     */
    public function excluir($id) {
        try {
            $idInt = (int)$id;
            if ($idInt <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID inválido para exclusão.'
                ];
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $idInt, PDO::PARAM_INT);

            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Contato excluído com sucesso!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Contato não encontrado ou já excluído.'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro no banco de dados: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar combinações de origem e destino (origem única, destinos múltiplos)
     */
    public function buscarCombinacoes() {
        try {
            $query = "SELECT 
                        c1.origem as origem1, 
                        c1.destino as destinos1, 
                        c1.nome as nome1, 
                        c1.num_funcional as func1,
                        c1.telefone as tel1,
                        c2.nome as nome2, 
                        c2.num_funcional as func2,
                        c2.telefone as tel2,
                        c2.origem as origem2,
                        c2.destino as destinos2,
                        c1.created as data1,
                        c2.created as data2,
                        c1.id as id1,
                        c2.id as id2
                      FROM " . $this->table_name . " c1 
                      INNER JOIN " . $this->table_name . " c2 ON 
                        c1.id < c2.id
                      ORDER BY c1.created DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $combinacoes = [];
            
            // Processar combinações manualmente para lidar com destinos múltiplos
            foreach ($contatos as $contato) {
                // Origem é única, destinos podem ser múltiplos (separados por vírgula)
                $origem1 = isset($contato['origem1']) ? trim($contato['origem1']) : '';
                $origem2 = isset($contato['origem2']) ? trim($contato['origem2']) : '';

                // Normalizações para comparação robusta (case-insensitive e espaços)
                $origem1N = $this->normalizarUnidade($origem1);
                $origem2N = $this->normalizarUnidade($origem2);

                $destinos1Arr = $this->parseDestinos(isset($contato['destinos1']) ? $contato['destinos1'] : '');
                $destinos2Arr = $this->parseDestinos(isset($contato['destinos2']) ? $contato['destinos2'] : '');

                // Verificar se há combinação:
                // Origem de c1 está nos destinos de c2 E
                // Origem de c2 está nos destinos de c1
                $match1 = in_array($origem1N, $destinos2Arr, true);
                $match2 = in_array($origem2N, $destinos1Arr, true);

                if ($match1 && $match2) {
                    // Encontrou uma combinação válida
                    $combinacoes[] = [
                        'nome1' => $contato['nome1'],
                        'func1' => $contato['func1'],
                        'tel1' => $contato['tel1'],
                        'origem1' => $origem1,
                        'destinos1' => $contato['destinos1'],
                        'nome2' => $contato['nome2'],
                        'func2' => $contato['func2'],
                        'tel2' => $contato['tel2'],
                        'origem2' => $origem2,
                        'destinos2' => $contato['destinos2'],
                        'data1' => $contato['data1'],
                        'data2' => $contato['data2'],
                        // Mostrar par de troca para clareza
                        'match_unidade' => $origem1 . ' ⇄ ' . $origem2
                    ];
                }
            }
            
            return $combinacoes;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Normaliza uma unidade (código/nome) para comparação
     * - trim
     * - collapse múltiplos espaços
     * - transforma em maiúsculas (comparação case-insensitive)
     * - remove acentos
     */
    private function normalizarUnidade($s) {
        if (!is_string($s)) return '';
        $s = trim($s);
        // substituir múltiplos espaços por um único
        $s = preg_replace('/\s+/', ' ', $s);
        $s = mb_strtoupper($s, 'UTF-8');
        
        // Remover acentos
        $acentos = [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        return strtr($s, $acentos);
    }

    /**
     * Converte string de destinos "A, B, C" em array normalizado
     */
    private function parseDestinos($s) {
        if (!is_string($s) || $s === '') return [];
        // separar por vírgula, ignorando espaços
        $parts = preg_split('/\s*,\s*/', $s, -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) return [];
        $norm = [];
        foreach ($parts as $p) {
            $n = $this->normalizarUnidade($p);
            if ($n !== '') {
                $norm[] = $n;
            }
        }
        return $norm;
    }

    /**
     * Estatísticas gerais
     */
    public function getEstatisticas() {
        try {
            $stats = [];
            
            // Total de contatos
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_contatos'] = $stmt->fetch()['total'];
            
            // Contatos hoje
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE DATE(created) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['contatos_hoje'] = $stmt->fetch()['total'];
            
            // Contatos esta semana
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE WEEK(created) = WEEK(NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['contatos_semana'] = $stmt->fetch()['total'];
            
            // Origens mais procuradas
            $query = "SELECT origem, COUNT(*) as total FROM " . $this->table_name . " GROUP BY origem ORDER BY total DESC LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['origens_populares'] = $stmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Buscar todas as unidades
     */
    public function buscarUnidades() {
        try {
            $query = "SELECT * FROM unidades 
                     WHERE ativo = 1 AND codigo != 'teste' 
                     ORDER BY cidade, codigo";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Resolver código de unidade para nome completo
     */
    public function resolverUnidade($codigo) {
        if (empty($codigo)) {
            return '';
        }
        
        $codigo = trim($codigo);
        
        try {
            $query = "SELECT CONCAT(codigo, ' - ', nome, ' (', cidade, ')') as nome_completo 
                     FROM unidades WHERE codigo = :codigo AND ativo = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['nome_completo'] : $codigo . ' (Unidade não encontrada)';
        } catch (PDOException $e) {
            return $codigo . ' (Erro ao buscar)'; // fallback para o código original
        }
    }

    /**
     * Resolver múltiplos códigos de destino para nomes completos
     */
    public function resolverDestinos($destinos_string) {
        if (empty($destinos_string)) {
            return '';
        }
        
        $codigos = array_map('trim', explode(',', $destinos_string));
        $nomes_resolvidos = [];
        
        foreach ($codigos as $codigo) {
            if (!empty($codigo)) {
                $nomes_resolvidos[] = $this->resolverUnidade($codigo);
            }
        }
        
        return implode(', ', $nomes_resolvidos);
    }
}

