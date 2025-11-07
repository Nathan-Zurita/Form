<?php
/**
 * Sistema de Autenticação para Administradores
 */
class Auth {
    private $conn;
    private $table_name = "administradores";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Autentica um usuário
     */
    public function login($usuario, $senha) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE usuario = :usuario AND ativo = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($senha, $admin['senha'])) {
                    // Atualizar último login
                    $this->atualizarUltimoLogin($admin['id']);
                    
                    // Regenerar ID da sessão para segurança
                    session_regenerate_id(true);
                    
                    // Criar sessão
                    $_SESSION['admin_logado'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_usuario'] = $admin['usuario'];
                    $_SESSION['admin_nome'] = $admin['nome_completo'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['session_regenerated'] = true;
                    
                    return [
                        'success' => true,
                        'message' => 'Login realizado com sucesso!'
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Usuário ou senha incorretos.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erro no sistema: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica se o usuário está autenticado
     */
    public static function verificarAuth() {
        if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
            return false;
        }
        
        // Verificar se a sessão não expirou (2 horas)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 7200)) {
            self::logout();
            return false;
        }
        
        return true;
    }

    /**
     * Força redirecionamento se não estiver autenticado
     */
    public static function requireAuth() {
        if (!self::verificarAuth()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Faz logout do usuário
     */
    public static function logout() {
        session_unset();
        session_destroy();
        
        // Iniciar nova sessão
        session_start();
        session_regenerate_id(true);
        
        header('Location: login.php');
        exit;
    }

    /**
     * Atualiza o último login do administrador
     */
    private function atualizarUltimoLogin($admin_id) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET ultimo_login = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $admin_id);
            $stmt->execute();
        } catch (PDOException $e) {
            // Log do erro se necessário
        }
    }

    /**
     * Cria hash da senha
     */
    public static function hashSenha($senha) {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    /**
     * Cria um novo administrador
     */
    public function criarAdmin($dados) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (usuario, senha, nome_completo, email) 
                     VALUES (:usuario, :senha, :nome_completo, :email)";

            $stmt = $this->conn->prepare($query);

            $usuario = htmlspecialchars(strip_tags($dados['usuario']));
            $senha = self::hashSenha($dados['senha']);
            $nome_completo = htmlspecialchars(strip_tags($dados['nome_completo']));
            $email = htmlspecialchars(strip_tags($dados['email']));

            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':senha', $senha);
            $stmt->bindParam(':nome_completo', $nome_completo);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Administrador criado com sucesso!'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro ao criar administrador.'
            ];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'success' => false,
                    'message' => 'Usuário já existe.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Erro no sistema: ' . $e->getMessage()
            ];
        }
    }
}

