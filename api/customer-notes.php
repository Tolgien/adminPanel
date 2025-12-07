<?php
// api/customer-notes.php
require_once '../config/database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'get';
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

if($customer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz müşteri ID']);
    exit;
}

try {
    $db = getDB();
    
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Notları getir
        $stmt = $db->prepare("SELECT notlar FROM musteriler WHERE id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch();
        
        if(!$customer) {
            echo json_encode(['success' => false, 'message' => 'Müşteri bulunamadı']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'notes' => $customer['notlar']
            ]
        ]);
        
    } elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Not ekle/güncelle
        $notes = trim($_POST['notes'] ?? '');
        
        $stmt = $db->prepare("UPDATE musteriler SET notlar = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$notes, $customer_id]);
        
        // Aktivite log'u
        $logStmt = $db->prepare("
            INSERT INTO bildirimler 
            (kullanici_id, baslik, mesaj, tip, link)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $logStmt->execute([
            $_SESSION['user_id'] ?? null,
            'Müşteri Notları Güncellendi',
            'Müşteri #' . $customer_id . ' için notlar güncellendi',
            'sistem',
            'musteriler.php?id=' . $customer_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notlar güncellendi'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>