<?php
// api/send-customer-message.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

$type = $_POST['type'] ?? ''; // 'email' veya 'sms'
$customerIds = $_POST['customers'] ?? '';
$subject = $_POST['subject'] ?? '';
$content = $_POST['content'] ?? '';

if(empty($type) || empty($customerIds) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Eksik parametreler']);
    exit;
}

try {
    $db = getDB();
    
    // Müşteri ID'lerini array'e çevir
    $idArray = explode(',', $customerIds);
    $idArray = array_filter($idArray, 'is_numeric');
    
    if(empty($idArray)) {
        echo json_encode(['success' => false, 'message' => 'Geçerli müşteri ID bulunamadı']);
        exit;
    }
    
    // Müşterileri getir
    $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
    $stmt = $db->prepare("SELECT id, ad, soyad, email, telefon FROM musteriler WHERE id IN ($placeholders)");
    $stmt->execute($idArray);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(empty($customers)) {
        echo json_encode(['success' => false, 'message' => 'Müşteri bulunamadı']);
        exit;
    }
    
    $successCount = 0;
    $failedCount = 0;
    $results = [];
    
    foreach($customers as $customer) {
        try {
            if($type === 'email') {
                // Email gönderimi simülasyonu
                $to = $customer['email'];
                $fullName = $customer['ad'] . ' ' . $customer['soyad'];
                
                // Kişiselleştirilmiş içerik
                $personalizedContent = str_replace(
                    ['{ad}', '{soyad}', '{ad_soyad}'],
                    [$customer['ad'], $customer['soyad'], $fullName],
                    $content
                );
                
                // Gerçek email gönderimi için burayı değiştirin
                // mail($to, $subject, $personalizedContent);
                
                $results[] = [
                    'id' => $customer['id'],
                    'name' => $fullName,
                    'email' => $to,
                    'status' => 'sent',
                    'message' => 'Email gönderildi'
                ];
                
            } elseif($type === 'sms') {
                // SMS gönderimi simülasyonu
                $phone = $customer['telefon'];
                $fullName = $customer['ad'] . ' ' . $customer['soyad'];
                
                // Kişiselleştirilmiş içerik
                $personalizedContent = str_replace(
                    ['{ad}', '{soyad}', '{ad_soyad}'],
                    [$customer['ad'], $customer['soyad'], $fullName],
                    $content
                );
                
                // SMS karakter sınırı
                if(strlen($personalizedContent) > 160) {
                    $personalizedContent = substr($personalizedContent, 0, 157) . '...';
                }
                
                // Gerçek SMS gönderimi için burayı değiştirin
                // Burada SMS API'sini çağırın
                
                $results[] = [
                    'id' => $customer['id'],
                    'name' => $fullName,
                    'phone' => $phone,
                    'status' => 'sent',
                    'message' => 'SMS gönderildi'
                ];
            }
            
            $successCount++;
            
            // Geçmişe kaydet
            $logStmt = $db->prepare("
                INSERT INTO bildirimler 
                (kullanici_id, baslik, mesaj, tip, link)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $logStmt->execute([
                $_SESSION['user_id'] ?? null,
                $type === 'email' ? 'Email Gönderimi' : 'SMS Gönderimi',
                $customer['ad'] . ' ' . $customer['soyad'] . ' müşterisine ' . 
                ($type === 'email' ? 'email' : 'SMS') . ' gönderildi',
                'mesaj',
                'musteriler.php?id=' . $customer['id']
            ]);
            
        } catch(Exception $e) {
            $failedCount++;
            $results[] = [
                'id' => $customer['id'],
                'name' => $customer['ad'] . ' ' . $customer['soyad'],
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => $successCount . ' müşteriye ' . ($type === 'email' ? 'email' : 'SMS') . ' gönderildi',
        'data' => [
            'total' => count($customers),
            'success' => $successCount,
            'failed' => $failedCount,
            'results' => $results
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>