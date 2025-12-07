<?php
// api/get-customer-data.php
require_once '../config/database.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ID']);
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM musteriler WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$customer) {
        echo json_encode(['success' => false, 'message' => 'Müşteri bulunamadı']);
        exit;
    }
    
    // Etiketleri getir (notlardan parse et)
    $tags = [];
    if(!empty($customer['notlar'])) {
        // Basit etiket parse etme
        preg_match_all('/#(\w+)/', $customer['notlar'], $matches);
        if(!empty($matches[1])) {
            $tags = $matches[1];
        }
    }
    
    $customer['etiketler'] = implode(',', $tags);
    
    echo json_encode([
        'success' => true,
        'data' => $customer
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>