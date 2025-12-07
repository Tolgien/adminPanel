<?php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getTags();
        break;
    case 'POST':
        createTag();
        break;
    case 'DELETE':
        deleteTag();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function getTags() {
    global $pdo;
    
    $search = $_GET['search'] ?? null;
    
    try {
        $sql = "SELECT be.*, 
                COUNT(bye.yazi_id) as kullanım_sayisi
                FROM blog_etiketleri be
                LEFT JOIN blog_yazi_etiketleri bye ON be.id = bye.etiket_id
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND be.etiket_adi LIKE ?";
            $params[] = "%$search%";
        }
        
        $sql .= " GROUP BY be.id
                  ORDER BY kullanım_sayisi DESC, be.etiket_adi ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $tags,
            'total' => count($tags)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function createTag() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['etiket_adi']) || empty($data['etiket_adi'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Etiket adı gereklidir']);
        return;
    }
    
    try {
        $slug = createSlug($data['etiket_adi']);
        
        $checkSql = "SELECT id FROM blog_etiketleri WHERE slug = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$slug]);
        
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Bu etiket zaten mevcut']);
            return;
        }
        
        $sql = "INSERT INTO blog_etiketleri (etiket_adi, slug) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['etiket_adi'], $slug]);
        
        $tagId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Etiket başarıyla oluşturuldu',
            'id' => $tagId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteTag() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Etiket ID gereklidir']);
        return;
    }
    
    try {
        $checkSql = "SELECT COUNT(*) as count FROM blog_yazi_etiketleri WHERE etiket_id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Bu etiket yazılarda kullanılıyor. Önce yazılardan kaldırın.']);
            return;
        }
        
        $sql = "DELETE FROM blog_etiketleri WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Etiket başarıyla silindi'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}
?>