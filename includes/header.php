<!-- includes/header.php -->
<header class="bg-white sticky top-0 z-40 px-8 py-4 flex justify-between items-center aesthetic-card shadow-lg m-4">
    <div class="relative w-1/3">
        <input type="text" placeholder="Rezervasyon, Misafir veya ID ile ara..." class="w-full bg-gray-50 modern-input py-3 pl-12 rounded-xl text-sm border border-gray-200 focus:border-violet-400 transition">
        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
    </div>
    
    <div class="flex items-center space-x-6">
        <button class="modern-btn modern-btn-primary py-2 px-4 text-sm">
            <i class="fas fa-plus mr-2"></i> Yeni Rezervasyon
        </button>
        <div class="relative cursor-pointer text-gray-500 hover:text-violet-600">
            <i class="fas fa-cog text-xl"></i>
        </div>
        <div class="relative cursor-pointer text-gray-500 hover:text-violet-600">
            <i class="fas fa-bell text-xl"></i>
            <span class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500"></span>
        </div>
        <div class="flex items-center space-x-3 cursor-pointer">
            <img class="h-10 w-10 rounded-full object-cover border-2 border-violet-400" src="https://via.placeholder.com/150/5b21b7/ffffff?text=ADM" alt="Admin Profili">
            <div>
                <p class="text-sm font-semibold text-gray-800"><?php echo $_SESSION["user_name"] ?? "Admin"; ?></p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
    </div>
</header>