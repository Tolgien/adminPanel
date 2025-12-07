<?php
// modules/dashboard.php
?>
<!-- Müşteri İstatistikleri Widget'ı -->
<div class="aesthetic-card p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-800">Müşteri İstatistikleri</h3>
        <a href="#" class="sidebar-link text-sm text-violet-600 hover:text-violet-800" data-page="musteriler">
            Tümünü Gör <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="text-center">
            <div class="text-3xl font-bold text-gray-900" id="total-customers">0</div>
            <div class="text-sm text-gray-600">Toplam Müşteri</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-green-600" id="today-customers">0</div>
            <div class="text-sm text-gray-600">Bugün Eklenen</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-blue-600" id="vip-customers">0</div>
            <div class="text-sm text-gray-600">VIP Müşteri</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-purple-600" id="birthday-customers">0</div>
            <div class="text-sm text-gray-600">Doğum Günü Yakın</div>
        </div>
    </div>
    
    <div class="border-t pt-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Son Eklenen Müşteriler</h4>
        <div id="recent-customers">
            <!-- AJAX ile dolacak -->
        </div>
    </div>
</div>

<script>
// Dashboard'a müşteri istatistiklerini ekle
function loadCustomerStats() {
    $.ajax({
        url: 'api/customer-statistics.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#total-customers').text(response.data.total_customers);
                $('#today-customers').text(response.data.today_added);
                $('#vip-customers').text(response.data.vip_customers.length);
                
                // Doğum günü yakın olanları getir
                $.ajax({
                    url: 'api/customer-birthdays.php',
                    type: 'GET',
                    data: {range: 'week'},
                    dataType: 'json',
                    success: function(birthdayResponse) {
                        if(birthdayResponse.success) {
                            $('#birthday-customers').text(birthdayResponse.count);
                        }
                    }
                });
                
                // Son eklenen müşteriler
                loadRecentCustomers();
            }
        }
    });
}

function loadRecentCustomers() {
    $.ajax({
        url: 'api/get-customers.php',
        type: 'GET',
        data: {per_page: 5},
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.customers.length > 0) {
                let html = '<div class="space-y-2">';
                
                response.data.customers.slice(0, 5).forEach(function(customer) {
                    html += `
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-r from-violet-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold mr-3">
                                ${customer.ad.charAt(0)}${customer.soyad.charAt(0)}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${customer.ad} ${customer.soyad}</div>
                                <div class="text-xs text-gray-500">${customer.email}</div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${formatDate(customer.created_at)}
                        </div>
                    </div>
                    `;
                });
                
                html += '</div>';
                $('#recent-customers').html(html);
            } else {
                $('#recent-customers').html('<p class="text-gray-500 text-sm">Henüz müşteri bulunmuyor</p>');
            }
        }
    });
}

// Dashboard yüklendiğinde çağır
$(document).ready(function() {
    if($('#total-customers').length) {
        loadCustomerStats();
    }
});
</script>
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
</script>