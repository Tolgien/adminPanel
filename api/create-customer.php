<?php
// api/create-customer.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

try {
    $db = getDB();
    
    // Form verilerini al
    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $tc_kimlik = trim($_POST['tc_kimlik'] ?? '');
    $dogum_tarihi = $_POST['dogum_tarihi'] ?? null;
    $cinsiyet = $_POST['cinsiyet'] ?? null;
    $adres = trim($_POST['adres'] ?? '');
    $sehir = trim($_POST['sehir'] ?? '');
    $ulke = trim($_POST['ulke'] ?? 'Türkiye');
    $notlar = trim($_POST['notlar'] ?? '');
    $etiketler = trim($_POST['etiketler'] ?? '');
    
    // Validasyon
    if(empty($ad) || empty($soyad)) {
        throw new Exception('Ad ve soyad gereklidir');
    }
    
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Geçerli bir email adresi girin');
    }
    
    if(empty($telefon)) {
        throw new Exception('Telefon numarası gereklidir');
    }
    
    // TC Kimlik kontrolü
    if(!empty($tc_kimlik) && strlen($tc_kimlik) !== 11) {
        throw new Exception('TC Kimlik numarası 11 haneli olmalıdır');
    }
    
    // Email kontrolü
    $stmt = $db->prepare("SELECT id FROM musteriler WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()) {
        throw new Exception('Bu email adresi zaten kayıtlı');
    }
    
    // Müşteriyi oluştur
    $stmt = $db->prepare("
        INSERT INTO musteriler 
        (ad, soyad, email, telefon, tc_kimlik, dogum_tarihi, cinsiyet, adres, sehir, ulke, notlar)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $ad,
        $soyad,
        $email,
        $telefon,
        empty($tc_kimlik) ? null : $tc_kimlik,
        empty($dogum_tarihi) ? null : $dogum_tarihi,
        $cinsiyet,
        $adres,
        $sehir,
        $ulke,
        $notlar
    ]);
    
    $musteri_id = $db->lastInsertId();
    
    // Etiketleri ekle (eğer varsa)
    if(!empty($etiketler)) {
        $tags = explode(',', $etiketler);
        foreach($tags as $tag) {
            $tag = trim($tag);
            if(!empty($tag)) {
                // Burada etiket tablosuna ekleme yapılabilir
                // Şimdilik notlara ekleyelim
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Müşteri başarıyla oluşturuldu',
        'data' => [
            'musteri_id' => $musteri_id
        ]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>