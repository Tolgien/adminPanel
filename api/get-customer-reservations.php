<?php
// api/get-customer-reservations.php
require_once '../config/database.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ID']);
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            r.*,
            o.oda_no, o.oda_adi, o.oda_tipi,
            DATEDIFF(r.cikis_tarihi, r.giris_tarihi) as gece_sayisi,
            (SELECT COUNT(*) FROM rezervasyonlar WHERE musteri_id = ?) as toplam_rezervasyon
        FROM rezervasyonlar r
        LEFT JOIN odalar o ON r.oda_id = o.id
        WHERE r.musteri_id = ?
        ORDER BY r.created_at DESC
    ");
    
    $stmt->execute([$id, $id]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // İstatistikler
    $stats = [
        'toplam_rezervasyon' => count($reservations),
        'toplam_gece' => 0,
        'toplam_harcama' => 0,
        'ortalama_gece' => 0,
        'ortalama_harcama' => 0
    ];
    
    foreach($reservations as $res) {
        $stats['toplam_gece'] += $res['gece_sayisi'];
        $stats['toplam_harcama'] += $res['toplam_fiyat'];
    }
    
    if($stats['toplam_rezervasyon'] > 0) {
        $stats['ortalama_gece'] = round($stats['toplam_gece'] / $stats['toplam_rezervasyon'], 1);
        $stats['ortalama_harcama'] = round($stats['toplam_harcama'] / $stats['toplam_rezervasyon'], 2);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reservations' => $reservations,
            'statistics' => $stats
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>