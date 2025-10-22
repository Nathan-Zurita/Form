<?php
require_once __DIR__ . '/config/autoload.php';

echo "<h1>Atualizar Senha do Admin</h1>";

try {
    $database = new Database();
    $conn = $database->connect();
    
    // Gerar hash para a senha 'admin123'
    $senha = 'admin123';
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    
    echo "Senha original: $senha<br>";
    echo "Hash gerado: $hash<br><br>";
    
    // Atualizar no banco
    $query = "UPDATE administradores SET senha = :senha WHERE usuario = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':senha', $hash);
    
    if ($stmt->execute()) {
        echo "✅ Senha atualizada com sucesso!<br>";
        
        // Verificar se funciona
        $queryVerify = "SELECT * FROM administradores WHERE usuario = 'admin'";
        $stmtVerify = $conn->prepare($queryVerify);
        $stmtVerify->execute();
        $admin = $stmtVerify->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($senha, $admin['senha'])) {
            echo "✅ Verificação de senha funcionando corretamente!<br>";
        } else {
            echo "❌ Erro na verificação de senha<br>";
        }
        
    } else {
        echo "❌ Erro ao atualizar senha<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<br><strong>Agora você pode fazer login com:</strong><br>";
echo "Usuário: admin<br>";
echo "Senha: admin123<br>";
echo "<br><a href='login.php'>🔐 Ir para o login</a>";
?>