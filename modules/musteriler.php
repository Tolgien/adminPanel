<?php
// modules/musteriler.php
require_once '../config/database.php';
?>
<div class="space-y-8">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Müşteri Yönetimi</h1>
            <p class="text-gray-600 mt-1">Tüm müşterileri görüntüleyin ve yönetin</p>
        </div>
        <div class="flex space-x-3">
            <button class="new-customer-btn modern-btn modern-btn-primary">
                <i class="fas fa-plus mr-2"></i> Yeni Müşteri
            </button>
            <button class="export-customers-btn px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
                <i class="fas fa-download mr-2"></i> Export
            </button>
            <button class="import-customers-btn px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                <i class="fas fa-upload mr-2"></i> Import
            </button>
        </div>
    </div>
    
    <!-- İstatistik Kartları -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <?php
        $db = getDB();
        
        // Toplam müşteri
        $stmt = $db->query("SELECT COUNT(*) FROM musteriler");
        $toplam = $stmt->fetchColumn();
        
        // Bugün eklenenler
        $stmt = $db->query("SELECT COUNT(*) FROM musteriler WHERE DATE(created_at) = CURDATE()");
        $bugun_eklenen = $stmt->fetchColumn();
        
        // En çok rezervasyon yapan
        $stmt = $db->query("
            SELECT COUNT(*) as rezervasyon_sayisi 
            FROM rezervasyonlar 
            GROUP BY musteri_id 
            ORDER BY rezervasyon_sayisi DESC 
            LIMIT 1
        ");
        $en_cok_rezervasyon = $stmt->fetchColumn() ?: 0;
        
        // Aktif müşteriler (son 30 günde rezervasyon yapan)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT musteri_id) 
            FROM rezervasyonlar 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $aktif_musteri = $stmt->fetchColumn();
        ?>
        
        <div class="filter-customer-card aesthetic-card p-4 cursor-pointer" data-filter="all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-800"><?php echo $toplam; ?></div>
                    <div class="text-sm text-gray-600">Toplam Müşteri</div>
                </div>
                <div class="text-blue-500 text-2xl">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="filter-customer-card aesthetic-card p-4 cursor-pointer" data-filter="today">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-800"><?php echo $bugun_eklenen; ?></div>
                    <div class="text-sm text-gray-600">Bugün Eklenen</div>
                </div>
                <div class="text-green-500 text-2xl">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
        
        <div class="aesthetic-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-800"><?php echo $en_cok_rezervasyon; ?></div>
                    <div class="text-sm text-gray-600">En Çok Rezervasyon</div>
                </div>
                <div class="text-purple-500 text-2xl">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
        </div>
        
        <div class="aesthetic-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-800"><?php echo $aktif_musteri; ?></div>
                    <div class="text-sm text-gray-600">Aktif Müşteri (30 gün)</div>
                </div>
                <div class="text-orange-500 text-2xl">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtre ve Arama -->
    <div class="aesthetic-card p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <!-- Hızlı Filtreler -->
            <div class="flex space-x-2">
                <button class="customer-filter-btn px-3 py-1 bg-violet-100 text-violet-700 rounded-lg text-sm" data-filter="all">
                    Tümü
                </button>
                <button class="customer-filter-btn px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-sm" data-filter="vip">
                    VIP
                </button>
                <button class="customer-filter-btn px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm" data-filter="frequent">
                    Sık Gelen
                </button>
                <button class="customer-filter-btn px-3 py-1 bg-pink-100 text-pink-700 rounded-lg text-sm" data-filter="birthday">
                    Doğum Günü Yakın
                </button>
            </div>
            
            <!-- Arama -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="customer-search" placeholder="Ad, soyad, email, telefon veya TC ile ara..." 
                           class="w-full px-4 py-2 pl-10 border rounded-lg focus:border-violet-400 customer-search-input">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <!-- İleri Filtreler -->
            <button class="toggle-advanced-filters-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                <i class="fas fa-filter mr-2"></i> İleri Filtreler
            </button>
            
            <button class="reset-filters-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-redo mr-2"></i> Sıfırla
            </button>
        </div>
        
        <!-- İleri Filtreler (Gizli) -->
        <div id="advanced-filters" class="mt-4 hidden">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şehir</label>
                    <select id="city-filter" class="w-full px-3 py-2 border rounded-lg focus:border-violet-400 customer-filter-select">
                        <option value="">Tüm Şehirler</option>
                        <?php
                        $stmt = $db->query("SELECT DISTINCT sehir FROM musteriler WHERE sehir IS NOT NULL AND sehir != '' ORDER BY sehir");
                        while($city = $stmt->fetch()):
                        ?>
                        <option value="<?php echo htmlspecialchars($city['sehir']); ?>">
                            <?php echo htmlspecialchars($city['sehir']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cinsiyet</label>
                    <select id="gender-filter" class="w-full px-3 py-2 border rounded-lg focus:border-violet-400 customer-filter-select">
                        <option value="">Tümü</option>
                        <option value="erkek">Erkek</option>
                        <option value="kadin">Kadın</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kayıt Tarihi</label>
                    <select id="registration-date-filter" class="w-full px-3 py-2 border rounded-lg focus:border-violet-400 customer-filter-select">
                        <option value="">Tüm Zamanlar</option>
                        <option value="today">Bugün</option>
                        <option value="week">Bu Hafta</option>
                        <option value="month">Bu Ay</option>
                        <option value="year">Bu Yıl</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                    <select id="sort-filter" class="w-full px-3 py-2 border rounded-lg focus:border-violet-400 customer-filter-select">
                        <option value="created_at_desc">En Yeni</option>
                        <option value="created_at_asc">En Eski</option>
                        <option value="name_asc">Ad (A-Z)</option>
                        <option value="name_desc">Ad (Z-A)</option>
                        <option value="reservation_desc">Çok Rezervasyon</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Müşteri Listesi -->
    <div class="aesthetic-card p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all-customers" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İletişim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kişisel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İstatistikler</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="customers-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- AJAX ile dolacak -->
                </tbody>
            </table>
        </div>
        
        <!-- Toplu İşlemler -->
        <div id="batch-actions" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span id="selected-count" class="font-medium text-gray-700">0 müşteri seçildi</span>
                    <select id="batch-action-select" class="px-3 py-2 border rounded-lg focus:border-violet-400 text-sm">
                        <option value="">Toplu İşlem Seçin</option>
                        <option value="export">Seçilenleri Export Et</option>
                        <option value="email">Toplu Email Gönder</option>
                        <option value="sms">Toplu SMS Gönder</option>
                        <option value="add_tag">Etiket Ekle</option>
                        <option value="remove_tag">Etiket Kaldır</option>
                        <option value="delete">Seçilenleri Sil</option>
                    </select>
                    <button class="apply-batch-action-btn px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 text-sm">
                        Uygula
                    </button>
                </div>
                <button class="clear-selection-btn text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i> Temizle
                </button>
            </div>
        </div>
        
        <!-- Sayfalama -->
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Gösterilen: <span id="shown-customers">0</span> / Toplam: <span id="total-customers">0</span>
            </div>
            <div class="flex space-x-2">
                <button id="prev-page-btn" class="px-3 py-1 bg-gray-100 rounded disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination" class="flex space-x-1"></div>
                <button id="next-page-btn" class="px-3 py-1 bg-gray-100 rounded disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Müşteri Modal -->
<div id="new-customer-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900" id="customer-modal-title">Yeni Müşteri Ekle</h3>
                    <button class="modal-close-btn text-gray-400 hover:text-gray-600" data-modal="new-customer-modal">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form id="new-customer-form">
                    <input type="hidden" id="customer-id" name="id" value="">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Temel Bilgiler -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Temel Bilgiler</h4>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad *</label>
                                    <input type="text" name="ad" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Soyad *</label>
                                    <input type="text" name="soyad" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                                <input type="tel" name="telefon" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                            </div>
                        </div>
                        
                        <!-- Kişisel Bilgiler -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Kişisel Bilgiler</h4>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">TC Kimlik No</label>
                                <input type="text" name="tc_kimlik" pattern="[0-9]{11}" maxlength="11"
                                       class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Doğum Tarihi</label>
                                    <input type="date" name="dogum_tarihi" 
                                           class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cinsiyet</label>
                                    <select name="cinsiyet" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                        <option value="">Seçiniz</option>
                                        <option value="erkek">Erkek</option>
                                        <option value="kadin">Kadın</option>
                                        <option value="diger">Diğer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Adres Bilgileri -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Adres Bilgileri</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                                <textarea name="adres" rows="2" 
                                          class="w-full px-4 py-2 border rounded-lg focus:border-violet-400"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Şehir</label>
                                    <input type="text" name="sehir" 
                                           class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ülke</label>
                                    <input type="text" name="ulke" value="Türkiye"
                                           class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notlar ve Etiketler -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Notlar ve Etiketler</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
                                <textarea name="notlar" rows="3" 
                                          class="w-full px-4 py-2 border rounded-lg focus:border-violet-400"
                                          placeholder="Müşteri hakkında özel notlar..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Etiketler</label>
                                <div class="border rounded-lg p-3 min-h-[100px]">
                                    <div id="selected-tags" class="flex flex-wrap gap-2 mb-2">
                                        <!-- Seçilen etiketler buraya gelecek -->
                                    </div>
                                    <div class="relative">
                                        <input type="text" id="tag-input" placeholder="Etiket ekle..." 
                                               class="w-full px-3 py-1 border rounded focus:border-violet-400 text-sm">
                                        <div id="tag-suggestions" class="absolute z-10 bg-white border rounded-lg shadow-lg hidden w-full"></div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        Önerilen etiketler: 
                                        <span class="suggested-tag cursor-pointer text-violet-600" data-tag="VIP">VIP</span>, 
                                        <span class="suggested-tag cursor-pointer text-violet-600" data-tag="Sık Gelen">Sık Gelen</span>, 
                                        <span class="suggested-tag cursor-pointer text-violet-600" data-tag="Kurumsal">Kurumsal</span>
                                    </div>
                                </div>
                                <input type="hidden" name="etiketler" id="tags-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-4">
                        <button type="button" class="modal-close-btn px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50" data-modal="new-customer-modal">
                            İptal
                        </button>
                        <button type="submit" id="save-customer-btn" class="px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700">
                            <i class="fas fa-save mr-2"></i> Müşteriyi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Müşteri Detay Modal -->
<div id="customer-detail-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl">
            <div id="customer-detail-content">
                <!-- AJAX ile yüklenecek -->
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Müşteri Import</h3>
                    <button class="modal-close-btn text-gray-400 hover:text-gray-600" data-modal="import-modal">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div class="space-y-6">
                    <!-- Adım 1: Dosya Yükle -->
                    <div id="import-step-1">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">1. Excel/CSV Dosyasını Yükle</h4>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                            <i class="fas fa-file-excel text-4xl text-green-500 mb-4"></i>
                            <p class="text-gray-600 mb-4">Excel veya CSV dosyanızı buraya sürükleyin veya seçin</p>
                            <input type="file" id="import-file" accept=".csv,.xlsx,.xls" class="hidden">
                            <button class="select-import-file-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-upload mr-2"></i> Dosya Seç
                            </button>
                            <p class="text-xs text-gray-500 mt-4">Desteklenen formatlar: CSV, XLSX, XLS</p>
                        </div>
                        <div id="file-info" class="mt-4 hidden">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                        <span id="file-name" class="font-medium"></span>
                                        <span id="file-size" class="text-sm text-gray-600 ml-2"></span>
                                    </div>
                                    <button class="remove-import-file-btn text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <button class="preview-import-btn mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                    <i class="fas fa-eye mr-2"></i> Önizleme
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Adım 2: Önizleme -->
                    <div id="import-step-2" class="hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">2. Verileri Kontrol Edin</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="overflow-x-auto max-h-64">
                                <table class="min-w-full text-sm" id="import-preview">
                                    <!-- Önizleme tablosu buraya gelecek -->
                                </table>
                            </div>
                            <div class="mt-4 text-sm text-gray-600">
                                <span id="preview-count">0</span> kayıt bulundu
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between">
                            <button class="back-to-step-1-btn px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i> Geri
                            </button>
                            <button class="start-import-btn px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700">
                                <i class="fas fa-database mr-2"></i> Verileri Import Et
                            </button>
                        </div>
                    </div>
                    
                    <!-- Adım 3: Sonuç -->
                    <div id="import-step-3" class="hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">3. Import Sonucu</h4>
                        <div id="import-result" class="bg-gray-50 p-4 rounded-lg">
                            <!-- Sonuçlar buraya gelecek -->
                        </div>
                        <div class="mt-4 text-center">
                            <button class="modal-close-btn px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" data-modal="import-modal">
                                <i class="fas fa-check mr-2"></i> Tamam
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SMS/Email Gönder Modal -->
<div id="message-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900" id="message-modal-title"></h3>
                    <button class="modal-close-btn text-gray-400 hover:text-gray-600" data-modal="message-modal">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form id="message-form">
                    <input type="hidden" id="message-type" name="type">
                    <input type="hidden" id="selected-customers" name="customers">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alıcılar</label>
                            <div id="recipient-list" class="bg-gray-50 p-3 rounded-lg text-sm"></div>
                            <p class="text-xs text-gray-500 mt-1" id="recipient-count">0 müşteri seçildi</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Konu</label>
                            <input type="text" id="message-subject" name="subject" 
                                   class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mesaj</label>
                            <textarea id="message-content" name="content" rows="6" 
                                      class="w-full px-4 py-2 border rounded-lg focus:border-violet-400"></textarea>
                            <p class="text-xs text-gray-500 mt-1" id="char-count">0 karakter</p>
                        </div>
                        
                        <div id="template-section" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Şablon Seç</label>
                            <select id="message-template" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                <option value="">Özel Mesaj</option>
                                <option value="welcome">Hoş Geldiniz Mesajı</option>
                                <option value="birthday">Doğum Günü Tebriği</option>
                                <option value="promotion">Kampanya Duyurusu</option>
                                <option value="survey">Memnuniyet Anketi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-4">
                        <button type="button" class="modal-close-btn px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50" data-modal="message-modal">
                            İptal
                        </button>
                        <button type="submit" class="send-message-btn px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700">
                            <i class="fas fa-paper-plane mr-2"></i> Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Global değişkenler
let currentPage = 1;
let currentFilter = 'all';
let totalPages = 1;
const perPage = 15;
let selectedCustomers = new Set();
let selectedTags = new Set();
let importData = null;
let isEditing = false;
let currentEditId = 0;

// Sayfa yüklendiğinde
$(document).ready(function() {
    // Müşterileri yükle
    loadCustomers();
    
    // Event delegation ile tüm click event'larını yönet
    initEventListeners();
    
    // Arama için debounce
    initSearchDebounce();
});

// Event listener'ları başlat
function initEventListeners() {
    // Modal kapatma butonları
    $(document).on('click', '.modal-close-btn', function() {
        const modalId = $(this).data('modal');
        $('#' + modalId).addClass('hidden');
        
        // Eğer yeni müşteri modalıysa formu resetle
        if(modalId === 'new-customer-modal') {
            resetCustomerForm();
        }
    });
    
    // Modal dışına tıklayınca kapat
    $(document).on('click', '.fixed.inset-0', function(e) {
        if(e.target === this) {
            $(this).addClass('hidden');
            if($(this).attr('id') === 'new-customer-modal') {
                resetCustomerForm();
            }
        }
    });
    
    // ESC tuşu ile kapat
    $(document).on('keyup', function(e) {
        if(e.key === 'Escape') {
            $('.fixed.inset-0').addClass('hidden');
            resetCustomerForm();
        }
    });
    
    // Yeni müşteri butonu
    $(document).on('click', '.new-customer-btn', function() {
        $('#customer-modal-title').text('Yeni Müşteri Ekle');
        $('#save-customer-btn').html('<i class="fas fa-save mr-2"></i> Müşteriyi Kaydet');
        isEditing = false;
        currentEditId = 0;
        $('#new-customer-modal').removeClass('hidden');
    });
    
    // Müşteri formu submit
    $(document).on('submit', '#new-customer-form', function(e) {
        e.preventDefault();
        if(isEditing) {
            updateCustomer(currentEditId);
        } else {
            createCustomer();
        }
    });
    
    // Filtre butonları
    $(document).on('click', '.customer-filter-btn, .filter-customer-card', function() {
        const filter = $(this).data('filter');
        filterCustomers(filter);
        
        // Aktif class'ını güncelle
        $('.customer-filter-btn').removeClass('bg-violet-600 text-white');
        $(this).addClass('bg-violet-600 text-white');
    });
    
    // Müşteri görüntüle
    $(document).on('click', '.view-customer-btn', function() {
        const customerId = $(this).data('id');
        viewCustomer(customerId);
    });
    
    // Müşteri düzenle
    $(document).on('click', '.edit-customer-btn', function() {
        const customerId = $(this).data('id');
        editCustomer(customerId);
    });
    
    // Müşteri sil
    $(document).on('click', '.delete-customer-btn', function() {
        const customerId = $(this).data('id');
        if(confirm('Bu müşteriyi silmek istediğinize emin misiniz?')) {
            deleteCustomer(customerId);
        }
    });
    
    // Sayfalama butonları
    $(document).on('click', '#prev-page-btn', function() {
        if(currentPage > 1) {
            currentPage--;
            loadCustomers();
        }
    });
    
    $(document).on('click', '#next-page-btn', function() {
        if(currentPage < totalPages) {
            currentPage++;
            loadCustomers();
        }
    });
    
    // Tümünü seç
    $(document).on('change', '#select-all-customers', function() {
        const isChecked = $(this).prop('checked');
        $('.customer-checkbox').prop('checked', isChecked);
        updateSelection();
    });
    
    // Bireysel checkbox değişikliği
    $(document).on('change', '.customer-checkbox', function() {
        updateSelection();
    });
    
    // Toplu işlem uygula
    $(document).on('click', '.apply-batch-action-btn', function() {
        applyBatchAction();
    });
    
    // Seçimi temizle
    $(document).on('click', '.clear-selection-btn', function() {
        clearSelection();
    });
    
    // Export butonu
    $(document).on('click', '.export-customers-btn', function() {
        exportCustomers();
    });
    
    // Import butonu
    $(document).on('click', '.import-customers-btn', function() {
        openImportModal();
    });
    
    // Import işlemleri
    $(document).on('click', '.select-import-file-btn', function() {
        $('#import-file').click();
    });
    
    $(document).on('change', '#import-file', handleFileSelect);
    $(document).on('click', '.remove-import-file-btn', removeImportFile);
    $(document).on('click', '.preview-import-btn', previewImport);
    $(document).on('click', '.back-to-step-1-btn', backToImportStep1);
    $(document).on('click', '.start-import-btn', startImport);
    
    // Etiket işlemleri
    $(document).on('click', '.suggested-tag', function() {
        const tag = $(this).data('tag');
        addTag(tag);
    });
    
    $(document).on('keypress', '#tag-input', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            addTag($(this).val());
            $(this).val('');
        }
    });
    
    $(document).on('click', '.remove-tag-btn', function() {
        const tag = $(this).data('tag');
        removeTag(tag);
    });
    
    // Mesaj gönderme
    $(document).on('submit', '#message-form', sendMessage);
    $(document).on('change', '#message-template', loadMessageTemplate);
    $(document).on('input', '#message-content', updateCharCount);
    
    // İleri filtreleri göster/gizle
    $(document).on('click', '.toggle-advanced-filters-btn', function() {
        $('#advanced-filters').slideToggle();
    });
    
    // Filtreleri sıfırla
    $(document).on('click', '.reset-filters-btn', resetFilters);
    
    // Filtre select'lerinde değişiklik
    $(document).on('change', '.customer-filter-select', function() {
        loadCustomers();
    });
}

