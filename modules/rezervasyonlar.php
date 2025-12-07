<?php
// modules/rezervasyonlar.php
require_once '../config/database.php';
?>
<div class="space-y-8">
    
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Rezervasyon Yönetimi</h1>
        <button class="modern-btn modern-btn-primary" onclick="openNewReservationModal()">
            <i class="fas fa-plus mr-2"></i> Yeni Rezervasyon
        </button>
    </div>
    
    <!-- Filtre Kartları -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <?php
        $db = getDB();
        $statuses = [
            'all' => ['label' => 'Tüm Rezervasyonlar', 'color' => 'gray'],
            'beklemede' => ['label' => 'Beklemede', 'color' => 'yellow'],
            'onaylandi' => ['label' => 'Onaylandı', 'color' => 'green'],
            'iptal' => ['label' => 'İptal Edilen', 'color' => 'red']
        ];
        
        foreach($statuses as $status => $info):
            if($status === 'all') {
                $stmt = $db->query("SELECT COUNT(*) FROM rezervasyonlar");
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM rezervasyonlar WHERE durum = ?");
                $stmt->execute([$status]);
            }
            $count = $stmt->fetchColumn();
        ?>
        <div class="aesthetic-card p-4 text-center cursor-pointer hover:bg-<?php echo $info['color']; ?>-50 transition" 
             onclick="filterReservations('<?php echo $status; ?>')">
            <div class="text-2xl font-bold text-gray-800"><?php echo $count; ?></div>
            <div class="text-sm text-gray-600"><?php echo $info['label']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Arama ve Kontroller -->
    <div class="aesthetic-card p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Rezervasyon Listesi</h2>
            <div class="flex space-x-3">
                <input type="text" id="search-reservations" placeholder="Rezervasyon no, müşteri adı..." 
                       class="px-4 py-2 border rounded-lg focus:border-violet-400 w-64">
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" onclick="refreshReservations()">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="px-4 py-2 bg-violet-100 text-violet-700 rounded-lg hover:bg-violet-200" onclick="exportReservations()">
                    <i class="fas fa-download mr-2"></i> Export
                </button>
            </div>
        </div>
        
        <!-- Rezervasyon Tablosu -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rezervasyon No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oda</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="reservations-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- AJAX ile dolacak -->
                </tbody>
            </table>
        </div>
        
        <!-- Sayfalama -->
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Gösterilen: <span id="shown-reservations">0</span> / Toplam: <span id="total-reservations">0</span>
            </div>
            <div class="flex space-x-2">
                <button id="prev-page" class="px-3 py-1 bg-gray-100 rounded disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination" class="flex space-x-1"></div>
                <button id="next-page" class="px-3 py-1 bg-gray-100 rounded disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Rezervasyon Modal -->
