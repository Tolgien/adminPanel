<?php
// includes/sidebar.php
?>
<!-- Sidebar -->
<aside class="fixed top-0 left-0 w-72 h-screen bg-white border-r border-gray-200 shadow-lg z-40">
    <div class="p-6 border-b">
        <div class="flex items-center space-x-3">
            <div class="flat-icon-styled">
                <i class="fas fa-hotel"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Aesthetica PMS</h2>
                <p class="text-xs text-gray-500">Otel Yönetim Sistemi</p>
            </div>
        </div>
    </div>
    
    <div class="p-4">
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">GENEL</h3>
            <ul class="space-y-2">
                <li>
                    <a href="#" data-page="dashboard" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="font-medium">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-page="rezervasyonlar" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span class="font-medium">Rezervasyonlar</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-page="odalar" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-bed"></i>
                        </div>
                        <span class="font-medium">Odalar</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-page="musteriler" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="font-medium">Müşteriler</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">İÇERİK</h3>
            <ul class="space-y-2">
                <li>
                    <a href="#" data-page="blog" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-blog"></i>
                        </div>
                        <span class="font-medium">Blog</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-page="galeri" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-images"></i>
                        </div>
                        <span class="font-medium">Galeri</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">AYARLAR</h3>
            <ul class="space-y-2">
                <li>
                    <a href="#" data-page="kullanicilar" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <span class="font-medium">Kullanıcılar</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-page="hakkimizda" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <span class="font-medium">Hakkımızda</span>
                    </a>
                </li>
                <li>
                    <a href="#" data-page="iletisim" class="sidebar-link flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                        <div class="flat-icon-styled">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="font-medium">İletişim</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="mt-8 p-4 bg-indigo-50 rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-question-circle text-white"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Yardım mı lazım?</p>
                    <a href="#" class="text-xs text-indigo-600 hover:underline">Dokümantasyona göz atın</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom user info -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-bold"><?php echo substr($_SESSION['user_name'] ?? 'A', 0, 1); ?></span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></p>
                    <p class="text-xs text-gray-500"><?php echo $_SESSION['user_role'] ?? 'Yönetici'; ?></p>
                </div>
            </div>
            <a href="logout.php" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>