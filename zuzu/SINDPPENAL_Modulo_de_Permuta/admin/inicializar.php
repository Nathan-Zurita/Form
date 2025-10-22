<?php
/**
 * Script de inicialização do sistema administrativo
 * Execute este script após criar as tabelas para configurar o usuário admin
 */

require_once __DIR__ . '/config/autoload.php';

echo "<h1>🚀 Inicialização do Sistema Administrativo</h1>";

try {
    $database = new Database();
    $conn = $database->connect();
    
    echo "✅ Conexão com banco estabelecida<br><br>";
    
    // Verificar se a tabela administradores existe
    $stmt = $conn->query("SHOW TABLES LIKE 'administradores'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela 'administradores' não encontrada. Execute o script SQL primeiro.<br>";
        exit;
    }
    
    echo "✅ Tabela 'administradores' encontrada<br>";
    
    // Verificar se já existe um admin
    $checkQuery = "SELECT COUNT(*) as total FROM administradores WHERE usuario = 'admin'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute();
    $count = $checkStmt->fetch()['total'];
    
    if ($count > 0) {
        echo "⚠️ Usuário 'admin' já existe. Atualizando senha...<br>";
        
        // Atualizar senha existente
        $senha = 'admin123';
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $updateQuery = "UPDATE administradores SET senha = :senha WHERE usuario = 'admin'";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':senha', $hash);
        
        if ($updateStmt->execute()) {
            echo "✅ Senha do usuário 'admin' atualizada<br>";
        } else {
            echo "❌ Erro ao atualizar senha<br>";
        }
        
    } else {
        echo "➕ Criando usuário 'admin'...<br>";
        
        // Criar novo usuário admin
        $senha = 'admin123';
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO administradores (usuario, senha, nome_completo, email) 
                       VALUES ('admin', :senha, 'Administrador', 'admin@sindppenal.com')";
        
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bindParam(':senha', $hash);
        
        if ($insertStmt->execute()) {
            echo "✅ Usuário 'admin' criado com sucesso<br>";
        } else {
            echo "❌ Erro ao criar usuário<br>";
        }
    }
    
    // Teste final de autenticação
    echo "<br><h3>🧪 Teste de Autenticação:</h3>";
    
    $testQuery = "SELECT * FROM administradores WHERE usuario = 'admin'";
    $testStmt = $conn->prepare($testQuery);
    $testStmt->execute();
    $admin = $testStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify('admin123', $admin['senha'])) {
        echo "✅ <strong>Teste de login bem-sucedido!</strong><br>";
        echo "✅ <strong>Sistema pronto para uso!</strong><br>";
    } else {
        echo "❌ Erro no teste de autenticação<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<br><div style='background: #d4edda; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
echo "<h3 style='color: #155724; margin-top: 0;'>🎯 Credenciais de Acesso:</h3>";
echo "<strong style='color: #155724;'>Usuário:</strong> admin<br>";
echo "<strong style='color: #155724;'>Senha:</strong> admin123<br>";
echo "</div>";

echo "<br><div style='text-align: center;'>";
echo "<a href='login.php' style='background: #275940; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px;'>🔐 Fazer Login</a>";
echo "<a href='teste.php' style='background: #6c757d; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px;'>🧪 Executar Testes</a>";
echo "</div>";
?>