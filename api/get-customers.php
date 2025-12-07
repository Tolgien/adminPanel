<?php
// api/get-customers.php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Parametreler
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 15;
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $city = isset($_GET['city']) ? $_GET['city'] : '';
    $gender = isset($_GET['gender']) ? $_GET['gender'] : '';
    $regDate = isset($_GET['reg_date']) ? $_GET['reg_date'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
    
    $offset = ($page - 1) * $perPage;
    
    // SQL sorgusu
    $sql = "SELECT 
                m.*,
                COUNT(r.id) as rezervasyon_sayisi,
                MAX(r.created_at) as son_rezervasyon,
                SUM(r.toplam_fiyat) as toplam_harcama
            FROM musteriler m
            LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtrele
    if($filter === 'today') {
        $sql .= " AND DATE(m.created_at) = CURDATE()";
    } elseif($filter === 'vip') {
        $sql .= " AND (SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id = m.id) >= 5";
    } elseif($filter === 'frequent') {
        $sql .= " AND (SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id = m.id) >= 3";
    } elseif($filter === 'birthday') {
        $sql .= " AND MONTH(m.dogum_tarihi) = MONTH(CURDATE()) 
                 AND DAY(m.dogum_tarihi) BETWEEN DAY(CURDATE()) AND DAY(CURDATE()) + 30";
    }
    
    // Arama
    if(!empty($search)) {
        $sql .= " AND (
            m.ad LIKE ? OR 
            m.soyad LIKE ? OR 
            m.email LIKE ? OR 
            m.telefon LIKE ? OR
            m.tc_kimlik LIKE ?
        )";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    
    // Şehir filtresi
    if(!empty($city)) {
        $sql .= " AND m.sehir = ?";
        $params[] = $city;
    }
    
    // Cinsiyet filtresi
    if(!empty($gender)) {
        $sql .= " AND m.cinsiyet = ?";
        $params[] = $gender;
    }
    
    // Kayıt tarihi filtresi
    if(!empty($regDate)) {
        switch($regDate) {
            case 'today':
                $sql .= " AND DATE(m.created_at) = CURDATE()";
                break;
            case 'week':
                $sql .= " AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $sql .= " AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
                break;
        }
    }
    
    // Gruplama
    $sql .= " GROUP BY m.id";
    
    // Sıralama
    switch($sort) {
        case 'created_at_asc':
            $sql .= " ORDER BY m.created_at ASC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY m.ad ASC, m.soyad ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY m.ad DESC, m.soyad DESC";
            break;
        case 'reservation_desc':
            $sql .= " ORDER BY rezervasyon_sayisi DESC";
            break;
        default: // created_at_desc
            $sql .= " ORDER BY m.created_at DESC";
    }
    
    // Sayfalama
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    // Sorguyu çalıştır
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Toplam kayıt sayısı
    $countSql = "SELECT COUNT(DISTINCT m.id) 
                 FROM musteriler m
                 LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
                 WHERE 1=1";
    
    $countParams = [];
    if($filter === 'today') {
        $countSql .= " AND DATE(m.created_at) = CURDATE()";
    } elseif($filter === 'vip') {
        $countSql .= " AND (SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id = m.id) >= 5";
    } elseif($filter === 'frequent') {
        $countSql .= " AND (SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id = m.id) >= 3";
    } elseif($filter === 'birthday') {
        $countSql .= " AND MONTH(m.dogum_tarihi) = MONTH(CURDATE()) 
                      AND DAY(m.dogum_tarihi) BETWEEN DAY(CURDATE()) AND DAY(CURDATE()) + 30";
    }
    
    if(!empty($search)) {
        $countSql .= " AND (
            m.ad LIKE ? OR 
            m.soyad LIKE ? OR 
            m.email LIKE ? OR 
            m.telefon LIKE ? OR
            m.tc_kimlik LIKE ?
        )";
        $searchTerm = "%$search%";
        array_push($countParams, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    
    if(!empty($city)) {
        $countSql .= " AND m.sehir = ?";
        $countParams[] = $city;
    }
    
    if(!empty($gender)) {
        $countSql .= " AND m.cinsiyet = ?";
        $countParams[] = $gender;
    }
    
    if(!empty($regDate)) {
        switch($regDate) {
            case 'today':
                $countSql .= " AND DATE(m.created_at) = CURDATE()";
                break;
            case 'week':
                $countSql .= " AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $countSql .= " AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $countSql .= " AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
                break;
        }
    }
    
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $total = $countStmt->fetchColumn();
    
    $totalPages = ceil($total / $perPage);
    
    // Yanıt
    echo json_encode([
        'success' => true,
        'data' => [
            'customers' => $customers,
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