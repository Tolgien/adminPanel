<!-- includes/header.php -->
<?php
// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Bildirim sayısı
$notification_count = 0;

try {
    require_once 'config/database.php';
    $db = getDB();
    
    // Bildirim sayısı
    $notification_count = $db->query("
        SELECT COUNT(*) as count FROM bildirimler 
        WHERE kullanici_id = " . intval($_SESSION['user_id']) . " 
        AND okundu = 0
    ")->fetchColumn();
    
    // Kullanıcı bilgileri
    $user_info = $db->query("
        SELECT k.*, r.rol_adi 
        FROM kullanicilar k
        LEFT JOIN roller r ON k.rol_id = r.id
        WHERE k.id = " . intval($_SESSION['user_id']) . "
    ")->fetch();
    
    if ($user_info) {
        $_SESSION['user_avatar'] = $user_info['profil_resmi'] ?? '';
        $_SESSION['role_name'] = $user_info['rol_adi'] ?? 'Yönetici';
    }
    
} catch (Exception $e) {
    error_log("Header veri hatası: " . $e->getMessage());
}

// Kullanıcı bilgileri
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = $_SESSION['role_name'] ?? 'Yönetici';
$user_avatar = $_SESSION['user_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user_name) . '&background=5b21b7&color=fff&size=150';

// Kısa ad oluştur
$short_name = '';
$name_parts = explode(' ', $user_name);
foreach ($name_parts as $part) {
    $short_name .= strtoupper(substr($part, 0, 1));
}
$short_name = substr($short_name, 0, 2);

// Avatar URL kontrolü
if (empty($user_avatar) || strpos($user_avatar, 'http') === false) {
    $user_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($short_name) . '&background=5b21b7&color=fff&size=150';
}
?>

<header class="bg-white sticky top-0 z-40 px-8 py-4 flex justify-between items-center border-b border-gray-100">
    <!-- Sol Taraf: Logo ve Arama -->
    <div class="flex-1 flex items-center space-x-6">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
        </div>
        
        <!-- Arama Kutusu -->
        <div class="relative flex-1 max-w-lg">
            <input type="text" 
                   id="globalSearch" 
                   placeholder="Rezervasyon, müşteri, oda veya ID ile ara..." 
                   class="w-full bg-gray-50 border border-gray-200 py-2.5 pl-10 pr-8 rounded-lg text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100 transition-all">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <button id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>
    
    <!-- Sağ Taraf: İkonlar ve Kullanıcı -->
    <div class="flex items-center space-x-3">
        <!-- Hızlı Check-in Icon -->
        <button id="quickCheckinBtn"
                class="p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
                title="Hızlı Check-in">
            <i class="fas fa-sign-in-alt text-lg"></i>
        </button>
        
        <!-- Hızlı İşlemler Icon -->
        <div class="relative">
            <button id="quickActionsBtn"
                    class="p-2 text-gray-500 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors duration-200"
                    title="Hızlı İşlemler">
                <i class="fas fa-bolt text-lg"></i>
            </button>
            
            <!-- Hızlı İşlemler Dropdown -->
            <div id="quickActionsMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-50">
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                        <i class="fas fa-plus text-sm"></i>
                    </div>
                    <span>Yeni Rezervasyon</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus text-sm"></i>
                    </div>
                    <span>Yeni Müşteri</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mr-3">
                        <i class="fas fa-bed text-sm"></i>
                    </div>
                    <span>Oda Durumu</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-purple-50">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                        <i class="fas fa-file-invoice text-sm"></i>
                    </div>
                    <span>Fatura Oluştur</span>
                </a>
            </div>
        </div>
        
        <!-- Bildirimler -->
        <div class="relative">
            <button class="relative p-2 text-gray-500 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors duration-200"
                    title="Bildirimler">
                <i class="fas fa-bell text-lg"></i>
                <?php if ($notification_count > 0): ?>
                    <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-white text-xs flex items-center justify-center font-bold">
                        <?php echo min($notification_count, 9); ?>
                    </span>
                <?php endif; ?>
            </button>
            
            <!-- Bildirimler Dropdown -->
            <div id="notificationsMenu" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-900">Bildirimler</h3>
                        <button class="text-xs text-violet-600 hover:text-violet-800">Tümünü Oku</button>
                    </div>
                </div>
                
                <div class="max-h-64 overflow-y-auto py-1">
                    <div class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-medium text-gray-900">Yeni Rezervasyon</p>
                                <p class="text-xs text-gray-500 mt-0.5">Ahmet Yılmaz - Oda 101</p>
                                <p class="text-xs text-gray-400 mt-0.5">2 dakika önce</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <i class="fas fa-bed text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-medium text-gray-900">Oda Temizliği</p>
                                <p class="text-xs text-gray-500 mt-0.5">Oda 205 hazır</p>
                                <p class="text-xs text-gray-400 mt-0.5">15 dakika önce</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="px-4 py-2 border-t border-gray-100 text-center">
                    <a href="?page=bildirimler" class="text-xs text-violet-600 hover:text-violet-800">
                        Tümünü Gör
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Ayarlar -->
        <a href="?page=ayarlar" 
           class="p-2 text-gray-500 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors duration-200"
           title="Ayarlar">
            <i class="fas fa-cog text-lg"></i>
        </a>
        
        <!-- Yardım -->
        <button id="helpBtn" 
                class="p-2 text-gray-500 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors duration-200"
                title="Yardım">
            <i class="fas fa-question-circle text-lg"></i>
        </button>
        
        <!-- Kullanıcı Profili -->
        <div class="relative">
            <button id="userProfileBtn"
                    class="flex items-center space-x-2 cursor-pointer group pl-2">
                <div class="relative">
                    <img class="h-8 w-8 rounded-full object-cover border-2 border-violet-400 group-hover:border-violet-600 transition-colors" 
                         src="<?php echo htmlspecialchars($user_avatar); ?>" 
                         alt="<?php echo htmlspecialchars($user_name); ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($short_name); ?>&background=5b21b7&color=fff&size=150'">
                    <span class="absolute -bottom-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-500 border border-white"></span>
                </div>
                <div class="text-left">
                    <p class="text-xs font-semibold text-gray-800 group-hover:text-violet-700">
                        <?php echo htmlspecialchars($user_name); ?>
                    </p>
                </div>
                <i class="fas fa-chevron-down text-gray-400 text-xs group-hover:text-violet-600"></i>
            </button>
            
            <!-- User Profile Dropdown -->
            <div id="userProfileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center">
                        <img class="h-8 w-8 rounded-full object-cover border-2 border-violet-400 mr-3" 
                             src="<?php echo htmlspecialchars($user_avatar); ?>" 
                             alt="<?php echo htmlspecialchars($user_name); ?>">
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user_role); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="py-1">
                    <a href="?page=profil" class="flex items-center px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user-circle mr-3 text-gray-400 w-4"></i>
                        Profilim
                    </a>
                    <a href="?page=ayarlar" class="flex items-center px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-cog mr-3 text-gray-400 w-4"></i>
                        Ayarlar
                    </a>
                    <button id="toggleTheme" class="flex items-center px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 w-full text-left">
                        <i class="fas fa-moon mr-3 text-gray-400 w-4"></i>
                        Koyu Tema
                    </button>
                </div>
                
                <div class="border-t border-gray-100 pt-2">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-xs text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-3 w-4"></i>
                        Çıkış Yap
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Arama işlevselliği
    const searchInput = document.getElementById('globalSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    
    if (searchInput && clearSearchBtn) {
        searchInput.addEventListener('input', function() {
            clearSearchBtn.classList.toggle('hidden', !this.value.trim());
        });
        
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.add('hidden');
            searchInput.focus();
        });
    }
    
    // Dropdown'lar
    const dropdowns = {
        quickActions: {
            button: document.getElementById('quickActionsBtn'),
            menu: document.getElementById('quickActionsMenu')
        },
        notifications: {
            button: document.querySelector('#notificationsMenu').previousElementSibling,
            menu: document.getElementById('notificationsMenu')
        },
        userProfile: {
            button: document.getElementById('userProfileBtn'),
            menu: document.getElementById('userProfileMenu')
        }
    };
    
    // Dropdown açma/kapama
    Object.values(dropdowns).forEach(dropdown => {
        if (dropdown.button && dropdown.menu) {
            dropdown.button.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Diğer dropdown'ları kapat
                Object.values(dropdowns).forEach(other => {
                    if (other.menu !== dropdown.menu) {
                        other.menu.classList.add('hidden');
                    }
                });
                
                // Bu dropdown'ı aç/kapat
                dropdown.menu.classList.toggle('hidden');
                
                // Pozisyon ayarla
                const rect = dropdown.button.getBoundingClientRect();
                dropdown.menu.style.top = (rect.bottom + 8) + 'px';
                dropdown.menu.style.right = (window.innerWidth - rect.right) + 'px';
            });
        }
    });
    
    // Sayfa dışına tıklayınca dropdown'ları kapat
    document.addEventListener('click', function() {
        Object.values(dropdowns).forEach(dropdown => {
            if (dropdown.menu) {
                dropdown.menu.classList.add('hidden');
            }
        });
    });
    
    // Hızlı Check-in butonu
    const quickCheckinBtn = document.getElementById('quickCheckinBtn');
    if (quickCheckinBtn && typeof Swal !== 'undefined') {
        quickCheckinBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Hızlı Check-in',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Rezervasyon No</label>
                            <input type="text" id="reservationNo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="REZ-2024-001">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Kimlik No</label>
                            <input type="text" id="identityNo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="TC Kimlik No">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Check-in Yap',
                cancelButtonText: 'İptal',
                confirmButtonColor: '#10b981',
                width: '400px'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Başarılı!', 'Check-in işlemi tamamlandı.', 'success');
                }
            });
        });
    }
    
    // Yardım butonu
    const helpBtn = document.getElementById('helpBtn');
    if (helpBtn && typeof Swal !== 'undefined') {
        helpBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Yardım Merkezi',
                html: `
                    <div class="text-left">
                        <p class="text-sm mb-3">Sık kullanılan işlemler:</p>
                        <ul class="space-y-2 text-sm">
                            <li><strong>Hızlı Check-in (↙):</strong> Mevcut rezervasyon için check-in yapın</li>
                            <li><strong>Hızlı İşlemler (⚡):</strong> Yeni rezervasyon, müşteri, oda durumu</li>
                            <li><strong>Bildirimler (🔔):</strong> Sistem bildirimlerini görüntüleyin</li>
                            <li><strong>Ayarlar (⚙️):</strong> Sistem ayarlarını yapılandırın</li>
                            <li><strong>Yardım (?):</strong> Yardım merkezine erişin</li>
                        </ul>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Anladım',
                confirmButtonColor: '#5b21b7',
                width: '500px'
            });
        });
    }
    
    // Tema değiştirme
    const toggleTheme = document.getElementById('toggleTheme');
    if (toggleTheme) {
        toggleTheme.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
            const icon = this.querySelector('i');
            if (document.documentElement.classList.contains('dark')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            }
        });
    }
    
    // Tema kontrolü
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
        const themeIcon = document.querySelector('#toggleTheme i');
        if (themeIcon) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    }
});
</script>

<!-- CSS Stilleri -->
<style>
/* Header stilleri */
header {
    background: linear-gradient(to right, #ffffff, #fafafa);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Input focus efekti */
#globalSearch:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    border-color: #8b5cf6;
}

/* Dropdown animasyonları */
#quickActionsMenu,
#notificationsMenu,
#userProfileMenu {
    animation: fadeIn 0.15s ease-out;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Bildirim sayacı animasyonu */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.bg-red-500 {
    animation: pulse 2s infinite;
}

/* Responsive tasarım */
@media (max-width: 768px) {
    header {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .flex-1.max-w-lg {
        max-width: 200px;
    }
    
    #globalSearch::placeholder {
        font-size: 0.75rem;
    }
    
    .space-x-3 > * {
        margin-right: 0.25rem;
        margin-left: 0.25rem;
    }
}

/* Koyu tema desteği */
.dark header {
    background: linear-gradient(to right, #1f2937, #111827);
    border-color: #374151;
}

.dark .bg-white {
    background-color: #1f2937 !important;
}

.dark .text-gray-900 {
    color: #f9fafb !important;
}

.dark .text-gray-500 {
    color: #d1d5db !important;
}

.dark .border-gray-100 {
    border-color: #374151 !important;
}

.dark #globalSearch {
    background-color: #374151;
    border-color: #4b5563;
    color: #f9fafb;
}

.dark #globalSearch:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}
</style>