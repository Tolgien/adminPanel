<?php
// api/delete-customer.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz müşteri ID']);
    exit;
}

try {
    $db = getDB();
    
    // Müşterinin rezervasyonları var mı kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id = ?");
    $stmt->execute([$id]);
    $reservationCount = $stmt->fetchColumn();
    
    if($reservationCount > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bu müşterinin ' . $reservationCount . ' rezervasyonu bulunuyor. Önce rezervasyonları silmelisiniz.'
        ]);
        exit;
    }
    
    // Müşteriyi sil
    $stmt = $db->prepare("DELETE FROM musteriler WHERE id = ?");
    $stmt->execute([$id]);
    
    if($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Müşteri başarıyla silindi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Müşteri bulunamadı veya silinemedi'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>