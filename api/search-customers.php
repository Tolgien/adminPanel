<?php
// api/search-customers.php
require_once '../config/database.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if(strlen($query) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $db = getDB();
    
    $sql = "SELECT 
                id, ad, soyad, email, telefon, tc_kimlik,
                CONCAT(ad, ' ', soyad) as ad_soyad
            FROM musteriler 
            WHERE 
                ad LIKE ? OR 
                soyad LIKE ? OR 
                email LIKE ? OR 
                telefon LIKE ? OR
                tc_kimlik LIKE ? OR
                CONCAT(ad, ' ', soyad) LIKE ?
            ORDER BY 
                CASE 
                    WHEN ad LIKE ? THEN 1
                    WHEN soyad LIKE ? THEN 2
                    WHEN email LIKE ? THEN 3
                    WHEN CONCAT(ad, ' ', soyad) LIKE ? THEN 4
                    ELSE 5
                END
            LIMIT 10";
    
    $searchTerm = "%$query%";
    $params = array_fill(0, 9, $searchTerm);
    $params[5] = "%$query%"; // CONCAT için
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>