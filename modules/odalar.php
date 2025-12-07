<?php
// modules/odalar.php
require_once '../config/database.php';
?>
<div class="space-y-8">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Oda Yönetimi</h1>
            <p class="text-gray-600 mt-1">Tüm odaları görüntüleyin ve yönetin</p>
        </div>
        <div class="flex space-x-3">
            <button class="modern-btn modern-btn-primary" onclick="openNewRoomModal()">
                <i class="fas fa-plus mr-2"></i> Yeni Oda
            </button>
            <button class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200" onclick="exportRooms()">
                <i class="fas fa-download mr-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Oda Durum Kartları -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <?php
        $db = getDB();
        $statuses = [
            ['status' => 'bos', 'label' => 'Boş Odalar', 'color' => 'green', 'icon' => 'bed'],
            ['status' => 'dolu', 'label' => 'Dolu Odalar', 'color' => 'red', 'icon' => 'user-friends'],
            ['status' => 'bakimda', 'label' => 'Bakımda', 'color' => 'yellow', 'icon' => 'tools'],
            ['status' => 'temizlik', 'label' => 'Temizlik Bekleyen', 'color' => 'blue', 'icon' => 'broom']
        ];
        
        foreach($statuses as $status):
            $stmt = $db->prepare("SELECT COUNT(*) FROM odalar WHERE durum = ? AND aktif = 1");
            $stmt->execute([$status['status']]);
            $count = $stmt->fetchColumn();
        ?>
        <div class="aesthetic-card p-4 cursor-pointer" onclick="filterRooms('<?php echo $status['status']; ?>')">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-gray-800"><?php echo $count; ?></div>
                    <div class="text-sm text-gray-600"><?php echo $status['label']; ?></div>
                </div>
                <div class="text-<?php echo $status['color']; ?>-500 text-2xl">
                    <i class="fas fa-<?php echo $status['icon']; ?>"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Oda Filtreleri -->
    <div class="aesthetic-card p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <select id="room-type-filter" class="px-4 py-2 border rounded-lg focus:border-violet-400" onchange="filterRooms()">
                <option value="">Tüm Oda Tipleri</option>
                <option value="standart">Standart</option>
                <option value="deluxe">Deluxe</option>
                <option value="suite">Suite</option>
                <option value="aile">Aile</option>
            </select>
            
            <select id="capacity-filter" class="px-4 py-2 border rounded-lg focus:border-violet-400" onchange="filterRooms()">
                <option value="">Tüm Kapasiteler</option>
                <option value="1">1 Kişi</option>
                <option value="2">2 Kişi</option>
                <option value="3">3 Kişi</option>
                <option value="4">4+ Kişi</option>
            </select>
            
            <input type="text" id="room-search" placeholder="Oda no veya adı ara..." 
                   class="px-4 py-2 border rounded-lg focus:border-violet-400 w-64"
                   onkeyup="filterRooms()">
            
            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" onclick="resetFilters()">
                <i class="fas fa-redo mr-2"></i> Sıfırla
            </button>
        </div>
    </div>
    
    <!-- Oda Listesi -->
    <div class="aesthetic-card p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oda No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oda Adı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasite</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="rooms-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- AJAX ile dolacak -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Yeni Oda Modal -->
