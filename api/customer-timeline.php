<?php
// api/customer-timeline.php
require_once '../config/database.php';
header('Content-Type: application/json');

$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($customer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz müşteri ID']);
    exit;
}

try {
    $db = getDB();
    
    // Tüm aktiviteleri birleştir
    $timeline = [];
    
    // Müşteri oluşturulma
    $stmt = $db->prepare("SELECT created_at FROM musteriler WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    
    if($customer) {
        $timeline[] = [
            'type' => 'customer_created',
            'title' => 'Müşteri Kaydı Oluşturuldu',
            'description' => 'Sistemde kayıt oluşturuldu',
            'date' => $customer['created_at'],
            'icon' => 'user-plus',
            'color' => 'green'
        ];
    }
    
    // Rezervasyonlar
    $stmt = $db->prepare("
        SELECT 
            r.*,
            o.oda_no, o.oda_adi,
            DATEDIFF(r.cikis_tarihi, r.giris_tarihi) as gece_sayisi
        FROM rezervasyonlar r
        LEFT JOIN odalar o ON r.oda_id = o.id
        WHERE r.musteri_id = ?
        ORDER BY r.created_at DESC
    ");
    
    $stmt->execute([$customer_id]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($reservations as $res) {
        $statusText = [
            'beklemede' => 'Beklemede',
            'onaylandi' => 'Onaylandı',
            'iptal' => 'İptal Edildi',
            'tamamlandi' => 'Tamamlandı'
        ];
        
        $timeline[] = [
            'type' => 'reservation',
            'title' => 'Rezervasyon: ' . $res['rezervasyon_no'],
            'description' => $res['oda_no'] . ' - ' . $res['oda_adi'] . 
                           ' (' . $res['gece_sayisi'] . ' gece) - ' . 
                           ($statusText[$res['durum']] ?? $res['durum']),
            'date' => $res['created_at'],
            'icon' => 'calendar-alt',
            'color' => $res['durum'] === 'onaylandi' ? 'blue' : 
                      ($res['durum'] === 'iptal' ? 'red' : 'yellow'),
            'data' => $res
        ];
    }
    
    // Ödeme işlemleri
    $stmt = $db->prepare("
        SELECT 
            r.rezervasyon_no,
            r.odenen,
            r.odeme_durumu,
            r.updated_at
        FROM rezervasyonlar r
        WHERE r.musteri_id = ? AND r.odenen > 0
        ORDER BY r.updated_at DESC
    ");
    
    $stmt->execute([$customer_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($payments as $payment) {
        $timeline[] = [
            'type' => 'payment',
            'title' => 'Ödeme: ' . $payment['rezervasyon_no'],
            'description' => '₺' . number_format($payment['odenen'], 2) . ' ödeme yapıldı',
            'date' => $payment['updated_at'],
            'icon' => 'money-bill-wave',
            'color' => 'green'
        ];
    }
    
    // Not güncellemeleri
    $stmt = $db->prepare("
        SELECT updated_at 
        FROM musteriler 
        WHERE id = ? AND updated_at != created_at
    ");
    $stmt->execute([$customer_id]);
    $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($updates as $update) {
        $timeline[] = [
            'type' => 'update',
            'title' => 'Bilgiler Güncellendi',
            'description' => 'Müşteri bilgileri güncellendi',
            'date' => $update['updated_at'],
            'icon' => 'edit',
            'color' => 'purple'
        ];
    }
    
    // Tarihe göre sırala
    usort($timeline, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    echo json_encode([
        'success' => true,
        'data' => [
            'timeline' => $timeline,
            'total_activities' => count($timeline)
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>