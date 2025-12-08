<?php
// index.php
require_once 'config/database.php';


// Eğer giriş yapılmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = [
    'dashboard', 'rezervasyonlar', 'odalar', 'musteriler', 'blog',
    'galeri', 'hakkimizda', 'iletisim', 'bildirimler', 'kullanicilar'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otel Yönetim Paneli - Aesthetica PMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
        }
        
        .aesthetic-card {
            background-color: white;
            box-shadow: 0 12px 40px rgba(10, 20, 30, 0.06);
            border-radius: 24px;
            border: 1px solid rgba(220, 220, 230, 0.3);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .aesthetic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(10, 20, 30, 0.1);
        }

        .modern-btn {
            padding: 12px 28px;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        .modern-btn-primary {
            background-color: #5b21b7;
            color: white;
        }
        .modern-btn-primary:hover {
            background-color: #4c1d95;
            box-shadow: 0 6px 15px rgba(91, 33, 183, 0.4);
        }
        
        .flat-icon-styled {
            display: inline-flex;
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #4f46e5;
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1);
        }
        
        .sidebar-link.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
        }
        .sidebar-link.active .flat-icon-styled {
            background: white;
            color: #4f46e5;
        }
        
        .content-area {
            min-height: 80vh;
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #5b21b7;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="flex min-h-screen">

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-grow ml-72">
        
        <!-- Header -->
        <?php include 'includes/header.php'; ?>

        <main class="p-8">
            <div id="content-area" class="content-area">
                <div class="loading">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </main>
    </div>

    <script>
    $(document).ready(function() {
        // Sayfa yüklendiğinde dashboard'u aç
        loadPage('<?php echo $page; ?>');
        
        // Sidebar link tıklamaları
        $('.sidebar-link').on('click', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            
            // Active class'ını güncelle
            $('.sidebar-link').removeClass('active');
            $(this).addClass('active');
            
            // Sayfayı yükle
            loadPage(page);
            
            // URL'yi güncelle (tarayıcı geçmişi için)
            history.pushState({page: page}, '', '?page=' + page);
        });
        
        // Browser geri/ileri butonları için
        window.onpopstate = function(event) {
            if (event.state && event.state.page) {
                loadPage(event.state.page);
            }
        };
        
        // Sayfa yükleme fonksiyonu
        function loadPage(page) {
            $('#content-area').html('<div class="loading"><div class="loading-spinner"></div></div>');
            
            $.ajax({
                url: 'modules/' + page + '.php',
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    $('#content-area').html(response);
                    initPageScripts(page);
                },
                error: function() {
                    $('#content-area').html(
                        '<div class="aesthetic-card p-8 text-center">' +
                        '<i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>' +
                        '<h3 class="text-xl font-bold mb-2">Sayfa Yüklenemedi</h3>' +
                        '<p class="text-gray-600">Lütfen sayfayı yenileyin veya daha sonra tekrar deneyin.</p>' +
                        '</div>'
                    );
                }
            });
        }
        
        // Sayfaya özel script'leri başlat
        function initPageScripts(page) {
            switch(page) {
                case 'dashboard':
                    loadDashboardStats();
                    break;
                case 'rezervasyonlar':
                    loadReservations();
                    break;
                case 'odalar':
                    loadRooms();
                    break;
                // Diğer sayfalar için script'ler
            }
        }
        
        // Dashboard istatistiklerini yükle
        function loadDashboardStats() {
            $.ajax({
                url: 'api/dashboard-stats.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    updateDashboardCards(data);
                }
            });
        }
        
        function updateDashboardCards(data) {
            // Burada dashboard kartlarını güncelleyecek kodlar
            console.log('Dashboard verileri:', data);
        }
    });
    </script>
</body>
</html>