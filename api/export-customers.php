<?php
// api/export-customers.php
require_once '../config/database.php';

// Seçilen ID'ler
$ids = isset($_GET['ids']) ? $_GET['ids'] : '';

try {
    $db = getDB();
    
    // SQL sorgusu
    $sql = "SELECT 
                m.*,
                COUNT(r.id) as rezervasyon_sayisi,
                SUM(r.toplam_fiyat) as toplam_harcama,
                MIN(r.giris_tarihi) as ilk_rezervasyon,
                MAX(r.giris_tarihi) as son_rezervasyon
            FROM musteriler m
            LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id";
    
    if(!empty($ids)) {
        $idArray = explode(',', $ids);
        $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
        $sql .= " WHERE m.id IN ($placeholders)";
        $params = $idArray;
    } else {
        $params = [];
    }
    
    $sql .= " GROUP BY m.id ORDER BY m.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // CSV çıktısı
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=musteriler_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Başlıklar
    $headers = [
        'ID', 'Ad', 'Soyad', 'Email', 'Telefon', 'TC Kimlik', 'Doğum Tarihi',
        'Cinsiyet', 'Adres', 'Şehir', 'Ülke', 'Rezervasyon Sayısı',
        'Toplam Harcama', 'İlk Rezervasyon', 'Son Rezervasyon', 'Kayıt Tarihi'
    ];
    
    fputcsv($output, $headers);
    
    // Veriler
    foreach($customers as $customer) {
        $row = [
            $customer['id'],
            $customer['ad'],
            $customer['soyad'],
            $customer['email'],
            $customer['telefon'],
            $customer['tc_kimlik'] ?? '',
            $customer['dogum_tarihi'] ?? '',
            $customer['cinsiyet'] ?? '',
            $customer['adres'] ?? '',
            $customer['sehir'] ?? '',
            $customer['ulke'] ?? '',
            $customer['rezervasyon_sayisi'] ?? 0,
            $customer['toplam_harcama'] ?? 0,
            $customer['ilk_rezervasyon'] ?? '',
            $customer['son_rezervasyon'] ?? '',
            $customer['created_at']
        ];
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} catch(PDOException $e) {
    echo "Export hatası: " . $e->getMessage();
}
?>