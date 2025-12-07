<?php
// api/get-selected-customers.php
require_once '../config/database.php';
header('Content-Type: application/json');

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';

if(empty($ids)) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $db = getDB();
    
    $idArray = explode(',', $ids);
    $idArray = array_filter($idArray, 'is_numeric');
    
    if(empty($idArray)) {
        echo json_encode(['success' => false, 'message' => 'Geçerli ID bulunamadı']);
        exit;
    }
    
    $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
    $stmt = $db->prepare("SELECT id, ad, soyad, email, telefon FROM musteriler WHERE id IN ($placeholders)");
    $stmt->execute($idArray);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>