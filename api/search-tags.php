<?php
// api/search-tags.php
require_once '../config/database.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if(strlen($query) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $db = getDB();
    
    // Tüm müşterilerin notlarından etiketleri çıkar
    $stmt = $db->query("SELECT notlar FROM musteriler WHERE notlar IS NOT NULL AND notlar != ''");
    $allNotes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $allTags = [];
    foreach($allNotes as $note) {
        preg_match_all('/#(\w+)/', $note, $matches);
        if(!empty($matches[1])) {
            foreach($matches[1] as $tag) {
                $allTags[] = strtolower($tag);
            }
        }
    }
    
    $allTags = array_unique($allTags);
    
    // Arama yap
    $suggestions = [];
    foreach($allTags as $tag) {
        if(stripos($tag, $query) !== false) {
            $suggestions[] = ucfirst($tag);
        }
    }
    
    // Sık kullanılan etiketler
    $commonTags = ['VIP', 'Sık Gelen', 'Kurumsal', 'Özel Misafir', 'Yabancı', 'Aile', 'Çocuklu', 'Engelli', 'Uzun Süreli'];
    foreach($commonTags as $tag) {
        if(stripos($tag, $query) !== false && !in_array($tag, $suggestions)) {
            $suggestions[] = $tag;
        }
    }
    
    // Sırala ve limitle
    sort($suggestions);
    $suggestions = array_slice($suggestions, 0, 10);
    
    echo json_encode([
        'success' => true,
        'data' => $suggestions
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>