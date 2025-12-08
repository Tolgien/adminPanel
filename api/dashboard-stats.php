<?php
// api/dashboard-stats.php
session_start();

require_once '../config/database.php';

// Header'ları en başta ayarla
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// OPTIONS isteği için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Başlangıç zamanı
$startTime = microtime(true);

try {
    // Database bağlantısını al - SADECE BU SATIRI KULLAN
    $db = getDB();
    
    // Tüm istatistikleri toplamak için dizi
    $stats = [];
    $currentDate = date('Y-m-d');
    $currentYear = date('Y');
    $currentMonth = date('m');
    
    // 1. ODA İSTATİSTİKLERİ
    $roomStats = $db->query("
        SELECT 
            COUNT(*) as toplam_oda,
            SUM(CASE WHEN durum = 'dolu' THEN 1 ELSE 0 END) as dolu_oda,
            SUM(CASE WHEN durum = 'bos' THEN 1 ELSE 0 END) as bos_oda,
            SUM(CASE WHEN durum = 'bakimda' THEN 1 ELSE 0 END) as bakimdaki_oda,
            SUM(CASE WHEN durum = 'temizlik' THEN 1 ELSE 0 END) as temizlikteki_oda
        FROM odalar 
        WHERE aktif = 1
    ")->fetch();
    
    $stats['oda_istatistikleri'] = [
        'toplam_oda' => (int)($roomStats['toplam_oda'] ?? 0),
        'dolu_oda' => (int)($roomStats['dolu_oda'] ?? 0),
        'bos_oda' => (int)($roomStats['bos_oda'] ?? 0),
        'bakimdaki_oda' => (int)($roomStats['bakimdaki_oda'] ?? 0),
        'temizlikteki_oda' => (int)($roomStats['temizlikteki_oda'] ?? 0),
        'doluluk_orani' => ($roomStats['toplam_oda'] ?? 0) > 0 ? 
            round((($roomStats['dolu_oda'] ?? 0) / ($roomStats['toplam_oda'] ?? 1)) * 100, 2) : 0
    ];
    
    // 2. GÜNLÜK İSTATİSTİKLER
    $dailyStats = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM rezervasyonlar WHERE giris_tarihi = CURDATE() AND durum = 'onaylandi') as bugun_checkin,
            (SELECT COUNT(*) FROM rezervasyonlar WHERE cikis_tarihi = CURDATE() AND durum = 'onaylandi') as bugun_checkout,
            (SELECT COALESCE(SUM(toplam_fiyat), 0) FROM rezervasyonlar WHERE DATE(created_at) = CURDATE() AND durum = 'onaylandi') as bugun_gelir,
            (SELECT COUNT(*) FROM rezervasyonlar WHERE DATE(created_at) = CURDATE() AND durum = 'beklemede') as bugun_yeni_rezervasyon
        FROM DUAL
    ")->fetch();
    
    $stats['gunluk_istatistikler'] = [
        'bugun_checkin' => (int)($dailyStats['bugun_checkin'] ?? 0),
        'bugun_checkout' => (int)($dailyStats['bugun_checkout'] ?? 0),
        'bugun_gelir' => (float)($dailyStats['bugun_gelir'] ?? 0),
        'bugun_yeni_rezervasyon' => (int)($dailyStats['bugun_yeni_rezervasyon'] ?? 0)
    ];
    
    // 3. GELİR İSTATİSTİKLERİ
    $revenueStats = $db->query("
        SELECT 
            COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN toplam_fiyat ELSE 0 END), 0) as aylik_gelir,
            COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN toplam_fiyat ELSE 0 END), 0) as haftalik_gelir,
            COALESCE(SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN toplam_fiyat ELSE 0 END), 0) as yillik_gelir,
            COALESCE(SUM(toplam_fiyat), 0) as toplam_gelir
        FROM rezervasyonlar 
        WHERE durum = 'onaylandi'
    ")->fetch();
    
    $stats['gelir_istatistikleri'] = [
        'bugun_gelir' => (float)($dailyStats['bugun_gelir'] ?? 0),
        'haftalik_gelir' => (float)($revenueStats['haftalik_gelir'] ?? 0),
        'aylik_gelir' => (float)($revenueStats['aylik_gelir'] ?? 0),
        'yillik_gelir' => (float)($revenueStats['yillik_gelir'] ?? 0),
        'toplam_gelir' => (float)($revenueStats['toplam_gelir'] ?? 0)
    ];
    
    // 4. REZERVASYON İSTATİSTİKLERİ
    $reservationStats = $db->query("
        SELECT 
            COUNT(*) as toplam_rezervasyon,
            SUM(CASE WHEN durum = 'onaylandi' THEN 1 ELSE 0 END) as aktif_rezervasyon,
            SUM(CASE WHEN durum = 'beklemede' THEN 1 ELSE 0 END) as beklemede_rezervasyon,
            SUM(CASE WHEN durum = 'iptal' THEN 1 ELSE 0 END) as iptal_rezervasyon,
            SUM(CASE WHEN durum = 'tamamlandi' THEN 1 ELSE 0 END) as tamamlanan_rezervasyon
        FROM rezervasyonlar
    ")->fetch();
    
    $stats['rezervasyon_istatistikleri'] = [
        'toplam_rezervasyon' => (int)($reservationStats['toplam_rezervasyon'] ?? 0),
        'aktif_rezervasyon' => (int)($reservationStats['aktif_rezervasyon'] ?? 0),
        'beklemede_rezervasyon' => (int)($reservationStats['beklemede_rezervasyon'] ?? 0),
        'iptal_rezervasyon' => (int)($reservationStats['iptal_rezervasyon'] ?? 0),
        'tamamlanan_rezervasyon' => (int)($reservationStats['tamamlanan_rezervasyon'] ?? 0)
    ];
    
    // 5. MÜŞTERİ İSTATİSTİKLERİ
    $customerStats = $db->query("
        SELECT 
            COUNT(*) as toplam_musteri,
            COUNT(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) as yeni_musteri,
            (SELECT COUNT(DISTINCT musteri_id) FROM rezervasyonlar WHERE YEAR(created_at) = YEAR(CURDATE())) as aktif_musteri
        FROM musteriler
    ")->fetch();
    
    $stats['musteri_istatistikleri'] = [
        'toplam_musteri' => (int)($customerStats['toplam_musteri'] ?? 0),
        'yeni_musteri' => (int)($customerStats['yeni_musteri'] ?? 0),
        'aktif_musteri' => (int)($customerStats['aktif_musteri'] ?? 0)
    ];
    
    // 6. SON 5 REZERVASYON
    $lastReservations = $db->query("
        SELECT 
            r.id,
            r.rezervasyon_no,
            r.giris_tarihi,
            r.cikis_tarihi,
            r.toplam_fiyat,
            r.durum,
            r.odeme_durumu,
            CONCAT(m.ad, ' ', m.soyad) as musteri_adi,
            m.email,
            m.telefon,
            o.oda_no,
            o.oda_adi,
            o.oda_tipi,
            DATEDIFF(r.cikis_tarihi, r.giris_tarihi) as konaklama_suresi
        FROM rezervasyonlar r
        LEFT JOIN musteriler m ON r.musteri_id = m.id
        LEFT JOIN odalar o ON r.oda_id = o.id
        WHERE r.durum IN ('onaylandi', 'beklemede')
        ORDER BY r.created_at DESC
        LIMIT 5
    ")->fetchAll();
    
    // Tarih formatını düzenle
    foreach ($lastReservations as &$reservation) {
        $reservation['giris_tarihi_formatted'] = date('d.m.Y', strtotime($reservation['giris_tarihi']));
        $reservation['cikis_tarihi_formatted'] = date('d.m.Y', strtotime($reservation['cikis_tarihi']));
        $reservation['toplam_fiyat_formatted'] = number_format($reservation['toplam_fiyat'], 2, ',', '.') . ' ₺';
        
        // Durum renkleri
        $statusColors = [
            'onaylandi' => 'success',
            'beklemede' => 'warning',
            'iptal' => 'danger',
            'tamamlandi' => 'info'
        ];
        $reservation['durum_renk'] = $statusColors[$reservation['durum']] ?? 'secondary';
        
        // Ödeme durumu renkleri
        $paymentColors = [
            'tamam' => 'success',
            'kismi' => 'warning',
            'bekliyor' => 'danger'
        ];
        $reservation['odeme_durumu_renk'] = $paymentColors[$reservation['odeme_durumu']] ?? 'secondary';
    }
    
    $stats['son_rezervasyonlar'] = $lastReservations;
    
    // 7. ODA TİPİ DAĞILIMI
    $roomTypes = $db->query("
        SELECT 
            oda_tipi,
            COUNT(*) as sayi,
            ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM odalar WHERE aktif = 1)), 2) as yuzde
        FROM odalar 
        WHERE aktif = 1 
        GROUP BY oda_tipi
        ORDER BY sayi DESC
    ")->fetchAll();
    
    // Oda tipi renkleri
    $roomTypeColors = [
        'standart' => '#3b82f6',
        'deluxe' => '#8b5cf6',
        'suite' => '#10b981',
        'aile' => '#f59e0b'
    ];
    
    // Oda tipi çeviri fonksiyonu
    function translateRoomType($type) {
        $translations = [
            'standart' => 'Standart',
            'deluxe' => 'Deluxe',
            'suite' => 'Süit',
            'aile' => 'Aile'
        ];
        return $translations[$type] ?? $type;
    }
    
    foreach ($roomTypes as &$roomType) {
        $roomType['renk'] = $roomTypeColors[$roomType['oda_tipi']] ?? '#6b7280';
        $roomType['oda_tipi_tr'] = translateRoomType($roomType['oda_tipi']);
    }
    
    $stats['oda_tipleri'] = $roomTypes;
    
    // 8. AYLIK DOLULUK ORANI (Alternatif yöntem)
    try {
        // Stored procedure yerine direkt sorgu
        $monthlyOccupancy = $db->query("
            SELECT 
                o.id,
                o.oda_no,
                o.oda_adi,
                COUNT(mt.id) as rezerve_gun,
                DAY(LAST_DAY(CURDATE())) as toplam_gun,
                ROUND((COUNT(mt.id) / DAY(LAST_DAY(CURDATE()))) * 100, 2) as doluluk_orani
            FROM odalar o
            LEFT JOIN musaitlik_takvimi mt ON o.id = mt.oda_id 
                AND YEAR(mt.tarih) = YEAR(CURDATE()) 
                AND MONTH(mt.tarih) = MONTH(CURDATE())
                AND mt.durum = 'rezerve'
            WHERE o.aktif = 1
            GROUP BY o.id, o.oda_no, o.oda_adi
            ORDER BY doluluk_orani DESC
        ")->fetchAll();
        
        // Toplam doluluk oranı hesapla
        $totalOccupancy = 0;
        $roomCount = 0;
        foreach ($monthlyOccupancy as $room) {
            if ($room['doluluk_orani'] > 0) {
                $totalOccupancy += $room['doluluk_orani'];
                $roomCount++;
            }
        }
        
        $stats['aylik_doluluk'] = [
            'veriler' => $monthlyOccupancy,
            'ortalama_doluluk' => $roomCount > 0 ? round($totalOccupancy / $roomCount, 2) : 0
        ];
        
    } catch (Exception $e) {
        $stats['aylik_doluluk'] = [
            'veriler' => [],
            'ortalama_doluluk' => 0,
            'hata' => ENVIRONMENT === 'development' ? $e->getMessage() : null
        ];
    }
    
    // 9. HAFTALIK DOLULUK TRENDİ (Basitleştirilmiş)
    $weeklyTrend = $db->query("
        SELECT 
            DATE(tarih) as gun,
            COUNT(DISTINCT oda_id) as dolu_oda_sayisi,
            (SELECT COUNT(*) FROM odalar WHERE aktif = 1) as toplam_oda,
            ROUND((COUNT(DISTINCT oda_id) * 100.0 / (SELECT COUNT(*) FROM odalar WHERE aktif = 1)), 2) as doluluk_orani
        FROM musaitlik_takvimi 
        WHERE durum = 'rezerve' 
            AND tarih >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND tarih <= CURDATE()
        GROUP BY DATE(tarih)
        ORDER BY gun
    ")->fetchAll();
    
    // Eksik günleri tamamla
    $completeTrend = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $found = false;
        
        foreach ($weeklyTrend as $day) {
            if ($day['gun'] == $date) {
                $day['gun_tarih'] = date('d.m', strtotime($day['gun']));
                $day['gun_ad'] = date('D', strtotime($day['gun']));
                $completeTrend[] = $day;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $completeTrend[] = [
                'gun' => $date,
                'gun_tarih' => date('d.m', strtotime($date)),
                'gun_ad' => date('D', strtotime($date)),
                'dolu_oda_sayisi' => 0,
                'toplam_oda' => $stats['oda_istatistikleri']['toplam_oda'],
                'doluluk_orani' => 0
            ];
        }
    }
    
    $stats['haftalik_trend'] = $completeTrend;
    
    // 10. YAKLAŞAN CHECK-IN'LER
    $upcomingCheckins = $db->query("
        SELECT 
            r.id,
            r.rezervasyon_no,
            r.giris_tarihi,
            r.cikis_tarihi,
            CONCAT(m.ad, ' ', m.soyad) as musteri_adi,
            o.oda_no,
            o.oda_adi,
            DATEDIFF(r.giris_tarihi, CURDATE()) as kac_gun_kaldi
        FROM rezervasyonlar r
        LEFT JOIN musteriler m ON r.musteri_id = m.id
        LEFT JOIN odalar o ON r.oda_id = o.id
        WHERE r.durum = 'onaylandi'
            AND r.giris_tarihi > CURDATE()
            AND r.giris_tarihi <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        ORDER BY r.giris_tarihi ASC
        LIMIT 5
    ")->fetchAll();
    
    $stats['yaklasan_checkinler'] = $upcomingCheckins;
    
    // Sorgu süresi
    $endTime = microtime(true);
    $queryTime = round(($endTime - $startTime) * 1000, 2);
    
    // BAŞARILI YANIT
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $stats,
        'performance' => [
            'query_time_ms' => $queryTime,
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    // Database hatası
    $errorResponse = [
        'success' => false,
        'error' => [
            'code' => 'DB_ERROR',
            'message' => 'Database error',
            'details' => ENVIRONMENT === 'development' ? $e->getMessage() : null
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Genel hata
    $errorResponse = [
        'success' => false,
        'error' => [
            'code' => 'GENERAL_ERROR',
            'message' => 'An unexpected error occurred',
            'details' => ENVIRONMENT === 'development' ? $e->getMessage() : null
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
}
?>