<?php
// api/update-customer.php
require_once '../config/database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek methodu']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz müşteri ID']);
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
    
    // Email benzersizlik kontrolü (kendisi hariç)
    $stmt = $db->prepare("SELECT id FROM musteriler WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if($stmt->fetch()) {
        throw new Exception('Bu email adresi başka bir müşteri tarafından kullanılıyor');
    }
    
    // Etiketleri notlara ekle
    if(!empty($etiketler)) {
        $tags = explode(',', $etiketler);
        $tagString = '';
        foreach($tags as $tag) {
            $tag = trim($tag);
            if(!empty($tag)) {
                $tagString .= '#' . $tag . ' ';
            }
        }
        
        if(!empty($tagString)) {
            $notlar = $tagString . "\n" . $notlar;
        }
    }
    
    // Müşteriyi güncelle
    $stmt = $db->prepare("
        UPDATE musteriler SET 
            ad = ?, soyad = ?, email = ?, telefon = ?, 
            tc_kimlik = ?, dogum_tarihi = ?, cinsiyet = ?, 
            adres = ?, sehir = ?, ulke = ?, notlar = ?,
            updated_at = NOW()
        WHERE id = ?
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
        $notlar,
        $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Müşteri başarıyla güncellendi',
        'data' => ['musteri_id' => $id]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>