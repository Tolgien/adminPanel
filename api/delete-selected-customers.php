<?php
// api/delete-selected-customers.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

$ids = isset($_POST['ids']) ? $_POST['ids'] : '';

if(empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Müşteri seçilmedi']);
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
    
    // Rezervasyon kontrolü
    $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
    $stmt = $db->prepare("SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id IN ($placeholders)");
    $stmt->execute($idArray);
    $reservationCount = $stmt->fetchColumn();
    
    if($reservationCount > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Seçilen müşterilerin ' . $reservationCount . ' rezervasyonu bulunuyor. Önce rezervasyonları silmelisiniz.'
        ]);
        exit;
    }
    
    // Müşterileri sil
    $stmt = $db->prepare("DELETE FROM musteriler WHERE id IN ($placeholders)");
    $stmt->execute($idArray);
    $deletedCount = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => $deletedCount . ' müşteri silindi',
        'data' => ['deleted_count' => $deletedCount]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>