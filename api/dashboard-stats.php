<?php
// api/dashboard-stats.php
require_once "../config/database.php";
header("Content-Type: application/json");

try {
    $db = getDB();
    
    $data = [
        "toplam_oda" => 0,
        "dolu_oda" => 0,
        "bos_oda" => 0,
        "doluluk_orani" => 0,
        "bugun_checkin" => 0,
        "aylik_gelir" => "0.00",
        "aktif_misafir" => 0,
        "ortalama_kalis" => 0
    ];
    
    // Sorguları try-catch içinde yapalım
    try {
        $stmt = $db->query("SELECT COUNT(*) as toplam FROM odalar WHERE aktif = 1");
        $result = $stmt->fetch();
        $data["toplam_oda"] = $result["toplam"] ?? 0;
    } catch(Exception $e) {}
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as dolu FROM odalar WHERE durum = 'dolu' AND aktif = 1");
        $result = $stmt->fetch();
        $data["dolu_oda"] = $result["dolu"] ?? 0;
        $data["bos_oda"] = $data["toplam_oda"] - $data["dolu_oda"];
        $data["doluluk_orani"] = $data["toplam_oda"] > 0 ? round(($data["dolu_oda"] / $data["toplam_oda"]) * 100, 2) : 0;
    } catch(Exception $e) {}
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as checkin FROM rezervasyonlar WHERE DATE(giris_tarihi) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch();
        $data["bugun_checkin"] = $result["checkin"] ?? 0;
    } catch(Exception $e) {}
    
    echo json_encode([
        "success" => true,
        "message" => "Başarılı",
        "data" => $data
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Veritabanı hatası: " . $e->getMessage(),
        "data" => [
            "toplam_oda" => 0,
            "dolu_oda" => 0,
            "bos_oda" => 0,
            "doluluk_orani" => 0,
            "bugun_checkin" => 0,
            "aylik_gelir" => "0.00",
            "aktif_misafir" => 0,
            "ortalama_kalis" => 0
        ]
    ]);
}
?>