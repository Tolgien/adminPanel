<?php
// setup.php - Tüm gerekli dosyaları oluştur
echo "<h1>Otel Panel Kurulum Aracı</h1>";

// Klasörleri oluştur
$folders = [
    'config',
    'includes',
    'modules',
    'api',
    'assets/js',
    'assets/css'
];

foreach($folders as $folder) {
    if(!is_dir($folder)) {
        mkdir($folder, 0777, true);
        echo "✓ Klasör oluşturuldu: $folder<br>";
    } else {
        echo "✓ Klasör zaten var: $folder<br>";
    }
}

// modules/dashboard.php oluştur
$dashboard_content = '<?php
// modules/dashboard.php
?>
<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="aesthetic-card p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Toplam Oda</h3>
            <p class="text-4xl font-bold text-indigo-600" id="total-rooms">Yükleniyor...</p>
        </div>
        
        <div class="aesthetic-card p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Dolu Oda</h3>
            <p class="text-4xl font-bold text-green-600" id="occupied-rooms">Yükleniyor...</p>
        </div>
        
        <div class="aesthetic-card p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Bugün Check-in</h3>
            <p class="text-4xl font-bold text-blue-600" id="today-checkin">Yükleniyor...</p>
        </div>
        
        <div class="aesthetic-card p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Aylık Gelir</h3>
            <p class="text-4xl font-bold text-purple-600" id="monthly-income">Yükleniyor...</p>
        </div>
    </div>
    
    <div class="aesthetic-card p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Hoş Geldiniz!</h2>
        <p class="text-gray-600">Otel yönetim panelinize başarıyla giriş yaptınız.</p>
    </div>
</div>

<script>
$(document).ready(function() {
    loadDashboardStats();
    
    function loadDashboardStats() {
        $.ajax({
            url: "api/dashboard-stats.php",
            type: "GET",
            dataType: "json",
            success: function(response) {
                console.log("Dashboard verileri:", response);
                if(response.success) {
                    $("#total-rooms").text(response.data.toplam_oda || 0);
                    $("#occupied-rooms").text(response.data.dolu_oda || 0);
                    $("#today-checkin").text(response.data.bugun_checkin || 0);
                    $("#monthly-income").text("₺" + (response.data.aylik_gelir || "0"));
                }
            }
        });
    }
});
</script>';

file_put_contents('modules/dashboard.php', $dashboard_content);
echo "✓ modules/dashboard.php oluşturuldu<br>";

// api/dashboard-stats.php oluştur
$api_content = '<?php
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
        $stmt = $db->query("SELECT COUNT(*) as dolu FROM odalar WHERE durum = \'dolu\' AND aktif = 1");
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
?>';

file_put_contents('api/dashboard-stats.php', $api_content);
echo "✓ api/dashboard-stats.php oluşturuldu<br>";

// includes/header.php oluştur
$header_content = '<!-- includes/header.php -->
<header class="bg-white sticky top-0 z-40 px-8 py-4 flex justify-between items-center aesthetic-card shadow-lg m-4">
    <div class="relative w-1/3">
        <input type="text" placeholder="Rezervasyon, Misafir veya ID ile ara..." class="w-full bg-gray-50 modern-input py-3 pl-12 rounded-xl text-sm border border-gray-200 focus:border-violet-400 transition">
        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
    </div>
    
    <div class="flex items-center space-x-6">
        <button class="modern-btn modern-btn-primary py-2 px-4 text-sm">
            <i class="fas fa-plus mr-2"></i> Yeni Rezervasyon
        </button>
        <div class="relative cursor-pointer text-gray-500 hover:text-violet-600">
            <i class="fas fa-cog text-xl"></i>
        </div>
        <div class="relative cursor-pointer text-gray-500 hover:text-violet-600">
            <i class="fas fa-bell text-xl"></i>
            <span class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500"></span>
        </div>
        <div class="flex items-center space-x-3 cursor-pointer">
            <img class="h-10 w-10 rounded-full object-cover border-2 border-violet-400" src="https://via.placeholder.com/150/5b21b7/ffffff?text=ADM" alt="Admin Profili">
            <div>
                <p class="text-sm font-semibold text-gray-800"><?php echo $_SESSION["user_name"] ?? "Admin"; ?></p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
    </div>
</header>';

file_put_contents('includes/header.php', $header_content);
echo "✓ includes/header.php oluşturuldu<br>";

// includes/sidebar.php oluştur
$sidebar_content = '<!-- includes/sidebar.php -->
<aside class="w-72 bg-white fixed h-full p-6 shadow-2xl z-50 overflow-y-auto">
    <div class="text-3xl font-extrabold text-violet-700 mb-10 tracking-wide border-b pb-4">
        AESTHETICA <span class="text-gray-900">PMS</span>
    </div>

    <nav class="space-y-6">
        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">OTEL OPERASYON</h3>
            <a href="#" class="sidebar-link active flex items-center p-3 rounded-xl transition duration-200" data-page="dashboard">
                <span class="flat-icon-styled mr-3"><i class="fas fa-chart-line"></i></span>
                <span class="text-sm font-semibold">Dashboard İstatistikleri</span>
            </a>
            <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700" data-page="rezervasyonlar">
                <span class="flat-icon-styled mr-3"><i class="fas fa-bookmark"></i></span>
                <span class="text-sm font-medium">Rezervasyonlar</span>
            </a>
        </div>

        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">ODA VE MÜŞTERİ</h3>
            <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700" data-page="odalar">
                <span class="flat-icon-styled mr-3"><i class="fas fa-bed"></i></span>
                <span class="text-sm font-medium">Odalar</span>
            </a>
            <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700" data-page="musteriler">
                <span class="flat-icon-styled mr-3"><i class="fas fa-users"></i></span>
                <span class="text-sm font-medium">Müşteriler</span>
            </a>
        </div>
        
        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">SİSTEM</h3>
            <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700" data-page="kullanicilar">
                <span class="flat-icon-styled mr-3"><i class="fas fa-user-shield"></i></span>
                <span class="text-sm font-medium">Kullanıcılar & Roller</span>
            </a>
        </div>
    </nav>
</aside>';

file_put_contents('includes/sidebar.php', $sidebar_content);
echo "✓ includes/sidebar.php oluşturuldu<br>";

// Diğer modül dosyalarını oluştur
$modules = [
    'rezervasyonlar' => '<div class="p-8"><h1 class="text-3xl font-bold">Rezervasyonlar</h1><p>Rezervasyon modülü yakında eklenecek.</p></div>',
    'odalar' => '<div class="p-8"><h1 class="text-3xl font-bold">Odalar</h1><p>Odalar modülü yakında eklenecek.</p></div>',
    'musteriler' => '<div class="p-8"><h1 class="text-3xl font-bold">Müşteriler</h1><p>Müşteriler modülü yakında eklenecek.</p></div>',
    'kullanicilar' => '<div class="p-8"><h1 class="text-3xl font-bold">Kullanıcılar</h1><p>Kullanıcılar modülü yakında eklenecek.</p></div>'
];

foreach($modules as $module => $content) {
    $file_content = '<?php // modules/' . $module . '.php ?>' . $content;
    file_put_contents("modules/$module.php", $file_content);
    echo "✓ modules/$module.php oluşturuldu<br>";
}

echo "<hr><h2>✅ KURULUM TAMAMLANDI!</h2>";
echo "<p>Artık <a href='index.php'>index.php</a> sayfasına gidebilirsiniz.</p>";
?>