<div id="new-room-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Yeni Oda Ekle</h3>
                    <button onclick="closeRoomModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form id="new-room-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Temel Bilgiler -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Temel Bilgiler</h4>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Oda No *</label>
                                <input type="text" name="oda_no" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Oda Adı *</label>
                                <input type="text" name="oda_adi" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Oda Tipi *</label>
                                <select name="oda_tipi" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                    <option value="standart">Standart</option>
                                    <option value="deluxe">Deluxe</option>
                                    <option value="suite">Suite</option>
                                    <option value="aile">Aile</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kişi Kapasitesi *</label>
                                <select name="kisi_kapasitesi" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                                    <?php for($i=1; $i<=10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i==2 ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Kişi
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Fiyat ve Özellikler -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Fiyat ve Özellikler</h4>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Günlük Fiyat (₺) *</label>
                                <input type="number" name="gunluk_fiyat" step="0.01" min="0" 
                                       class="w-full px-4 py-2 border rounded-lg focus:border-violet-400" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Haftalık Fiyat (₺)</label>
                                <input type="number" name="haftalik_fiyat" step="0.01" min="0" 
                                       class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metrekare (m²)</label>
                                <input type="number" name="metrekare" min="0" 
                                       class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Durumu</label>
                                <select name="durum" class="w-full px-4 py-2 border rounded-lg focus:border-violet-400">
                                    <option value="bos">Boş</option>
                                    <option value="dolu">Dolu</option>
                                    <option value="bakimda">Bakımda</option>
                                    <option value="temizlik">Temizlik</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Açıklama -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Açıklama</h4>
                        <textarea name="aciklama" rows="3" 
                                  class="w-full px-4 py-2 border rounded-lg focus:border-violet-400"
                                  placeholder="Oda açıklaması..."></textarea>
                    </div>
                    
                    <!-- Özellikler -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Oda Özellikleri</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php
                            $features = [
                                'wifi' => 'WiFi',
                                'tv' => 'TV',
                                'klima' => 'Klima',
                                'minibar' => 'Minibar',
                                'kasa' => 'Kasa',
                                'jakuzi' => 'Jakuzi',
                                'deniz_manzarasi' => 'Deniz Manzarası',
                                'balkon' => 'Balkon'
                            ];
                            
                            foreach($features as $key => $label):
                            ?>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="<?php echo $key; ?>" 
                                       class="h-4 w-4 text-violet-600 focus:ring-violet-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700"><?php echo $label; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-4">
                        <button type="button" onclick="closeRoomModal()" 
                                class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700">
                            <i class="fas fa-save mr-2"></i> Odayı Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadRooms();
    
    // Oda formu
    $('#new-room-form').on('submit', function(e) {
        e.preventDefault();
        createRoom();
    });
});

// Odaları yükle
function loadRooms() {
    const status = $('#room-status-filter').val();
    const type = $('#room-type-filter').val();
    const capacity = $('#capacity-filter').val();
    const search = $('#room-search').val();
    
    $.ajax({
        url: 'api/get-rooms.php',
        type: 'GET',
        data: {
            status: status,
            type: type,
            capacity: capacity,
            search: search
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                updateRoomsTable(response.data);
            }
        }
    });
}

