<?php
// api/customer-statistics.php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Toplam müşteri sayısı
    $stmt = $db->query("SELECT COUNT(*) FROM musteriler");
    $total_customers = $stmt->fetchColumn();
    
    // Bugün eklenenler
    $stmt = $db->query("SELECT COUNT(*) FROM musteriler WHERE DATE(created_at) = CURDATE()");
    $today_added = $stmt->fetchColumn();
    
    // Bu ay eklenenler
    $stmt = $db->query("SELECT COUNT(*) FROM musteriler WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $month_added = $stmt->fetchColumn();
    
    // Cinsiyet dağılımı
    $stmt = $db->query("SELECT cinsiyet, COUNT(*) as count FROM musteriler WHERE cinsiyet IS NOT NULL GROUP BY cinsiyet");
    $gender_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Şehir dağılımı (top 10)
    $stmt = $db->query("SELECT sehir, COUNT(*) as count FROM musteriler WHERE sehir IS NOT NULL AND sehir != '' GROUP BY sehir ORDER BY count DESC LIMIT 10");
    $city_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Rezervasyon sayısına göre dağılım
    $reservation_distribution = [
        ['range' => '0', 'label' => 'Hiç rezervasyon yapmamış', 'count' => 0],
        ['range' => '1', 'label' => '1 rezervasyon', 'count' => 0],
        ['range' => '2-4', 'label' => '2-4 rezervasyon', 'count' => 0],
        ['range' => '5-9', 'label' => '5-9 rezervasyon', 'count' => 0],
        ['range' => '10+', 'label' => '10+ rezervasyon', 'count' => 0]
    ];
    
    $stmt = $db->query("
        SELECT 
            m.id,
            COUNT(r.id) as reservation_count
        FROM musteriler m
        LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
        GROUP BY m.id
    ");
    
    $customer_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($customer_reservations as $cr) {
        $count = $cr['reservation_count'];
        
        if($count == 0) {
            $reservation_distribution[0]['count']++;
        } elseif($count == 1) {
            $reservation_distribution[1]['count']++;
        } elseif($count >= 2 && $count <= 4) {
            $reservation_distribution[2]['count']++;
        } elseif($count >= 5 && $count <= 9) {
            $reservation_distribution[3]['count']++;
        } else {
            $reservation_distribution[4]['count']++;
        }
    }
    
    // Aylık müşteri artışı (son 12 ay)
    $monthly_growth = [];
    for($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM musteriler 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$month]);
        $count = $stmt->fetchColumn();
        
        $monthly_growth[] = [
            'month' => $month,
            'count' => $count,
            'label' => date('M Y', strtotime($month . '-01'))
        ];
    }
    
    // VIP müşteriler (5+ rezervasyon)
    $stmt = $db->query("
        SELECT 
            m.*,
            COUNT(r.id) as reservation_count,
            SUM(r.toplam_fiyat) as total_spent
        FROM musteriler m
        LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
        GROUP BY m.id
        HAVING reservation_count >= 5
        ORDER BY reservation_count DESC
        LIMIT 10
    ");
    $vip_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // En çok harcama yapanlar
    $stmt = $db->query("
        SELECT 
            m.*,
            COUNT(r.id) as reservation_count,
            SUM(r.toplam_fiyat) as total_spent
        FROM musteriler m
        LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
        GROUP BY m.id
        HAVING total_spent > 0
        ORDER BY total_spent DESC
        LIMIT 10
    ");
    $top_spenders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_customers' => $total_customers,
            'today_added' => $today_added,
            'month_added' => $month_added,
            'gender_distribution' => $gender_distribution,
            'city_distribution' => $city_distribution,
            'reservation_distribution' => $reservation_distribution,
            'monthly_growth' => $monthly_growth,
            'vip_customers' => $vip_customers,
            'top_spenders' => $top_spenders
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>