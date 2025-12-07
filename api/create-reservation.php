<?php
// api/get-reservation.php
require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    die('Geçersiz ID');
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            r.*,
            m.*,
            o.*,
            k.ad as admin_ad, k.soyad as admin_soyad,
            DATEDIFF(r.cikis_tarihi, r.giris_tarihi) as gece_sayisi
        FROM rezervasyonlar r
        LEFT JOIN musteriler m ON r.musteri_id = m.id
        LEFT JOIN odalar o ON r.oda_id = o.id
        LEFT JOIN kullanicilar k ON r.created_by = k.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$id]);
    $rezervasyon = $stmt->fetch();
    
    if(!$rezervasyon) {
        die('Rezervasyon bulunamadı');
    }
    
    // Durum etiketleri
    $durumlar = [
        'beklemede' => ['text' => 'Beklemede', 'color' => 'yellow'],
        'onaylandi' => ['text' => 'Onaylandı', 'color' => 'green'],
        'iptal' => ['text' => 'İptal Edildi', 'color' => 'red'],
        'tamamlandi' => ['text' => 'Tamamlandı', 'color' => 'blue']
    ];
    
    $durum = $durumlar[$rezervasyon['durum']] ?? $durumlar['beklemede'];
    
    // Ödeme durumu
    $odeme_durumlari = [
        'bekliyor' => ['text' => 'Ödeme Bekliyor', 'color' => 'red'],
        'kismi' => ['text' => 'Kısmi Ödendi', 'color' => 'yellow'],
        'tamam' => ['text' => 'Tamamı Ödendi', 'color' => 'green']
    ];
    
    $odeme = $odeme_durumlari[$rezervasyon['odeme_durumu']] ?? $odeme_durumlari['bekliyor'];
    
    ?>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Rezervasyon Detayı</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Müşteri Bilgileri -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    <i class="fas fa-user mr-2"></i> Müşteri Bilgileri
                </h4>
                <div class="space-y-2">
                    <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($rezervasyon['ad'] . ' ' . $rezervasyon['soyad']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($rezervasyon['email']); ?></p>
                    <p><strong>Telefon:</strong> <?php echo htmlspecialchars($rezervasyon['telefon']); ?></p>
                    <?php if($rezervasyon['tc_kimlik']): ?>
                    <p><strong>TC Kimlik:</strong> <?php echo htmlspecialchars($rezervasyon['tc_kimlik']); ?></p>
                    <?php endif; ?>
                    <?php if($rezervasyon['dogum_tarihi']): ?>
                    <p><strong>Doğum Tarihi:</strong> <?php echo htmlspecialchars($rezervasyon['dogum_tarihi']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Rezervasyon Bilgileri -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    <i class="fas fa-calendar-alt mr-2"></i> Rezervasyon Bilgileri
                </h4>
                <div class="space-y-2">
                    <p><strong>Rezervasyon No:</strong> <?php echo htmlspecialchars($rezervasyon['rezervasyon_no']); ?></p>
                    <p><strong>Oda:</strong> <?php echo htmlspecialchars($rezervasyon['oda_no'] . ' - ' . $rezervasyon['oda_adi']); ?></p>
                    <p><strong>Giriş Tarihi:</strong> <?php echo htmlspecialchars($rezervasyon['giris_tarihi']); ?></p>
                    <p><strong>Çıkış Tarihi:</strong> <?php echo htmlspecialchars($rezervasyon['cikis_tarihi']); ?></p>
                    <p><strong>Gece Sayısı:</strong> <?php echo $rezervasyon['gece_sayisi']; ?></p>
                    <p><strong>Kişi Sayısı:</strong> <?php echo $rezervasyon['kisi_sayisi']; ?></p>
                </div>
            </div>
            
            <!-- Ödeme Bilgileri -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    <i class="fas fa-money-bill-wave mr-2"></i> Ödeme Bilgileri
                </h4>
                <div class="space-y-2">
                    <p><strong>Toplam Tutar:</strong> ₺<?php echo number_format($rezervasyon['toplam_fiyat'], 2); ?></p>
                    <p><strong>İndirim:</strong> ₺<?php echo number_format($rezervasyon['indirim'], 2); ?></p>
                    <p><strong>Ödenen:</strong> ₺<?php echo number_format($rezervasyon['odenen'], 2); ?></p>
                    <p><strong>Kalan:</strong> ₺<?php echo number_format($rezervasyon['kalan'], 2); ?></p>
                    <p>
                        <strong>Ödeme Durumu:</strong> 
                        <span class="px-2 py-1 text-xs bg-<?php echo $odeme['color']; ?>-100 text-<?php echo $odeme['color']; ?>-800 rounded-full">
                            <?php echo $odeme['text']; ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Durum ve Sistem -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    <i class="fas fa-info-circle mr-2"></i> Durum ve Sistem
                </h4>
                <div class="space-y-2">
                    <p>
                        <strong>Durum:</strong> 
                        <span class="px-2 py-1 text-xs bg-<?php echo $durum['color']; ?>-100 text-<?php echo $durum['color']; ?>-800 rounded-full">
                            <?php echo $durum['text']; ?>
                        </span>
                    </p>
                    <p><strong>Oluşturulma Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($rezervasyon['created_at'])); ?></p>
                    <?php if($rezervasyon['admin_ad']): ?>
                    <p><strong>Oluşturan:</strong> <?php echo htmlspecialchars($rezervasyon['admin_ad'] . ' ' . $rezervasyon['admin_soyad']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Notlar -->
        <?php if($rezervasyon['ozel_notlar'] || $rezervasyon['admin_notlari']): ?>
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-3">Notlar</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if($rezervasyon['ozel_notlar']): ?>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-700 mb-2">Müşteri Notları</h5>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($rezervasyon['ozel_notlar'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if($rezervasyon['admin_notlari']): ?>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-700 mb-2">Admin Notları</h5>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($rezervasyon['admin_notlari'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- İşlem Butonları -->
        <div class="mt-8 flex justify-end space-x-4">
            <button onclick="printInvoice(<?php echo $rezervasyon['id']; ?>)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i> Yazdır
            </button>
            <button onclick="editReservation(<?php echo $rezervasyon['id']; ?>)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i> Düzenle
            </button>
            <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Kapat
            </button>
        </div>
    </div>
    <?php
    
} catch(PDOException $e) {
    echo '<div class="p-6 text-red-600">Hata: ' . $e->getMessage() . '</div>';
}
?>