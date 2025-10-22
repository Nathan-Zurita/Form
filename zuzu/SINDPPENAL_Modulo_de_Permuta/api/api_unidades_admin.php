<?php
/**
 * API para buscar unidades
 * Retorna lista de unidades ativas em formato JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir configurações
require_once __DIR__ . '/../admin/config/database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    // Buscar termo de pesquisa
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    if (!empty($search)) {
        $query = "SELECT codigo, nome, cidade, 
                         CONCAT(codigo, ' - ', nome, ' (', cidade, ')') as display_name
                  FROM unidades 
                  WHERE ativo = 1 
                    AND (codigo LIKE :search OR nome LIKE :search OR cidade LIKE :search)
                  ORDER BY codigo ASC 
                  LIMIT :limit";
        
        $stmt = $conn->prepare($query);
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    } else {
        $query = "SELECT codigo, nome, cidade, 
                         CONCAT(codigo, ' - ', nome, ' (', cidade, ')') as display_name
                  FROM unidades 
                  WHERE ativo = 1 
                  ORDER BY codigo ASC 
                  LIMIT :limit";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retornar dados
    echo json_encode([
        'success' => true,
        'data' => $unidades,
        'total' => count($unidades)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na requisição'
    ], JSON_UNESCAPED_UNICODE);
}