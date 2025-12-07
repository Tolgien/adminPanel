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
        $id = $_GET['id'] ?? null;
        if ($id && !isset($_GET['page'])) {
            getSinglePost($id);
        } else {
            getPosts();
        }
        break;
    case 'POST':
        createPost();
        break;
    case 'PUT':
        updatePost();
        break;
    case 'DELETE':
        deletePost();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function getPosts() {
    global $pdo;
    
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    $categoryId = $_GET['category_id'] ?? null;
    $status = $_GET['status'] ?? null;
    $search = $_GET['search'] ?? null;
    
    try {
        $where = "WHERE 1=1";
        $params = [];
        
        if ($categoryId) {
            $where .= " AND byz.kategori_id = ?";
            $params[] = $categoryId;
        }
        
        if ($status) {
            $where .= " AND byz.yayin_durumu = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $where .= " AND (byz.baslik LIKE ? OR byz.ozet LIKE ? OR byz.icerik LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT byz.*, 
                bk.kategori_adi,
                CONCAT(k.ad, ' ', k.soyad) as yazar_adi,
                GROUP_CONCAT(be.etiket_adi SEPARATOR ', ') as etiketler
                FROM blog_yazilari byz
                LEFT JOIN blog_kategorileri bk ON byz.kategori_id = bk.id
                LEFT JOIN kullanicilar k ON byz.yazar_id = k.id
                LEFT JOIN blog_yazi_etiketleri bye ON byz.id = bye.yazi_id
                LEFT JOIN blog_etiketleri be ON bye.etiket_id = be.id
                $where
                GROUP BY byz.id
                ORDER BY byz.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(DISTINCT byz.id) as total 
                     FROM blog_yazilari byz
                     $where";
        $countStmt = $pdo->prepare(preg_replace('/LIMIT.*/', '', $countSql));
        $countParams = array_slice($params, 0, -2);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $posts,
            'pagination' => [
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getSinglePost($id) {
    global $pdo;
    
    try {
        $sql = "SELECT byz.*, 
                bk.kategori_adi,
                CONCAT(k.ad, ' ', k.soyad) as yazar_adi,
                GROUP_CONCAT(be.etiket_adi SEPARATOR ', ') as etiketler
                FROM blog_yazilari byz
                LEFT JOIN blog_kategorileri bk ON byz.kategori_id = bk.id
                LEFT JOIN kullanicilar k ON byz.yazar_id = k.id
                LEFT JOIN blog_yazi_etiketleri bye ON byz.id = bye.yazi_id
                LEFT JOIN blog_etiketleri be ON bye.etiket_id = be.id
                WHERE byz.id = ?
                GROUP BY byz.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            echo json_encode([
                'success' => true,
                'data' => [$post]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Yazı bulunamadı']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function createPost() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['baslik']) || empty($data['baslik'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Başlık gereklidir']);
        return;
    }
    
    try {
        $yazarId = $_SESSION['user_id'] ?? 1;
        
        $pdo->beginTransaction();
        
        $slug = createSlug($data['baslik']);
        
        $yayinTarihi = null;
        if (isset($data['yayin_durumu']) && $data['yayin_durumu'] == 'yayinda') {
            $yayinTarihi = date('Y-m-d H:i:s');
        }
        
        $sql = "INSERT INTO blog_yazilari 
                (kategori_id, yazar_id, baslik, slug, ozet, icerik, 
                 kapak_resmi, meta_baslik, meta_aciklama, meta_anahtar_kelimeler,
                 okunma_suresi, yayin_durumu, yayin_tarihi)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['kategori_id'] ?? null,
            $yazarId,
            $data['baslik'],
            $slug,
            $data['ozet'] ?? null,
            $data['icerik'] ?? '',
            $data['kapak_resmi'] ?? null,
            $data['meta_baslik'] ?? null,
            $data['meta_aciklama'] ?? null,
            $data['meta_anahtar_kelimeler'] ?? null,
            $data['okunma_suresi'] ?? null,
            $data['yayin_durumu'] ?? 'taslak',
            $yayinTarihi
        ]);
        
        $postId = $pdo->lastInsertId();
        
        if (isset($data['etiketler']) && is_array($data['etiketler'])) {
            addTagsToPost($postId, $data['etiketler']);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Blog yazısı başarıyla oluşturuldu',
            'id' => $postId
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function updatePost() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Yazı ID gereklidir']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        $updates = [];
        $params = [];
        
        if (isset($data['baslik'])) {
            $updates[] = "baslik = ?";
            $params[] = $data['baslik'];
            
            $slug = createSlug($data['baslik']);
            $updates[] = "slug = ?";
            $params[] = $slug;
        }
        
        if (isset($data['kategori_id'])) {
            $updates[] = "kategori_id = ?";
            $params[] = $data['kategori_id'];
        }
        
        if (isset($data['ozet'])) {
            $updates[] = "ozet = ?";
            $params[] = $data['ozet'];
        }
        
        if (isset($data['icerik'])) {
            $updates[] = "icerik = ?";
            $params[] = $data['icerik'];
        }
        
        if (isset($data['kapak_resmi'])) {
            $updates[] = "kapak_resmi = ?";
            $params[] = $data['kapak_resmi'];
        }
        
        if (isset($data['meta_baslik'])) {
            $updates[] = "meta_baslik = ?";
            $params[] = $data['meta_baslik'];
        }
        
        if (isset($data['meta_aciklama'])) {
            $updates[] = "meta_aciklama = ?";
            $params[] = $data['meta_aciklama'];
        }
        
        if (isset($data['meta_anahtar_kelimeler'])) {
            $updates[] = "meta_anahtar_kelimeler = ?";
            $params[] = $data['meta_anahtar_kelimeler'];
        }
        
        if (isset($data['okunma_suresi'])) {
            $updates[] = "okunma_suresi = ?";
            $params[] = $data['okunma_suresi'];
        }
        
        if (isset($data['yayin_durumu'])) {
            $updates[] = "yayin_durumu = ?";
            $params[] = $data['yayin_durumu'];
            
            if ($data['yayin_durumu'] == 'yayinda') {
                $updates[] = "yayin_tarihi = NOW()";
            }
        }
        
        if (!empty($updates)) {
            $params[] = $id;
            $sql = "UPDATE blog_yazilari SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        if (isset($data['etiketler'])) {
            $deleteSql = "DELETE FROM blog_yazi_etiketleri WHERE yazi_id = ?";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute([$id]);
            
            if (is_array($data['etiketler']) && !empty($data['etiketler'])) {
                addTagsToPost($id, $data['etiketler']);
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Blog yazısı başarıyla güncellendi'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function deletePost() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Yazı ID gereklidir']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        $sql1 = "DELETE FROM blog_yazi_etiketleri WHERE yazi_id = ?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$id]);
        
        $sql2 = "DELETE FROM blog_yazilari WHERE id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Blog yazısı başarıyla silindi'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function addTagsToPost($postId, $tags) {
    global $pdo;
    
    foreach ($tags as $tag) {
        if (is_numeric($tag)) {
            $tagId = $tag;
        } else {
            $tag = trim($tag);
            $slug = createSlug($tag);
            
            $checkSql = "SELECT id FROM blog_etiketleri WHERE slug = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$slug]);
            $existingTag = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingTag) {
                $tagId = $existingTag['id'];
            } else {
                $insertSql = "INSERT INTO blog_etiketleri (etiket_adi, slug) VALUES (?, ?)";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([$tag, $slug]);
                $tagId = $pdo->lastInsertId();
            }
        }
        
        $relationSql = "INSERT IGNORE INTO blog_yazi_etiketleri (yazi_id, etiket_id) VALUES (?, ?)";
        $relationStmt = $pdo->prepare($relationSql);
        $relationStmt->execute([$postId, $tagId]);
    }
}

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}
?>