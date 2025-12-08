<div class="space-y-8 animate-fade-in">
    <!-- Welcome Card -->
    <div class="aesthetic-card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-hotel text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Hoş Geldiniz, <span class="text-indigo-600"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>! 👋</h2>
                        <p class="text-gray-600">Otel yönetim panelinize hoş geldiniz. Sisteminizde <span id="liveTime" class="font-semibold"><?php echo date('H:i'); ?></span> itibarıyla son durum:</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <button id="refreshStats" class="bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl px-5 py-2.5 font-medium flex items-center gap-2 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                    <i class="fas fa-sync-alt text-gray-500"></i>
                    <span>Yenile</span>
                </button>
                <button class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl px-5 py-2.5 font-medium flex items-center gap-2 transition-all duration-300 hover:shadow-lg hover:shadow-emerald-200 hover:-translate-y-0.5">
                    <i class="fas fa-download"></i>
                    <span>Rapor İndir</span>
                </button>
            </div>
        </div>
        
        <!-- Quick Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-indigo-50 to-white p-4 rounded-xl border border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-bed text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Aktif Rezervasyon</p>
                        <p class="text-xl font-bold text-gray-900" id="aktifRezervasyon">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-white p-4 rounded-xl border border-emerald-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-user-check text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Bugün Check-in</p>
                        <p class="text-xl font-bold text-gray-900" id="bugunCheckin">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-white p-4 rounded-xl border border-amber-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-user-times text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Bugün Check-out</p>
                        <p class="text-xl font-bold text-gray-900" id="bugunCheckout">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-white p-4 rounded-xl border border-purple-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Bekleyen</p>
                        <p class="text-xl font-bold text-gray-900" id="bekleyenRezervasyon">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Toplam Oda -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Toplam Oda</p>
                    <h3 class="text-2xl font-bold text-gray-900" id="totalRooms">0</h3>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center text-sm">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></div>
                    <span class="text-gray-600"><span id="occupiedRooms">0</span> dolu oda</span>
                </div>
            </div>
        </div>
        
        <!-- Dolu Oda -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Dolu Oda</p>
                    <h3 class="text-2xl font-bold text-gray-900" id="occupiedRoomsCard">0</h3>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-check-circle text-emerald-600"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Doluluk Oranı</span>
                    <span class="font-bold text-emerald-600" id="dolulukOrani">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="dolulukBar" class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
        </div>
        
        <!-- Bugün Gelir -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Bugünkü Gelir</p>
                    <h3 class="text-2xl font-bold text-gray-900" id="todayIncome">0 TL</h3>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-amber-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-money-bill-wave text-amber-600"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center text-sm">
                    <i class="fas fa-calendar-week text-indigo-500 mr-2"></i>
                    <span class="text-gray-600">Haftalık: <span class="font-medium" id="weeklyIncome">0 TL</span></span>
                </div>
            </div>
        </div>
        
        <!-- Aylık Gelir -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Aylık Gelir</p>
                    <h3 class="text-2xl font-bold text-gray-900" id="monthlyIncome">0 TL</h3>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-line text-purple-600"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center text-sm">
                    <i class="fas fa-users text-blue-500 mr-2"></i>
                    <span class="text-gray-600">Toplam Müşteri: <span class="font-medium" id="totalCustomers">0</span></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts & Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Son Rezervasyonlar -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Son Rezervasyonlar</h3>
                <a href="#" data-page="rezervasyonlar" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 hover:gap-2 transition-all">
                    Tümünü Gör <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-200">
                            <th class="pb-3 font-medium">Rezervasyon No</th>
                            <th class="pb-3 font-medium">Müşteri</th>
                            <th class="pb-3 font-medium">Oda</th>
                            <th class="pb-3 font-medium">Tarih</th>
                            <th class="pb-3 font-medium">Tutar</th>
                        </tr>
                    </thead>
                    <tbody id="lastReservations">
                        <!-- AJAX ile dolacak -->
                        <tr>
                            <td colspan="5" class="py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-400 text-sm">Yükleniyor...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Oda Tipleri -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Oda Dağılımı</h3>
            <div id="roomTypeChart" class="h-48 flex items-center justify-center mb-6">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-pie text-2xl text-indigo-500"></i>
                    </div>
                    <p class="text-gray-400 text-sm">Oda dağılımı yükleniyor...</p>
                </div>
            </div>
            <div id="roomTypeList" class="space-y-3">
                <!-- Oda tipi listesi buraya gelecek -->
            </div>
        </div>
    </div>
    
    <!-- Doluluk Oranları -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Aylık Doluluk Oranları</h3>
                <p class="text-sm text-gray-500"><?php echo date('F Y'); ?> ayı için oda bazlı doluluk</p>
            </div>
            <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg">
                <span class="text-sm text-gray-500">Ortalama:</span>
                <span class="text-lg font-bold text-emerald-600" id="avgOccupancy">0%</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b border-gray-200">
                        <th class="pb-3 font-medium">Oda No</th>
                        <th class="pb-3 font-medium">Oda Adı</th>
                        <th class="pb-3 font-medium">Rezerve Gün</th>
                        <th class="pb-3 font-medium">Toplam Gün</th>
                        <th class="pb-3 font-medium">Doluluk Oranı</th>
                    </tr>
                </thead>
                <tbody id="occupancyTable">
                    <tr>
                        <td colspan="5" class="py-8 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                </div>
                                <p class="text-gray-400 text-sm">Doluluk verileri yükleniyor...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Canlı saat güncelleme
function updateLiveTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
    $('#liveTime').text(timeStr);
}

// Dashboard verilerini yükle
function loadDashboardStats() {
    // Loading state
    $('#lastReservations').html(`
        <tr>
            <td colspan="5" class="py-8 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </div>
                    <p class="text-gray-400 text-sm">Yükleniyor...</p>
                </div>
            </td>
        </tr>
    `);
    
    $.ajax({
        url: 'api/dashboard-stats.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                updateDashboardCards(response.data);
                updateLastReservations(response.data.son_rezervasyonlar);
                updateRoomTypes(response.data.oda_tipleri);
                updateOccupancyTable(response.data.aylik_doluluk);
                updateAverageOccupancy(response.data.ortalama_doluluk);
            } else {
                showError('Veri alınamadı: ' + (response.error || 'Bilinmeyen hata'));
            }
        },
        error: function(xhr, status, error) {
            showError('API bağlantı hatası: ' + error);
        }
    });
}

// Kartları güncelle
function updateDashboardCards(data) {
    // Ana istatistikler
    $('#totalRooms').text(data.toplam_oda || 0);
    $('#occupiedRooms').text(data.dolu_oda || 0);
    $('#occupiedRoomsCard').text(data.dolu_oda || 0);
    $('#bos_oda').text(data.bos_oda || 0);
    
    // Doluluk oranı
    const dolulukOrani = data.toplam_oda > 0 ? ((data.dolu_oda / data.toplam_oda) * 100).toFixed(1) : 0;
    $('#dolulukOrani').text(dolulukOrani + '%');
    $('#dolulukBar').css('width', dolulukOrani + '%');
    
    // Gelirler
    $('#todayIncome').text(formatCurrency(data.bugun_gelir || 0));
    $('#weeklyIncome').text(formatCurrency(data.haftalik_gelir || 0));
    $('#monthlyIncome').text(formatCurrency(data.aylik_gelir || 0));
    
    // Diğer istatistikler
    $('#aktifRezervasyon').text(data.aktif_rezervasyon || 0);
    $('#bugunCheckin').text(data.bugun_checkin || 0);
    $('#bugunCheckout').text(data.bugun_checkout || 0);
    $('#bekleyenRezervasyon').text(data.beklemede_rezervasyon || 0);
    $('#totalCustomers').text(data.toplam_musteri || 0);
}

