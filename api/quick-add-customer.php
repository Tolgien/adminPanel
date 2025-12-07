<?php
// api/quick-add-customer.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

$ad = trim($_POST['ad'] ?? '');
$soyad = trim($_POST['soyad'] ?? '');
$telefon = trim($_POST['telefon'] ?? '');

if(empty($ad) || empty($soyad) || empty($telefon)) {
    echo json_encode(['success' => false, 'message' => 'Ad, soyad ve telefon gereklidir']);
    exit;
}

try {
    $db = getDB();
    
    // Email oluştur (telefon@otel.com)
    $email = str_replace([' ', '(', ')', '-'], '', $telefon) . '@otel.com';
    
    // Müşteriyi oluştur
    $stmt = $db->prepare("
        INSERT INTO musteriler 
        (ad, soyad, email, telefon, ulke)
        VALUES (?, ?, ?, ?, 'Türkiye')
    ");
    
    $stmt->execute([$ad, $soyad, $email, $telefon]);
    $musteri_id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Müşteri başarıyla eklendi',
        'data' => [
            'musteri_id' => $musteri_id,
            'ad' => $ad,
            'soyad' => $soyad,
            'email' => $email,
            'telefon' => $telefon
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>