// Oda tablosunu güncelle
function updateRoomsTable(rooms) {
    const tbody = $('#rooms-table-body');
    
    if(rooms.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-bed text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Oda bulunamadı</p>
                    <p class="text-sm mt-2">Yeni oda eklemek için "Yeni Oda" butonuna tıklayın</p>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    rooms.forEach(function(room) {
        // Durum renkleri
        const statusConfig = {
            'bos': { color: 'green', text: 'Boş', icon: 'bed' },
            'dolu': { color: 'red', text: 'Dolu', icon: 'user-friends' },
            'bakimda': { color: 'yellow', text: 'Bakımda', icon: 'tools' },
            'temizlik': { color: 'blue', text: 'Temizlik', icon: 'broom' }
        };
        
        const status = statusConfig[room.durum] || statusConfig.bos;
        
        // Oda tipi
        const typeLabels = {
            'standart': 'Standart',
            'deluxe': 'Deluxe',
            'suite': 'Suite',
            'aile': 'Aile'
        };
        
        const typeClass = {
            'standart': 'bg-gray-100 text-gray-800',
            'deluxe': 'bg-purple-100 text-purple-800',
            'suite': 'bg-indigo-100 text-indigo-800',
            'aile': 'bg-green-100 text-green-800'
        };
        
        html += `
        <tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-lg font-bold text-gray-900">${room.oda_no}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">${room.oda_adi}</div>
                <div class="text-sm text-gray-500 truncate max-w-xs">${room.aciklama || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 text-xs font-semibold rounded-full ${typeClass[room.oda_tipi] || typeClass.standart}">
                    ${typeLabels[room.oda_tipi] || room.oda_tipi}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <i class="fas fa-user text-gray-400 mr-2"></i>
                    <span class="text-sm text-gray-900">${room.kisi_kapasitesi} Kişi</span>
                </div>
                ${room.metrekare ? `<div class="text-xs text-gray-500">${room.metrekare} m²</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-lg font-bold text-gray-900">₺${room.gunluk_fiyat}</div>
                ${room.haftalik_fiyat ? `<div class="text-sm text-gray-500">₺${room.haftalik_fiyat}/hafta</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-${status.color}-100 text-${status.color}-800">
                    <i class="fas fa-${status.icon} mr-1"></i> ${status.text}
                </span>
                <div class="text-xs text-gray-500 mt-1">
                    ${room.aktif ? '<span class="text-green-600">✓ Aktif</span>' : '<span class="text-red-600">✗ Pasif</span>'}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="viewRoom(${room.id})" class="text-blue-600 hover:text-blue-900" title="Detaylar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editRoom(${room.id})" class="text-green-600 hover:text-green-900" title="Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="toggleRoomStatus(${room.id}, ${room.aktif})" 
                            class="${room.aktif ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}" 
                            title="${room.aktif ? 'Pasif Yap' : 'Aktif Yap'}">
                        <i class="fas ${room.aktif ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                    </button>
                    <button onclick="confirmDeleteRoom(${room.id})" class="text-red-600 hover:text-red-900" title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
    });
    
    tbody.html(html);
}

// Filtrele
function filterRooms(status = null) {
    if(status) {
        $('#room-status-filter').val(status);
    }
    loadRooms();
}

// Filtreleri sıfırla
function resetFilters() {
    $('#room-status-filter').val('');
    $('#room-type-filter').val('');
    $('#capacity-filter').val('');
    $('#room-search').val('');
    loadRooms();
}

// Modal aç
function openNewRoomModal() {
    $('#new-room-modal').removeClass('hidden');
}

// Modal kapat
function closeRoomModal() {
    $('#new-room-modal').addClass('hidden');
    $('#new-room-form')[0].reset();
}

// Yeni oda oluştur
function createRoom() {
    const formData = $('#new-room-form').serialize();
    
    $.ajax({
        url: 'api/create-room.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('button[type="submit"]').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Kaydediliyor...');
        },
        success: function(response) {
            if(response.success) {
                showSuccess('Oda başarıyla eklendi!');
                closeRoomModal();
                loadRooms();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Sunucu hatası');
        },
        complete: function() {
            $('button[type="submit"]').prop('disabled', false)
                .html('<i class="fas fa-save mr-2"></i> Odayı Kaydet');
        }
    });
}

// Oda durumunu değiştir
function toggleRoomStatus(id, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const action = newStatus ? 'aktif yap' : 'pasif yap';
    
    if(confirm(`Bu odayı ${action} istediğinize emin misiniz?`)) {
        $.ajax({
            url: 'api/toggle-room-status.php',
            type: 'POST',
            data: { id: id, status: newStatus },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showSuccess(`Oda ${newStatus ? 'aktif' : 'pasif'} yapıldı`);
                    loadRooms();
                } else {
                    showError(response.message);
                }
            }
        });
    }
}

// Oda silme onayı
function confirmDeleteRoom(id) {
    if(confirm('Bu odayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
        deleteRoom(id);
    }
}

// Oda sil
function deleteRoom(id) {
    $.ajax({
        url: 'api/delete-room.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showSuccess('Oda silindi');
                loadRooms();
            } else {
                showError(response.message);
            }
        }
    });
}

// Export
function exportRooms() {
    const status = $('#room-status-filter').val();
    const type = $('#room-type-filter').val();
    const capacity = $('#capacity-filter').val();
    const search = $('#room-search').val();
    
    window.open(`api/export-rooms.php?status=${status}&type=${type}&capacity=${capacity}&search=${encodeURIComponent(search)}`, '_blank');
}
</script>