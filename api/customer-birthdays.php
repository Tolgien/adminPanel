<?php
// api/customer-birthdays.php
require_once '../config/database.php';
header('Content-Type: application/json');

$range = isset($_GET['range']) ? $_GET['range'] : 'month'; // today, week, month

try {
    $db = getDB();
    
    $sql = "SELECT 
                id, ad, soyad, email, telefon, dogum_tarihi,
                YEAR(CURDATE()) - YEAR(dogum_tarihi) as yas,
                DATE_FORMAT(dogum_tarihi, '%m-%d') as dogum_gunu
            FROM musteriler 
            WHERE dogum_tarihi IS NOT NULL";
    
    switch($range) {
        case 'today':
            $sql .= " AND MONTH(dogum_tarihi) = MONTH(CURDATE()) AND DAY(dogum_tarihi) = DAY(CURDATE())";
            break;
        case 'week':
            $sql .= " AND DATE_FORMAT(dogum_tarihi, '%m-%d') BETWEEN 
                      DATE_FORMAT(CURDATE(), '%m-%d') AND 
                      DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')";
            break;
        case 'month':
        default:
            $sql .= " AND MONTH(dogum_tarihi) = MONTH(CURDATE())";
            break;
    }
    
    $sql .= " ORDER BY MONTH(dogum_tarihi), DAY(dogum_tarihi)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Kalan günleri hesapla
    foreach($birthdays as &$birthday) {
        $today = new DateTime();
        $birthDate = new DateTime($birthday['dogum_tarihi']);
        $nextBirthday = new DateTime($today->format('Y') . '-' . $birthDate->format('m-d'));
        
        if($nextBirthday < $today) {
            $nextBirthday->modify('+1 year');
        }
        
        $daysUntil = $today->diff($nextBirthday)->days;
        $birthday['days_until'] = $daysUntil;
        $birthday['next_birthday'] = $nextBirthday->format('Y-m-d');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $birthdays,
        'count' => count($birthdays)
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>