<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Incluir apenas as classes necessárias sem session.php
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../admin/config/database.php';

/**
 * Remove acentos e normaliza string para comparações/queries (nome único para evitar conflitos globais)
 */
function api_unidades_remover_acentos($string) {
    if ($string === null) return '';
    $map = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n',
        'Á'=>'A','À'=>'A','Ã'=>'A','Â'=>'A','Ä'=>'A',
        'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
        'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
        'Ó'=>'O','Ò'=>'O','Õ'=>'O','Ô'=>'O','Ö'=>'O',
        'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
        'Ç'=>'C','Ñ'=>'N'
    ];
    return mb_strtolower(strtr($string, $map), 'UTF-8');
}

// Obter termo de busca
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

$result = [];

try {
    if (!class_exists('Database')) {
        // Se Database não estiver disponível, retornar vazio para evitar erro exposto
        echo json_encode([]);
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT codigo, nome, cidade FROM unidades WHERE ativo = 1";
    $params = [];

    if (!empty($searchTerm)) {
        $sql .= " AND (codigo LIKE ? OR nome LIKE ? OR cidade LIKE ?)";
        $like = '%' . $searchTerm . '%';
        $params = [$like, $like, $like];
    }

    $sql .= " ORDER BY cidade ASC, codigo ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se não encontrou nada e houve termo de busca, tentar busca sem acentos em PHP
    if (empty($rows) && !empty($searchTerm)) {
        $stmt2 = $conn->prepare("SELECT codigo, nome, cidade FROM unidades WHERE ativo = 1 ORDER BY cidade ASC, codigo ASC");
        $stmt2->execute();
        $all = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $needle = api_unidades_remover_acentos($searchTerm);
        foreach ($all as $r) {
            $hay = api_unidades_remover_acentos($r['codigo'] . ' ' . $r['nome'] . ' ' . $r['cidade']);
            if (strpos($hay, $needle) !== false) {
                $rows[] = $r;
            }
        }
    }

    // Agrupar por cidade
    $cidades = [];
    foreach ($rows as $u) {
        $cidade = $u['cidade'] ?: 'Outras';
        if (!isset($cidades[$cidade])) $cidades[$cidade] = [];
        $cidades[$cidade][] = $u;
    }

    foreach ($cidades as $cidade => $unidadesCidade) {
        $grupo = [
            'text' => $cidade,
            'children' => []
        ];
        foreach ($unidadesCidade as $u) {
            $grupo['children'][] = [
                'id' => $u['codigo'],
                'text' => $u['codigo'] . ' - ' . $u['nome']
            ];
        }
        $result[] = $grupo;
    }

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([]);
}