<div id="new-reservation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Yeni Rezervasyon</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form id="new-reservation-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Müşteri Bilgileri -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Müşteri Bilgileri</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ad *</label>
                                <input type="text" name="ad" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Soyad *</label>
                                <input type="text" name="soyad" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
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
                        
                        <!-- Rezervasyon Bilgileri -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Rezervasyon Bilgileri</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Oda Seçimi *</label>
                                <select name="oda_id" id="room-select" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                    <option value="">Oda Seçin</option>
                                    <?php
                                    $db = getDB();
                                    $stmt = $db->query("SELECT id, oda_no, oda_adi, gunluk_fiyat FROM odalar WHERE aktif = 1 ORDER BY oda_no");
                                    while($oda = $stmt->fetch()):
                                    ?>
                                    <option value="<?php echo $oda['id']; ?>" data-price="<?php echo $oda['gunluk_fiyat']; ?>">
                                        <?php echo $oda['oda_no']; ?> - <?php echo $oda['oda_adi']; ?> (₺<?php echo $oda['gunluk_fiyat']; ?>/gece)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Giriş Tarihi *</label>
                                    <input type="date" name="giris_tarihi" id="checkin-date" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Çıkış Tarihi *</label>
                                    <input type="date" name="cikis_tarihi" id="checkout-date" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kişi Sayısı</label>
                                <select name="kisi_sayisi" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                    <?php for($i=1; $i<=10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i==2 ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Kişi
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Toplam Tutar</label>
                                    <input type="text" id="total-price" class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gece Sayısı</label>
                                    <input type="text" id="night-count" class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Özel Notlar -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Özel Notlar</h4>
                        <textarea name="ozel_notlar" rows="3" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" 
                                  placeholder="Özel istekler, notlar..."></textarea>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-4">
                        <button type="button" onclick="closeModal()" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                            İptal
                        </button>
                        <button type="submit" class="px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700">
                            <i class="fas fa-save mr-2"></i> Rezervasyonu Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rezervasyon Detay Modal -->
<div id="reservation-detail-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl" id="reservation-detail-content">
            <!-- AJAX ile yüklenecek -->
        </div>
    </div>
</div>

<script>
// Değişkenler
let currentPage = 1;
let currentFilter = 'all';
let totalPages = 1;
const perPage = 10;

$(document).ready(function() {
    // Tarihleri bugün ve yarın olarak ayarla
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    
    $('#checkin-date').val(today);
    $('#checkout-date').val(tomorrowStr);
    
    // Rezervasyonları yükle
    loadReservations();
    
    // Form submit
    $('#new-reservation-form').on('submit', function(e) {
        e.preventDefault();
        createReservation();
    });
    
    // Tarih ve oda değişikliklerinde tutarı hesapla
    $('#checkin-date, #checkout-date, #room-select').on('change', calculatePrice);
    
    // Arama input'u
    $('#search-reservations').on('keyup', function(e) {
        if(e.key === 'Enter') {
            currentPage = 1;
            loadReservations();
        }
    });
    
    // Sayfalama butonları
    $('#prev-page').on('click', function() {
        if(currentPage > 1) {
            currentPage--;
            loadReservations();
        }
    });
    
    $('#next-page').on('click', function() {
        if(currentPage < totalPages) {
            currentPage++;
            loadReservations();
        }
    });
});

// Rezervasyonları yükle
function loadReservations() {
    const search = $('#search-reservations').val();
    
    $.ajax({
        url: 'api/get-reservations.php',
        type: 'GET',
        data: {
            page: currentPage,
            filter: currentFilter,
            search: search,
            per_page: perPage
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                updateReservationsTable(response.data.reservations);
                updatePagination(response.data.pagination);
            } else {
                showError(response.message || 'Rezervasyonlar yüklenemedi');
            }
        },
        error: function() {
            showError('Sunucu hatası');
        }
    });
}

