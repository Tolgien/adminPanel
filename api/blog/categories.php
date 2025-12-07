<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Oturum kontrolü
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getCategories();
        break;
    case 'POST':
        createCategory();
        break;
    case 'PUT':
        updateCategory();
        break;
    case 'DELETE':
        deleteCategory();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function getCategories() {
    global $pdo;
    
    try {
        $sql = "SELECT bc.*, 
                (SELECT COUNT(*) FROM blog_yazilari WHERE kategori_id = bc.id) as yazi_sayisi
                FROM blog_kategorileri bc 
                WHERE bc.aktif = 1 
                ORDER BY bc.sira ASC, bc.kategori_adi ASC";
        
        $stmt = $pdo->query($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categories,
            'total' => count($categories)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function createCategory() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['kategori_adi']) || empty($data['kategori_adi'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Kategori adı gereklidir']);
        return;
    }
    
    try {
        $slug = createSlug($data['kategori_adi']);
        
        $sql = "INSERT INTO blog_kategorileri 
                (kategori_adi, slug, aciklama, ust_kategori_id, sira, aktif) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['kategori_adi'],
            $slug,
            $data['aciklama'] ?? null,
            $data['ust_kategori_id'] ?? null,
            $data['sira'] ?? 0,
            $data['aktif'] ?? 1
        ]);
        
        $categoryId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Kategori başarıyla oluşturuldu',
            'id' => $categoryId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateCategory() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Kategori ID gereklidir']);
        return;
    }
    
    try {
        $updates = [];
        $params = [];
        
        if (isset($data['kategori_adi'])) {
            $updates[] = "kategori_adi = ?";
            $params[] = $data['kategori_adi'];
            
            $slug = createSlug($data['kategori_adi']);
            $updates[] = "slug = ?";
            $params[] = $slug;
        }
        
        if (isset($data['aciklama'])) {
            $updates[] = "aciklama = ?";
            $params[] = $data['aciklama'];
        }
        
        if (isset($data['ust_kategori_id'])) {
            $updates[] = "ust_kategori_id = ?";
            $params[] = $data['ust_kategori_id'];
        }
        
        if (isset($data['sira'])) {
            $updates[] = "sira = ?";
            $params[] = $data['sira'];
        }
        
        if (isset($data['aktif'])) {
            $updates[] = "aktif = ?";
            $params[] = $data['aktif'];
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Güncellenecek alan bulunamadı']);
            return;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE blog_kategorileri SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'Kategori başarıyla güncellendi'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteCategory() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Kategori ID gereklidir']);
        return;
    }
    
    try {
        $checkSql = "SELECT COUNT(*) as count FROM blog_yazilari WHERE kategori_id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Bu kategoriye ait yazılar bulunuyor. Önce yazıları silin veya taşıyın.']);
            return;
        }
        
        $sql = "DELETE FROM blog_kategorileri WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Kategori başarıyla silindi'
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