<?php
// api/get-customer.php
require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    die('<div class="p-6 text-red-600">Geçersiz ID</div>');
}

try {
    $db = getDB();
    
    // Müşteri bilgilerini getir
    $stmt = $db->prepare("
        SELECT 
            m.*,
            COUNT(r.id) as rezervasyon_sayisi,
            SUM(r.toplam_fiyat) as toplam_harcama,
            MIN(r.giris_tarihi) as ilk_rezervasyon,
            MAX(r.giris_tarihi) as son_rezervasyon
        FROM musteriler m
        LEFT JOIN rezervasyonlar r ON m.id = r.musteri_id
        WHERE m.id = ?
        GROUP BY m.id
    ");
    
    $stmt->execute([$id]);
    $musteri = $stmt->fetch();
    
    if(!$musteri) {
        die('<div class="p-6 text-red-600">Müşteri bulunamadı</div>');
    }
    
    // Müşterinin rezervasyonlarını getir
    $stmt = $db->prepare("
        SELECT 
            r.*,
            o.oda_no, o.oda_adi,
            DATEDIFF(r.cikis_tarihi, r.giris_tarihi) as gece_sayisi
        FROM rezervasyonlar r
        LEFT JOIN odalar o ON r.oda_id = o.id
        WHERE r.musteri_id = ?
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$id]);
    $rezervasyonlar = $stmt->fetchAll();
    
    // Yaş hesapla
    $yas = null;
    if($musteri['dogum_tarihi']) {
        $birthDate = new DateTime($musteri['dogum_tarihi']);
        $today = new DateTime();
        $yas = $today->diff($birthDate)->y;
    }
    
    ?>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Müşteri Detayları</h3>
                <p class="text-gray-600">#<?php echo $musteri['id']; ?> - <?php echo htmlspecialchars($musteri['ad'] . ' ' . $musteri['soyad']); ?></p>
            </div>
            <button class="detail-modal-close-btn text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sol Kolon: Profil ve İstatistikler -->
            <div class="space-y-6">
                <!-- Profil Kartı -->
                <div class="aesthetic-card p-6">
                    <div class="flex flex-col items-center text-center">
                        <div class="h-24 w-24 rounded-full bg-gradient-to-r from-violet-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold mb-4">
                            <?php echo strtoupper(substr($musteri['ad'], 0, 1) . substr($musteri['soyad'], 0, 1)); ?>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($musteri['ad'] . ' ' . $musteri['soyad']); ?></h4>
                        <p class="text-gray-600"><?php echo htmlspecialchars($musteri['email']); ?></p>
                        
                        <div class="flex space-x-2 mt-4">
                            <?php if($musteri['rezervasyon_sayisi'] >= 5): ?>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                <i class="fas fa-crown mr-1"></i> VIP
                            </span>
                            <?php endif; ?>
                            
                            <?php if($musteri['rezervasyon_sayisi'] >= 3): ?>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                <i class="fas fa-star mr-1"></i> Sık Gelen
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo $musteri['rezervasyon_sayisi']; ?></div>
                                <div class="text-sm text-gray-600">Rezervasyon</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900">₺<?php echo number_format($musteri['toplam_harcama'], 0); ?></div>
                                <div class="text-sm text-gray-600">Toplam Harcama</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hızlı İşlemler -->
                <div class="aesthetic-card p-4">
                    <h5 class="font-semibold text-gray-800 mb-3">Hızlı İşlemler</h5>
                    <div class="space-y-2">
                        <button class="send-email-btn w-full text-left px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm" data-id="<?php echo $musteri['id']; ?>">
                            <i class="fas fa-envelope mr-2"></i> Email Gönder
                        </button>
                        <button class="send-sms-btn w-full text-left px-3 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 text-sm" data-id="<?php echo $musteri['id']; ?>">
                            <i class="fas fa-comment mr-2"></i> SMS Gönder
                        </button>
                        <button class="create-reservation-btn w-full text-left px-3 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 text-sm" data-id="<?php echo $musteri['id']; ?>">
                            <i class="fas fa-calendar-plus mr-2"></i> Yeni Rezervasyon
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Orta Kolon: Kişisel Bilgiler -->
            <div class="space-y-6">
                <!-- İletişim Bilgileri -->
                <div class="aesthetic-card p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-address-card mr-2"></i> İletişim Bilgileri
                    </h4>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-gray-400 mt-1 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Email</div>
                                <div class="text-gray-900"><?php echo htmlspecialchars($musteri['email']); ?></div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-phone text-gray-400 mt-1 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Telefon</div>
                                <div class="text-gray-900"><?php echo htmlspecialchars($musteri['telefon']); ?></div>
                            </div>
                        </div>
                        <?php if($musteri['adres']): ?>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-gray-400 mt-1 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Adres</div>
                                <div class="text-gray-900"><?php echo nl2br(htmlspecialchars($musteri['adres'])); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-start">
                            <i class="fas fa-city text-gray-400 mt-1 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Şehir / Ülke</div>
                                <div class="text-gray-900">
                                    <?php echo htmlspecialchars($musteri['sehir'] ? $musteri['sehir'] . ' / ' . $musteri['ulke'] : $musteri['ulke']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kişisel Bilgiler -->
                <div class="aesthetic-card p-6">
                    <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">
                        <i class="fas fa-user mr-2"></i> Kişisel Bilgiler
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <?php if($musteri['tc_kimlik']): ?>
                        <div>
                            <div class="text-sm font-medium text-gray-700">TC Kimlik No</div>
                            <div class="text-gray-900"><?php echo htmlspecialchars($musteri['tc_kimlik']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($musteri['dogum_tarihi']): ?>
                        <div>
                            <div class="text-sm font-medium text-gray-700">Doğum Tarihi / Yaş</div>
                            <div class="text-gray-900">
                                <?php 
                                echo date('d.m.Y', strtotime($musteri['dogum_tarihi']));
                                echo $yas ? ' (' . $yas . ')' : '';
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($musteri['cinsiyet']): ?>
                        <div>
                            <div class="text-sm font-medium text-gray-700">Cinsiyet</div>
                            <div class="text-gray-900">
                                <?php 
                                $genderText = [
                                    'erkek' => 'Erkek',
                                    'kadin' => 'Kadın',
                                    'diger' => 'Diğer'
                                ];
                                echo $genderText[$musteri['cinsiyet']] ?? $musteri['cinsiyet'];
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <div class="text-sm font-medium text-gray-700">Kayıt Tarihi</div>
                            <div class="text-gray-900">
                                <?php echo date('d.m.Y H:i', strtotime($musteri['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sağ Kolon: Rezervasyon Geçmişi -->
            <div class="space-y-6">
                <!-- Son Rezervasyonlar -->
                <div class="aesthetic-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-history mr-2"></i> Son Rezervasyonlar
                        </h4>
                        <span class="text-sm text-gray-500"><?php echo $musteri['rezervasyon_sayisi']; ?> kayıt</span>
                    </div>
                    
                    <?php if(count($rezervasyonlar) > 0): ?>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <?php foreach($rezervasyonlar as $rez): ?>
                        <div class="border rounded-lg p-3 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($rez['oda_no'] . ' - ' . $rez['oda_adi']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('d.m.Y', strtotime($rez['giris_tarihi'])); ?> - 
                                        <?php echo date('d.m.Y', strtotime($rez['cikis_tarihi'])); ?>
                                        <span class="ml-2">(<?php echo $rez['gece_sayisi']; ?> gece)</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">₺<?php echo number_format($rez['toplam_fiyat'], 0); ?></div>
                                    <div class="text-xs">
                                        <?php 
                                        $statusColors = [
                                            'beklemede' => 'text-yellow-600',
                                            'onaylandi' => 'text-green-600',
                                            'iptal' => 'text-red-600',
                                            'tamamlandi' => 'text-blue-600'
                                        ];
                                        $statusText = [
                                            'beklemede' => 'Beklemede',
                                            'onaylandi' => 'Onaylandı',
                                            'iptal' => 'İptal',
                                            'tamamlandi' => 'Tamamlandı'
                                        ];
                                        ?>
                                        <span class="<?php echo $statusColors[$rez['durum']] ?? 'text-gray-600'; ?>">
                                            <?php echo $statusText[$rez['durum']] ?? $rez['durum']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                <?php echo $rez['rezervasyon_no']; ?> • 
                                <?php echo date('d.m.Y H:i', strtotime($rez['created_at'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if($musteri['rezervasyon_sayisi'] > 10): ?>
                    <div class="mt-4 text-center">
                        <button class="view-all-reservations-btn text-sm text-violet-600 hover:text-violet-800" data-id="<?php echo $musteri['id']; ?>">
                            Tümünü Görüntüle (<?php echo $musteri['rezervasyon_sayisi']; ?>)
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-times text-3xl mb-3"></i>
                        <p>Henüz rezervasyon bulunmuyor</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Notlar -->
                <?php if($musteri['notlar']): ?>
                <div class="aesthetic-card p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-sticky-note mr-2"></i> Notlar
                    </h4>
                    <div class="text-gray-700 whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($musteri['notlar'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- İşlem Butonları -->
        <div class="mt-8 flex justify-end space-x-4">
            <button class="edit-customer-action-btn px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" data-id="<?php echo $musteri['id']; ?>">
                <i class="fas fa-edit mr-2"></i> Düzenle
            </button>
            <button class="detail-modal-close-btn px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Kapat
            </button>
        </div>
    </div>
    <?php
    
} catch(PDOException $e) {
    echo '<div class="p-6 text-red-600">Hata: ' . $e->getMessage() . '</div>';
}
?>