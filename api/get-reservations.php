<?php
// api/get-reservations.php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Parametreler
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $offset = ($page - 1) * $perPage;
    
    // SQL sorgusu
    $sql = "SELECT 
                r.*,
                m.ad, m.soyad, m.email, m.telefon,
                o.oda_no, o.oda_adi,
                DATEDIFF(r.cikis_tarihi, r.giris_tarihi) as gece_sayisi
            FROM rezervasyonlar r
            LEFT JOIN musteriler m ON r.musteri_id = m.id
            LEFT JOIN odalar o ON r.oda_id = o.id
            WHERE 1=1";
    
    $params = [];
    
    // Filtrele
    if($filter !== 'all') {
        $sql .= " AND r.durum = ?";
        $params[] = $filter;
    }
    
    // Arama
    if(!empty($search)) {
        $sql .= " AND (
            r.rezervasyon_no LIKE ? OR 
            m.ad LIKE ? OR 
            m.soyad LIKE ? OR 
            m.email LIKE ? OR
            o.oda_no LIKE ?
        )";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    
    // Sıralama ve sayfalama
    $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    // Sorguyu çalıştır
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rezervasyonlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Toplam kayıt sayısı
    $countSql = "SELECT COUNT(*) as total 
                 FROM rezervasyonlar r
                 LEFT JOIN musteriler m ON r.musteri_id = m.id
                 LEFT JOIN odalar o ON r.oda_id = o.id
                 WHERE 1=1";
    
    $countParams = [];
    if($filter !== 'all') {
        $countSql .= " AND r.durum = ?";
        $countParams[] = $filter;
    }
    
    if(!empty($search)) {
        $countSql .= " AND (
            r.rezervasyon_no LIKE ? OR 
            m.ad LIKE ? OR 
            m.soyad LIKE ? OR 
            m.email LIKE ? OR
            o.oda_no LIKE ?
        )";
        $searchTerm = "%$search%";
        array_push($countParams, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetchColumn();
    
    $totalPages = ceil($total / $perPage);
    
    // Yanıt
    echo json_encode([
        'success' => true,
        'data' => [
            'reservations' => $rezervasyonlar,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>