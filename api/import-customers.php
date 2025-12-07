<?php
// api/import-customers.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

$data = isset($_POST['data']) ? json_decode($_POST['data'], true) : [];

if(empty($data) || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı']);
    exit;
}

try {
    $db = getDB();
    
    $successCount = 0;
    $failedCount = 0;
    $errors = [];
    
    foreach($data as $index => $row) {
        try {
            // Gerekli alanları kontrol et
            if(empty($row['ad']) || empty($row['soyad']) || empty($row['email'])) {
                throw new Exception("Satır " . ($index + 1) . ": Ad, soyad ve email gereklidir");
            }
            
            // Email formatını kontrol et
            if(!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Satır " . ($index + 1) . ": Geçersiz email formatı");
            }
            
            // Email benzersiz mi kontrol et
            $stmt = $db->prepare("SELECT id FROM musteriler WHERE email = ?");
            $stmt->execute([$row['email']]);
            if($stmt->fetch()) {
                // Email zaten varsa, güncelle
                $stmt = $db->prepare("
                    UPDATE musteriler SET 
                        ad = ?, soyad = ?, telefon = ?, tc_kimlik = ?, 
                        dogum_tarihi = ?, cinsiyet = ?, adres = ?, 
                        sehir = ?, ulke = ?, notlar = ?
                    WHERE email = ?
                ");
                
                $stmt->execute([
                    $row['ad'] ?? '',
                    $row['soyad'] ?? '',
                    $row['telefon'] ?? '',
                    $row['tc_kimlik'] ?? '',
                    !empty($row['dogum_tarihi']) ? $row['dogum_tarihi'] : null,
                    $row['cinsiyet'] ?? null,
                    $row['adres'] ?? '',
                    $row['sehir'] ?? '',
                    $row['ulke'] ?? 'Türkiye',
                    $row['notlar'] ?? '',
                    $row['email']
                ]);
            } else {
                // Yeni müşteri ekle
                $stmt = $db->prepare("
                    INSERT INTO musteriler 
                    (ad, soyad, email, telefon, tc_kimlik, dogum_tarihi, 
                     cinsiyet, adres, sehir, ulke, notlar)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $row['ad'] ?? '',
                    $row['soyad'] ?? '',
                    $row['email'],
                    $row['telefon'] ?? '',
                    $row['tc_kimlik'] ?? '',
                    !empty($row['dogum_tarihi']) ? $row['dogum_tarihi'] : null,
                    $row['cinsiyet'] ?? null,
                    $row['adres'] ?? '',
                    $row['sehir'] ?? '',
                    $row['ulke'] ?? 'Türkiye',
                    $row['notlar'] ?? ''
                ]);
            }
            
            $successCount++;
            
        } catch(Exception $e) {
            $failedCount++;
            $errors[] = $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total' => count($data),
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>