// Arama için debounce
function initSearchDebounce() {
    let searchTimeout;
    $(document).on('input', '.customer-search-input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadCustomers();
        }, 500);
    });
}

// Müşterileri yükle
function loadCustomers() {
    const search = $('#customer-search').val();
    const city = $('#city-filter').val();
    const gender = $('#gender-filter').val();
    const regDate = $('#registration-date-filter').val();
    const sort = $('#sort-filter').val();
    
    $.ajax({
        url: 'api/get-customers.php',
        type: 'GET',
        data: {
            page: currentPage,
            filter: currentFilter,
            search: search,
            city: city,
            gender: gender,
            reg_date: regDate,
            sort: sort,
            per_page: perPage
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                updateCustomersTable(response.data.customers);
                updatePagination(response.data.pagination);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Sunucu hatası');
        }
    });
}

// Tabloyu güncelle
function updateCustomersTable(customers) {
    const tbody = $('#customers-table-body');
    
    if(customers.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-user-slash text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Müşteri bulunamadı</p>
                    <p class="text-sm mt-2">Yeni müşteri eklemek için "Yeni Müşteri" butonuna tıklayın</p>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    customers.forEach(function(customer) {
        const isSelected = selectedCustomers.has(customer.id);
        
        // Müşteri tipi badge'leri
        let badges = '';
        if(customer.rezervasyon_sayisi >= 5) {
            badges += '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full mr-1">VIP</span>';
        }
        if(customer.rezervasyon_sayisi >= 3) {
            badges += '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full mr-1">Sık Gelen</span>';
        }
        
        // Son rezervasyon tarihi
        let lastReservation = 'Hiç rezervasyon yok';
        if(customer.son_rezervasyon) {
            const date = new Date(customer.son_rezervasyon);
            lastReservation = date.toLocaleDateString('tr-TR');
        }
        
        // Doğum günü kontrolü
        let birthdayInfo = '';
        if(customer.dogum_tarihi) {
            const today = new Date();
            const birthday = new Date(customer.dogum_tarihi);
            const nextBirthday = new Date(today.getFullYear(), birthday.getMonth(), birthday.getDate());
            if(nextBirthday < today) {
                nextBirthday.setFullYear(nextBirthday.getFullYear() + 1);
            }
            const daysUntil = Math.ceil((nextBirthday - today) / (1000 * 60 * 60 * 24));
            
            if(daysUntil <= 30) {
                birthdayInfo = `<div class="text-xs text-pink-600">
                    <i class="fas fa-birthday-cake mr-1"></i> ${daysUntil} gün sonra
                </div>`;
            }
        }
        
        html += `
        <tr class="hover:bg-gray-50 transition ${isSelected ? 'bg-blue-50' : ''}">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="customer-checkbox rounded border-gray-300" 
                       value="${customer.id}" ${isSelected ? 'checked' : ''}>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-violet-500 to-purple-600 flex items-center justify-center text-white font-bold">
                            ${customer.ad.charAt(0)}${customer.soyad.charAt(0)}
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                            ${customer.ad} ${customer.soyad}
                            ${badges}
                        </div>
                        <div class="text-sm text-gray-500">
                            ${customer.tc_kimlik ? 'TC: ' + customer.tc_kimlik + ' • ' : ''}
                            ${customer.cinsiyet ? customer.cinsiyet.charAt(0).toUpperCase() + customer.cinsiyet.slice(1) : ''}
                        </div>
                        ${birthdayInfo}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                    <i class="fas fa-envelope text-gray-400 mr-2"></i> ${customer.email}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-phone text-gray-400 mr-2"></i> ${customer.telefon}
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i> ${customer.sehir || '-'}
                </div>
            </td>
            <td class="px-6 py-4">
                ${customer.dogum_tarihi ? `
                <div class="text-sm text-gray-900">
                    <i class="fas fa-birthday-cake text-gray-400 mr-2"></i> 
                    ${new Date(customer.dogum_tarihi).toLocaleDateString('tr-TR')}
                </div>
                ` : ''}
                <div class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-calendar-plus text-gray-400 mr-2"></i> 
                    ${new Date(customer.created_at).toLocaleDateString('tr-TR')}
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="space-y-1">
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-gray-900">${customer.rezervasyon_sayisi || 0}</span>
                        <span class="text-xs text-gray-500 ml-1">rezervasyon</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Son: ${lastReservation}
                    </div>
                    <div class="text-xs text-gray-500">
                        Toplam harcama: ₺${customer.toplam_harcama || '0'}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button class="view-customer-btn text-blue-600 hover:text-blue-900" data-id="${customer.id}" title="Detaylar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="edit-customer-btn text-green-600 hover:text-green-900" data-id="${customer.id}" title="Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="message-customer-btn text-purple-600 hover:text-purple-900" data-id="${customer.id}" title="Mesaj Gönder">
                        <i class="fas fa-comment"></i>
                    </button>
                    <button class="delete-customer-btn text-red-600 hover:text-red-900" data-id="${customer.id}" title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
    });
    
    tbody.html(html);
    updateSelectionUI();
}

// Sayfalama güncelle
function updatePagination(pagination) {
    totalPages = pagination.total_pages;
    const totalItems = pagination.total_items;
    
    $('#shown-customers').text(pagination.per_page);
    $('#total-customers').text(totalItems);
    
    // Sayfa butonları
    $('#prev-page-btn').prop('disabled', currentPage <= 1);
    $('#next-page-btn').prop('disabled', currentPage >= totalPages);
    
    // Sayfa numaraları
    let pagesHtml = '';
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if(endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    for(let i = startPage; i <= endPage; i++) {
        if(i === currentPage) {
            pagesHtml += `<button class="page-number-btn px-3 py-1 bg-violet-600 text-white rounded" data-page="${i}">${i}</button>`;
        } else {
            pagesHtml += `<button class="page-number-btn px-3 py-1 bg-gray-100 rounded hover:bg-gray-200" data-page="${i}">${i}</button>`;
        }
    }
    
    $('#pagination').html(pagesHtml);
    
    // Sayfa numarası butonlarına event listener ekle
    $(document).on('click', '.page-number-btn', function() {
        const page = $(this).data('page');
        currentPage = page;
        loadCustomers();
    });
}

// Filtrele
function filterCustomers(filter) {
    currentFilter = filter;
    currentPage = 1;
    loadCustomers();
}

// Filtreleri sıfırla
function resetFilters() {
    currentFilter = 'all';
    $('#customer-search').val('');
    $('#city-filter').val('');
    $('#gender-filter').val('');
    $('#registration-date-filter').val('');
    $('#sort-filter').val('created_at_desc');
    $('#advanced-filters').hide();
    currentPage = 1;
    loadCustomers();
}

// Formu resetle
function resetCustomerForm() {
    $('#new-customer-form')[0].reset();
    $('#customer-id').val('');
    selectedTags.clear();
    updateSelectedTags();
    isEditing = false;
    currentEditId = 0;
    $('#customer-modal-title').text('Yeni Müşteri Ekle');
    $('#save-customer-btn').html('<i class="fas fa-save mr-2"></i> Müşteriyi Kaydet');
}

// Yeni müşteri oluştur
function createCustomer() {
    // Etiketleri form verisine ekle
    $('#tags-input').val(Array.from(selectedTags).join(','));
    
    const formData = $('#new-customer-form').serialize();
    
    $.ajax({
        url: 'api/create-customer.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('#save-customer-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Kaydediliyor...');
        },
        success: function(response) {
            if(response.success) {
                showSuccess('Müşteri başarıyla eklendi!');
                $('#new-customer-modal').addClass('hidden');
                resetCustomerForm();
                loadCustomers();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Sunucu hatası');
        },
        complete: function() {
            $('#save-customer-btn').prop('disabled', false)
                .html('<i class="fas fa-save mr-2"></i> Müşteriyi Kaydet');
        }
    });
}

// Müşteri detayını görüntüle
function viewCustomer(id) {
    $.ajax({
        url: 'api/get-customer.php',
        type: 'GET',
        data: { id: id },
        dataType: 'html',
        success: function(response) {
            $('#customer-detail-content').html(response);
            $('#customer-detail-modal').removeClass('hidden');
        },
        error: function() {
            showError('Detaylar yüklenemedi');
        }
    });
}

// Müşteri düzenle
function editCustomer(id) {
    currentEditId = id;
    isEditing = true;
    
    $.ajax({
        url: 'api/get-customer-data.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                fillCustomerForm(response.data);
                $('#customer-modal-title').text('Müşteriyi Düzenle');
                $('#save-customer-btn').html('<i class="fas fa-save mr-2"></i> Müşteriyi Güncelle');
                $('#new-customer-modal').removeClass('hidden');
            }
        },
        error: function() {
            showError('Müşteri verileri yüklenemedi');
        }
    });
}

// Form doldur
function fillCustomerForm(customer) {
    $('#customer-id').val(customer.id);
    $('input[name="ad"]').val(customer.ad);
    $('input[name="soyad"]').val(customer.soyad);
    $('input[name="email"]').val(customer.email);
    $('input[name="telefon"]').val(customer.telefon);
    $('input[name="tc_kimlik"]').val(customer.tc_kimlik);
    $('input[name="dogum_tarihi"]').val(customer.dogum_tarihi);
    $('select[name="cinsiyet"]').val(customer.cinsiyet);
    $('textarea[name="adres"]').val(customer.adres);
    $('input[name="sehir"]').val(customer.sehir);
    $('input[name="ulke"]').val(customer.ulke);
    $('textarea[name="notlar"]').val(customer.notlar);
    
    // Etiketleri ayarla
    if(customer.etiketler) {
        selectedTags = new Set(customer.etiketler.split(','));
        updateSelectedTags();
    }
}

// Müşteri güncelle
function updateCustomer(id) {
    // Etiketleri form verisine ekle
    $('#tags-input').val(Array.from(selectedTags).join(','));
    
    const formData = $('#new-customer-form').serialize();
    
    $.ajax({
        url: 'api/update-customer.php',
        type: 'POST',
        data: formData + '&id=' + id,
        dataType: 'json',
        beforeSend: function() {
            $('#save-customer-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Güncelleniyor...');
        },
        success: function(response) {
            if(response.success) {
                showSuccess('Müşteri başarıyla güncellendi!');
                $('#new-customer-modal').addClass('hidden');
                resetCustomerForm();
                loadCustomers();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Sunucu hatası');
        },
        complete: function() {
            $('#save-customer-btn').prop('disabled', false)
                .html('<i class="fas fa-save mr-2"></i> Müşteriyi Kaydet');
        }
    });
}

// Müşteri sil
function deleteCustomer(id) {
    $.ajax({
        url: 'api/delete-customer.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showSuccess('Müşteri silindi');
                loadCustomers();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Sunucu hatası');
        }
    });
}

// Seçim işlemleri
function updateSelection() {
    selectedCustomers.clear();
    $('.customer-checkbox:checked').each(function() {
        selectedCustomers.add(parseInt($(this).val()));
    });
    updateSelectionUI();
}

function updateSelectionUI() {
    const count = selectedCustomers.size;
    $('#selected-count').text(count + ' müşteri seçildi');
    
    if(count > 0) {
        $('#batch-actions').removeClass('hidden');
        $('#select-all-customers').prop('checked', count === $('.customer-checkbox').length);
    } else {
        $('#batch-actions').addClass('hidden');
        $('#select-all-customers').prop('checked', false);
    }
}

function clearSelection() {
    selectedCustomers.clear();
    $('.customer-checkbox').prop('checked', false);
    updateSelectionUI();
}

// Toplu işlem uygula
function applyBatchAction() {
    const action = $('#batch-action-select').val();
    if(!action) {
        showError('Lütfen bir işlem seçin');
        return;
    }
    
    if(selectedCustomers.size === 0) {
        showError('Lütfen müşteri seçin');
        return;
    }
    
    const ids = Array.from(selectedCustomers).join(',');
    
    switch(action) {
        case 'export':
            exportSelectedCustomers(ids);
            break;
        case 'email':
            openMessageModal('email', ids);
            break;
        case 'sms':
            openMessageModal('sms', ids);
            break;
        case 'delete':
            if(confirm(`Seçilen ${selectedCustomers.size} müşteriyi silmek istediğinize emin misiniz?`)) {
                deleteSelectedCustomers(ids);
            }
            break;
        default:
            showError('Bu işlem henüz implement edilmedi');
    }
}

// Seçilen müşterileri export et
function exportSelectedCustomers(ids) {
    window.open(`api/export-customers.php?ids=${ids}`, '_blank');
}

// Tüm müşterileri export et
function exportCustomers() {
    window.open('api/export-customers.php', '_blank');
}

// Seçilen müşterileri sil
function deleteSelectedCustomers(ids) {
    $.ajax({
        url: 'api/delete-selected-customers.php',
        type: 'POST',
        data: { ids: ids },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showSuccess(response.message);
                clearSelection();
                loadCustomers();
            } else {
                showError(response.message);
            }
        }
    });
}

// Mesaj modalını aç
function openMessageModal(type, ids) {
    $('#message-type').val(type);
    $('#selected-customers').val(ids);
    
    // Modal başlığını ayarla
    const titles = {
        'email': 'Toplu Email Gönder',
        'sms': 'Toplu SMS Gönder'
    };
    $('#message-modal-title').text(titles[type] || 'Mesaj Gönder');
    
    // Alıcı listesini güncelle
    updateRecipientList(ids);
    
    // SMS için şablonu gizle
    if(type === 'sms') {
        $('#template-section').addClass('hidden');
    } else {
        $('#template-section').removeClass('hidden');
    }
    
    $('#message-modal').removeClass('hidden');
}

function updateRecipientList(ids) {
    const count = selectedCustomers.size;
    $('#recipient-count').text(count + ' müşteri seçildi');
    
    // Seçilen müşteri isimlerini getir
    $.ajax({
        url: 'api/get-selected-customers.php',
        type: 'GET',
        data: { ids: ids },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const names = response.data.map(c => `${c.ad} ${c.soyad}`).join(', ');
                $('#recipient-list').text(names);
            }
        }
    });
}

function loadMessageTemplate() {
    const template = $(this).val();
    const templates = {
        'welcome': {
            subject: 'Hoş Geldiniz!',
            content: 'Sayın müşterimiz, otelimize hoş geldiniz. Sizleri ağırlamaktan mutluluk duyuyoruz. Size daha iyi hizmet verebilmek için her zaman yanınızdayız.'
        },
        'birthday': {
            subject: 'Doğum Gününüz Kutlu Olsun!',
            content: 'Sevgili müşterimiz, doğum gününüzü en içten dileklerimizle kutlarız. Size özel %15 indirim kuponunuz: DOGUM2024'
        },
        'promotion': {
            subject: 'Özel Kampanya!',
            content: 'Sadece sizin için hazırladığımız özel kampanyamızı kaçırmayın. Bu ay %20 indirim fırsatı ile sizleri bekliyoruz.'
        },
        'survey': {
            subject: 'Memnuniyet Anketi',
            content: 'Değerli müşterimiz, konaklamanızdan memnun kaldıysanız bizi değerlendirmek ister misiniz? Anketimize katılmak için tıklayın: [LINK]'
        }
    };
    
    if(templates[template]) {
        $('#message-subject').val(templates[template].subject);
        $('#message-content').val(templates[template].content);
        $('#char-count').text(templates[template].content.length + ' karakter');
    }
}

function updateCharCount() {
    $('#char-count').text($(this).val().length + ' karakter');
}

function sendMessage(e) {
    e.preventDefault();
    
    const formData = $('#message-form').serialize();
    
    $.ajax({
        url: 'api/send-customer-message.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('.send-message-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Gönderiliyor...');
        },
        success: function(response) {
            if(response.success) {
                showSuccess('Mesajlar başarıyla gönderildi!');
                $('#message-modal').addClass('hidden');
                clearSelection();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Sunucu hatası');
        },
        complete: function() {
            $('.send-message-btn').prop('disabled', false)
                .html('<i class="fas fa-paper-plane mr-2"></i> Gönder');
        }
    });
}

// Import işlemleri
function openImportModal() {
    $('#import-modal').removeClass('hidden');
    $('#import-step-1').show();
    $('#import-step-2').hide();
    $('#import-step-3').hide();
    resetImport();
}

function resetImport() {
    importData = null;
    $('#import-file').val('');
    $('#file-info').addClass('hidden');
    $('#import-preview').empty();
    $('#preview-count').text('0');
}

function handleFileSelect(e) {
    const file = e.target.files[0];
    if(!file) return;
    
    // Dosya türü kontrolü
    const validTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    if(!validTypes.includes(file.type) && !file.name.match(/\.(csv|xlsx|xls)$/)) {
        showError('Lütfen CSV veya Excel dosyası seçin');
        return;
    }
    
    // Dosya boyutu kontrolü (5MB)
    if(file.size > 5 * 1024 * 1024) {
        showError('Dosya boyutu 5MB\'dan küçük olmalıdır');
        return;
    }
    
    // Dosya bilgilerini göster
    $('#file-name').text(file.name);
    $('#file-size').text(formatFileSize(file.size));
    $('#file-info').removeClass('hidden');
    
    // Dosyayı oku
    const reader = new FileReader();
    reader.onload = function(e) {
        parseImportFile(e.target.result, file.name);
    };
    
    if(file.name.endsWith('.csv')) {
        reader.readAsText(file);
    } else {
        reader.readAsArrayBuffer(file);
    }
}

function parseImportFile(data, filename) {
    // Basit CSV parser
    if(filename.endsWith('.csv')) {
        const lines = data.split('\n');
        const headers = lines[0].split(',').map(h => h.trim());
        importData = [];
        
        for(let i = 1; i < lines.length; i++) {
            if(lines[i].trim() === '') continue;
            
            const values = lines[i].split(',').map(v => v.trim());
            const row = {};
            
            headers.forEach((header, index) => {
                if(values[index]) {
                    row[header] = values[index];
                }
            });
            
            if(Object.keys(row).length > 0) {
                importData.push(row);
            }
        }
    }
    
    showImportPreview();
}

function showImportPreview() {
    if(!importData || importData.length === 0) {
        showError('Dosyada geçerli veri bulunamadı');
        return;
    }
    
    $('#preview-count').text(importData.length + ' kayıt');
    
    // Tablo başlıkları
    const headers = Object.keys(importData[0]);
    let html = '<thead><tr>';
    headers.forEach(header => {
        html += `<th class="px-3 py-2 border-b text-left">${header}</th>`;
    });
    html += '</tr></thead><tbody>';
    
    // İlk 10 satırı göster
    const previewRows = importData.slice(0, 10);
    previewRows.forEach(row => {
        html += '<tr>';
        headers.forEach(header => {
            html += `<td class="px-3 py-2 border-b">${row[header] || ''}</td>`;
        });
        html += '</tr>';
    });
    
    if(importData.length > 10) {
        html += `<tr><td colspan="${headers.length}" class="px-3 py-2 text-center text-gray-500">
            ... ve ${importData.length - 10} daha
        </td></tr>`;
    }
    
    html += '</tbody>';
    $('#import-preview').html(html);
    
    $('#import-step-1').hide();
    $('#import-step-2').show();
}

function removeImportFile() {
    resetImport();
}

function previewImport() {
    showImportPreview();
}

function backToImportStep1() {
    $('#import-step-2').hide();
    $('#import-step-3').hide();
    $('#import-step-1').show();
}

function startImport() {
    $.ajax({
        url: 'api/import-customers.php',
        type: 'POST',
        data: {
            data: JSON.stringify(importData)
        },
        dataType: 'json',
        beforeSend: function() {
            $('.start-import-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Import Ediliyor...');
        },
        success: function(response) {
            if(response.success) {
                $('#import-result').html(`
                    <div class="text-green-600 mb-2">
                        <i class="fas fa-check-circle mr-2"></i> Import başarılı!
                    </div>
                    <div class="text-sm">
                        <p>Toplam: ${response.data.total} kayıt</p>
                        <p>Başarılı: <span class="text-green-600">${response.data.success}</span></p>
                        <p>Başarısız: <span class="text-red-600">${response.data.failed}</span></p>
                        ${response.data.errors ? `
                        <div class="mt-2 p-2 bg-red-50 rounded">
                            <p class="font-medium">Hatalar:</p>
                            <ul class="text-xs">
                                ${response.data.errors.map(e => `<li>${e}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                `);
                
                $('#import-step-2').hide();
                $('#import-step-3').show();
                loadCustomers(); // Listeyi yenile
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Import sırasında hata oluştu');
        },
        complete: function() {
            $('.start-import-btn').prop('disabled', false)
                .html('<i class="fas fa-database mr-2"></i> Verileri Import Et');
        }
    });
}

// Etiket işlemleri
function addTag(tag) {
    if(tag.trim() === '') return;
    selectedTags.add(tag.trim());
    updateSelectedTags();
    $('#tag-input').val('');
    $('#tag-suggestions').addClass('hidden');
}

function removeTag(tag) {
    selectedTags.delete(tag);
    updateSelectedTags();
}

function updateSelectedTags() {
    const container = $('#selected-tags');
    container.empty();
    
    selectedTags.forEach(tag => {
        container.append(`
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-violet-100 text-violet-800">
                ${tag}
                <button class="remove-tag-btn ml-1 text-violet-600 hover:text-violet-900" data-tag="${tag}">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `);
    });
    
    $('#tags-input').val(Array.from(selectedTags).join(','));
}

// Dosya boyutu formatı
function formatFileSize(bytes) {
    if(bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Mesaj göster
function showError(message) {
    alert('Hata: ' + message);
}

function showSuccess(message) {
    alert('Başarılı: ' + message);
}
</script>
<script>
// NÜKLEER ÇÖZÜM - Tüm click event'larını yeniden bağla
$(document).ready(function() {
    // 1. Sayfa yüklendiğinde tüm event'ları bağla
    bindAllEvents();
    
    // 2. Her 2 saniyede bir event'ları kontrol et (AJAX içerikleri için)
    setInterval(bindAllEvents, 2000);
    
    // 3. AJAX complete event'inde de bağla
    $(document).ajaxComplete(function() {
        setTimeout(bindAllEvents, 500);
    });
});

function bindAllEvents() {
    console.log('Tüm eventlar yeniden bağlanıyor...');
    
    // === MODAL KAPATMA ===
    // X butonları
    $('.modal-close-btn, .detail-modal-close-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.fixed.inset-0').addClass('hidden');
        console.log('Modal X butonu ile kapandı');
        return false;
    });
    
    // Kapat butonları
    $('button:contains("Kapat"), button:contains("İptal")').not('.new-customer-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.fixed.inset-0').addClass('hidden');
        console.log('Modal Kapat butonu ile kapandı');
        return false;
    });
    
    // === DÜZENLE BUTONLARI ===
    // Tablodaki düzenle
    $('.edit-customer-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        console.log('Düzenle tıklandı:', id);
        editCustomer(id);
        return false;
    });
    
    // Detay modalındaki düzenle
    $('.edit-customer-action-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        console.log('Detay modalı düzenle tıklandı:', id);
        $('#customer-detail-modal').addClass('hidden');
        setTimeout(() => editCustomer(id), 300);
        return false;
    });
    
    // === GÖRÜNTÜLE BUTONLARI ===
    $('.view-customer-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        console.log('Görüntüle tıklandı:', id);
        viewCustomer(id);
        return false;
    });
    
    // === YENİ MÜŞTERİ ===
    $('.new-customer-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        resetCustomerForm();
        $('#new-customer-modal').removeClass('hidden');
        console.log('Yeni müşteri modalı açıldı');
        return false;
    });
    
    // === ESC TUŞU ===
    $(document).off('keyup.modal').on('keyup.modal', function(e) {
        if(e.key === 'Escape') {
            $('.fixed.inset-0:visible').addClass('hidden');
            console.log('ESC ile modal kapandı');
        }
    });
    
    // === MODAL DIŞI TIKLAMA ===
    $('.fixed.inset-0').off('click').on('click', function(e) {
        if(e.target === this) {
            $(this).addClass('hidden');
            console.log('Modal dışı tıklama ile kapandı');
        }
    });
}

// Fallback: Eğer hiçbir şey çalışmazsa bu fonksiyonu çağır
function forceCloseModal(modalId) {
    $('#' + modalId).addClass('hidden');
    console.log('Zorla modal kapandı:', modalId);
}

// Tüm modal kapatma butonlarına manuel click event'i ekle
function addManualClickEvents() {
    // Detay modalı kapat
    $('#customer-detail-modal').on('click', '.detail-modal-close-btn, button:contains("Kapat")', function() {
        $('#customer-detail-modal').addClass('hidden');
    });
    
    // Yeni müşteri modalı kapat
    $('#new-customer-modal').on('click', '.modal-close-btn, button:contains("İptal")', function() {
        $('#new-customer-modal').addClass('hidden');
        resetCustomerForm();
    });
}

// Sayfa yüklendiğinde manuel event'leri de ekle
$(window).on('load', function() {
    addManualClickEvents();
    console.log('Manuel eventler eklendi');
});
</script>