// Tabloyu güncelle
function updateReservationsTable(reservations) {
    const tbody = $('#reservations-table-body');
    
    if(reservations.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-calendar-times text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Rezervasyon bulunamadı</p>
                    <p class="text-sm mt-2">Yeni rezervasyon oluşturmak için "Yeni Rezervasyon" butonuna tıklayın</p>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    reservations.forEach(function(res) {
        // Durum renkleri
        const statusConfig = {
            'beklemede': { color: 'yellow', text: 'Beklemede', icon: 'clock' },
            'onaylandi': { color: 'green', text: 'Onaylandı', icon: 'check-circle' },
            'iptal': { color: 'red', text: 'İptal Edildi', icon: 'times-circle' },
            'tamamlandi': { color: 'blue', text: 'Tamamlandı', icon: 'flag-checkered' }
        };
        
        const status = statusConfig[res.durum] || statusConfig.beklemede;
        
        // Ödeme durumu
        let paymentBadge = '';
        switch(res.odeme_durumu) {
            case 'tamam':
                paymentBadge = '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Ödendi</span>';
                break;
            case 'kismi':
                paymentBadge = `<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Kısmi (₺${res.odenen})</span>`;
                break;
            default:
                paymentBadge = '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Ödeme Bekliyor</span>';
        }
        
        html += `
        <tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${res.rezervasyon_no}</div>
                        <div class="text-sm text-gray-500">${formatDate(res.created_at)}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${res.ad} ${res.soyad}</div>
                <div class="text-sm text-gray-500">${res.email}</div>
                <div class="text-sm text-gray-500">${res.telefon || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-medium">${res.oda_no}</div>
                <div class="text-sm text-gray-500">${res.oda_adi}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                    <i class="fas fa-sign-in-alt text-green-500 mr-1"></i> ${res.giris_tarihi}
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-sign-out-alt text-red-500 mr-1"></i> ${res.cikis_tarihi}
                </div>
                <div class="text-xs text-gray-400">${res.gece_sayisi} gece</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-lg font-bold text-gray-900">₺${res.toplam_fiyat}</div>
                <div class="mt-1">${paymentBadge}</div>
                <div class="text-xs text-gray-500 mt-1">Kalan: ₺${res.kalan}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-${status.color}-100 text-${status.color}-800">
                    <i class="fas fa-${status.icon} mr-1"></i> ${status.text}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="viewReservation(${res.id})" class="text-blue-600 hover:text-blue-900" title="Detaylar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editReservation(${res.id})" class="text-green-600 hover:text-green-900" title="Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="confirmDelete(${res.id})" class="text-red-600 hover:text-red-900" title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button onclick="printInvoice(${res.id})" class="text-purple-600 hover:text-purple-900" title="Fatura">
                        <i class="fas fa-receipt"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
    });
    
    tbody.html(html);
}

// Sayfalama güncelle
function updatePagination(pagination) {
    totalPages = pagination.total_pages;
    const totalItems = pagination.total_items;
    
    $('#shown-reservations').text(pagination.per_page);
    $('#total-reservations').text(totalItems);
    
    // Sayfa butonlarını aktif/pasif yap
    $('#prev-page').prop('disabled', currentPage <= 1);
    $('#next-page').prop('disabled', currentPage >= totalPages);
    
    // Sayfa numaralarını göster
    let pagesHtml = '';
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if(endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    for(let i = startPage; i <= endPage; i++) {
        if(i === currentPage) {
            pagesHtml += `<button class="px-3 py-1 bg-violet-600 text-white rounded">${i}</button>`;
        } else {
            pagesHtml += `<button class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200" onclick="goToPage(${i})">${i}</button>`;
        }
    }
    
    $('#pagination').html(pagesHtml);
}

// Sayfaya git
function goToPage(page) {
    currentPage = page;
    loadReservations();
}

// Filtrele
function filterReservations(filter) {
    currentFilter = filter;
    currentPage = 1;
    loadReservations();
    
    // Filtre kartlarını aktif yap
    $('.aesthetic-card').removeClass('border-2 border-violet-500');
    $(event.target).closest('.aesthetic-card').addClass('border-2 border-violet-500');
}

// Yenile
function refreshReservations() {
    loadReservations();
    showSuccess('Liste yenilendi');
}

// Modal aç
function openNewReservationModal() {
    $('#new-reservation-modal').removeClass('hidden');
    calculatePrice(); // İlk tutarı hesapla
}

// Modal kapat
function closeModal() {
    $('#new-reservation-modal').addClass('hidden');
    $('#reservation-detail-modal').addClass('hidden');
    $('#new-reservation-form')[0].reset();
}

// Tutar hesapla
function calculatePrice() {
    const checkin = $('#checkin-date').val();
    const checkout = $('#checkout-date').val();
    const roomSelect = $('#room-select');
    const price = roomSelect.find('option:selected').data('price');
    
    if(checkin && checkout && price) {
        const nights = Math.ceil((new Date(checkout) - new Date(checkin)) / (1000 * 60 * 60 * 24));
        const total = nights * price;
        
        $('#night-count').val(nights + ' gece');
        $('#total-price').val('₺' + total.toFixed(2));
    }
}

// Rezervasyon oluştur
function createReservation() {
    const formData = $('#new-reservation-form').serialize();
    
    $.ajax({
        url: 'api/create-reservation.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            // Loading göster
            $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Kaydediliyor...');
        },
        success: function(response) {
            if(response.success) {
                showSuccess('Rezervasyon başarıyla oluşturuldu!');
                closeModal();
                loadReservations();
            } else {
                showError(response.message || 'Rezervasyon oluşturulamadı');
            }
        },
        error: function() {
            showError('Sunucu hatası');
        },
        complete: function() {
            $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Rezervasyonu Kaydet');
        }
    });
}

// Rezervasyon detayını görüntüle
function viewReservation(id) {
    $.ajax({
        url: 'api/get-reservation.php',
        type: 'GET',
        data: { id: id },
        dataType: 'html',
        success: function(response) {
            $('#reservation-detail-content').html(response);
            $('#reservation-detail-modal').removeClass('hidden');
        },
        error: function() {
            showError('Detaylar yüklenemedi');
        }
    });
}

// Silme onayı
function confirmDelete(id) {
    if(confirm('Bu rezervasyonu silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
        deleteReservation(id);
    }
}

// Rezervasyon sil
function deleteReservation(id) {
    $.ajax({
        url: 'api/delete-reservation.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showSuccess('Rezervasyon silindi');
                loadReservations();
            } else {
                showError(response.message || 'Silme işlemi başarısız');
            }
        },
        error: function() {
            showError('Sunucu hatası');
        }
    });
}

// Export
function exportReservations() {
    const search = $('#search-reservations').val();
    const filter = currentFilter;
    
    window.open(`api/export-reservations.php?filter=${filter}&search=${encodeURIComponent(search)}`, '_blank');
}

// Tarih formatı
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Hata mesajı
function showError(message) {
    alert('Hata: ' + message);
}

// Başarı mesajı
function showSuccess(message) {
    alert('Başarılı: ' + message);
}
</script>