// Son rezervasyonları güncelle
function updateLastReservations(reservations) {
    if (!reservations || reservations.length === 0) {
        $('#lastReservations').html(`
            <tr>
                <td colspan="5" class="py-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-calendar-times text-gray-400"></i>
                        </div>
                        <p class="text-gray-400 text-sm">Henüz rezervasyon bulunmuyor</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    reservations.forEach(function(res) {
        const girisTarihi = new Date(res.giris_tarihi);
        const cikisTarihi = new Date(res.cikis_tarihi);
        const tarihStr = `${girisTarihi.toLocaleDateString('tr-TR')} - ${cikisTarihi.toLocaleDateString('tr-TR')}`;
        
        html += `
        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
            <td class="py-3">
                <span class="font-mono font-bold text-indigo-600 text-sm">${res.rezervasyon_no}</span>
            </td>
            <td class="py-3">
                <div class="font-medium text-sm">${res.ad} ${res.soyad}</div>
                <div class="text-xs text-gray-500">${res.email || ''}</div>
            </td>
            <td class="py-3">
                <span class="inline-flex items-center gap-1 text-sm">
                    <i class="fas fa-door-closed text-gray-400 text-xs"></i>
                    ${res.oda_no} - ${res.oda_adi}
                </span>
            </td>
            <td class="py-3 text-sm">${tarihStr}</td>
            <td class="py-3">
                <span class="font-bold text-emerald-600 text-sm">${formatCurrency(res.toplam_fiyat)}</span>
            </td>
        </tr>
        `;
    });
    
    $('#lastReservations').html(html);
}

// Oda tiplerini güncelle
function updateRoomTypes(roomTypes) {
    if (!roomTypes || roomTypes.length === 0) {
        $('#roomTypeList').html(`
            <div class="text-center py-4">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-bed text-gray-400"></i>
                </div>
                <p class="text-gray-400 text-sm">Oda tipi bulunamadı</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    let total = roomTypes.reduce((sum, type) => sum + type.sayi, 0);
    
    roomTypes.forEach(function(type) {
        const percentage = total > 0 ? ((type.sayi / total) * 100).toFixed(1) : 0;
        const color = getRoomTypeColor(type.oda_tipi);
        const bgColor = getRoomTypeBgColor(type.oda_tipi);
        
        html += `
        <div class="flex items-center justify-between p-3 bg-gradient-to-r ${bgColor} to-white rounded-lg border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full ${color}"></div>
                <span class="font-medium text-sm">${type.oda_tipi}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-bold text-gray-900">${type.sayi}</span>
                <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded">${percentage}%</span>
            </div>
        </div>
        `;
    });
    
    $('#roomTypeList').html(html);
}

// Doluluk tablosunu güncelle
function updateOccupancyTable(occupancyData) {
    if (!occupancyData || occupancyData.length === 0) {
        $('#occupancyTable').html(`
            <tr>
                <td colspan="5" class="py-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-bed text-gray-400"></i>
                        </div>
                        <p class="text-gray-400 text-sm">Doluluk verisi bulunamadı</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    occupancyData.forEach(function(room) {
        const percentage = room.doluluk_orani || 0;
        const barColor = percentage >= 80 ? 'from-emerald-500 to-emerald-600' : 
                        percentage >= 50 ? 'from-amber-500 to-amber-600' : 
                        percentage >= 20 ? 'from-blue-500 to-blue-600' : 'from-gray-300 to-gray-400';
        const textColor = percentage >= 80 ? 'text-emerald-600' : 
                         percentage >= 50 ? 'text-amber-600' : 
                         percentage >= 20 ? 'text-blue-600' : 'text-gray-600';
        
        html += `
        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
            <td class="py-3">
                <span class="font-mono font-bold text-sm">${room.oda_no}</span>
            </td>
            <td class="py-3 font-medium text-sm">${room.oda_adi}</td>
            <td class="py-3 text-sm">${room.rezerve_gun || 0} gün</td>
            <td class="py-3 text-sm">${room.toplam_gun || 0} gün</td>
            <td class="py-3">
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r ${barColor} h-2 rounded-full transition-all duration-500" style="width: ${percentage}%"></div>
                    </div>
                    <span class="font-bold ${textColor} text-sm min-w-12 text-right">
                        ${percentage}%
                    </span>
                </div>
            </td>
        </tr>
        `;
    });
    
    $('#occupancyTable').html(html);
}

// Yardımcı fonksiyonlar
function formatCurrency(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function getRoomTypeColor(type) {
    const colors = {
        'standart': 'bg-blue-500',
        'deluxe': 'bg-purple-500',
        'suite': 'bg-emerald-500',
        'aile': 'bg-amber-500'
    };
    return colors[type] || 'bg-gray-400';
}

function getRoomTypeBgColor(type) {
    const colors = {
        'standart': 'from-blue-50',
        'deluxe': 'from-purple-50',
        'suite': 'from-emerald-50',
        'aile': 'from-amber-50'
    };
    return colors[type] || 'from-gray-50';
}

function updateAverageOccupancy(avg) {
    $('#avgOccupancy').text(avg + '%');
}

function showError(message) {
    console.error('Dashboard Error:', message);
    
    const errorHtml = `
    <div class="bg-gradient-to-r from-red-50 to-white p-4 rounded-xl border border-red-100 mb-6 animate-pulse">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <div>
                <h4 class="font-bold text-red-700 text-sm">Veri Yüklenemedi</h4>
                <p class="text-red-600 text-xs">${message}</p>
            </div>
        </div>
    </div>
    `;
    
    $('.space-y-8').prepend(errorHtml);
}

// Sayfa yüklendiğinde çalıştır
$(document).ready(function() {
    // Saati güncelle
    updateLiveTime();
    setInterval(updateLiveTime, 60000);
    
    // İlk yükleme
    loadDashboardStats();
    
    // Yenile butonu
    $('#refreshStats').on('click', function() {
        const btn = $(this);
        const icon = btn.find('i');
        const text = btn.find('span');
        const originalText = text.text();
        
        btn.addClass('opacity-75 cursor-not-allowed');
        icon.addClass('fa-spin');
        text.text('Yükleniyor...');
        
        loadDashboardStats();
        
        setTimeout(() => {
            btn.removeClass('opacity-75 cursor-not-allowed');
            icon.removeClass('fa-spin');
            text.text(originalText);
        }, 1000);
    });
    
    // 60 saniyede bir otomatik yenile
    setInterval(loadDashboardStats, 60000);
    
    // Rapor indir butonu
    $('button:contains("Rapor İndir")').on('click', function() {
        const btn = $(this);
        const icon = btn.find('i');
        const text = btn.find('span');
        const originalText = text.text();
        
        btn.addClass('opacity-75 cursor-not-allowed');
        icon.removeClass('fa-download').addClass('fa-spinner fa-spin');
        text.text('Hazırlanıyor...');
        
        setTimeout(() => {
            btn.removeClass('opacity-75 cursor-not-allowed');
            icon.removeClass('fa-spinner fa-spin').addClass('fa-download');
            text.text(originalText);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Başarılı!',
                    text: 'Rapor başarıyla indirildi.',
                    icon: 'success',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#10b981',
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        }, 1500);
    });
});
</script>

<style>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: translateY(10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Buton hover efektleri */
button {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover {
    transform: translateY(-2px);
}

/* Kart hover efektleri */
.bg-white {
    transition: all 0.3s ease;
}

.bg-white:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Tablo hover efektleri */
tbody tr {
    transition: all 0.2s ease;
}

tbody tr:hover {
    background-color: #f9fafb;
}

/* Responsive tasarım */
@media (max-width: 768px) {
    .text-2xl {
        font-size: 1.5rem;
    }
    
    .text-xl {
        font-size: 1.25rem;
    }
    
    .grid-cols-2 {
        grid-template-columns: repeat(1, 1fr);
    }
    
    .gap-5 {
        gap: 1rem;
    }
    
    .p-5, .p-6 {
        padding: 1rem;
    }
}
</style>