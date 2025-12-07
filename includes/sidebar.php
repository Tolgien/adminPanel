<!-- includes/sidebar.php -->
<aside class="w-72 bg-white fixed h-full p-6 shadow-2xl z-50 overflow-y-auto">
    <div class="text-3xl font-extrabold text-violet-700 mb-10 tracking-wide border-b pb-4">
        AESTHETICA <span class="text-gray-900">PMS</span>
    </div>

    <nav class="space-y-6">
        <!-- OTEL OPERASYON -->
        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">OTEL OPERASYON</h3>
            <a href="/admin/modules/dashboard.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-chart-line text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
            <a href="/admin/modules/rezervasyonlar.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-bookmark text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Rezervasyonlar</span>
            </a>
        </div>

        <!-- ODA VE MÜŞTERİ -->
        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">ODA VE MÜŞTERİ</h3>
            <a href="/admin/modules/odalar.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-bed text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Odalar</span>
            </a>
            <a href="/admin/modules/musteriler.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-users text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Müşteriler</span>
            </a>
        </div>
        
        <!-- BLOG MODÜLÜ -->
        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">BLOG YÖNETİMİ</h3>
            
            <a href="/admin/modules/blog.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-newspaper text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Tüm Yazılar</span>
            </a>
            
            <a href="/admin/modules/blog-add.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-plus text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Yeni Yazı</span>
            </a>
            
            <a href="/admin/modules/blog-categories.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-folder text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Kategoriler</span>
            </a>
            
            <a href="/admin/modules/blog-tags.php" class="flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-tags text-gray-500"></i>
                </div>
                <span class="text-sm font-medium">Etiketler</span>
            </a>
        </div>
        
        <!-- ÇIKIŞ -->
        <div class="pt-10 border-t">
            <a href="/admin/logout.php" class="flex items-center p-3 rounded-xl hover:bg-red-50 text-red-600 transition duration-200">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                    <i class="fas fa-sign-out-alt text-red-500"></i>
                </div>
                <span class="text-sm font-medium">Çıkış Yap</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Aktif sayfayı JavaScript ile belirle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Önce mevcut URL'yi al
    const currentUrl = window.location.pathname;
    console.log('Mevcut URL:', currentUrl);
    
    // 2. Tüm sidebar linklerini al
    const allLinks = document.querySelectorAll('aside nav a');
    
    // 3. Her link için kontrol et
    allLinks.forEach(link => {
        const linkUrl = link.getAttribute('href');
        
        // Eğer mevcut URL bu linkin URL'sini içeriyorsa
        if (currentUrl.includes(linkUrl.replace('/admin', ''))) {
            // Aktif stil ekle
            link.classList.add('bg-violet-50', 'text-violet-700');
            link.classList.remove('hover:bg-gray-50', 'text-gray-700');
            
            // İkonu da aktif yap
            const iconDiv = link.querySelector('div.w-8');
            if (iconDiv) {
                iconDiv.classList.add('bg-violet-700');
                iconDiv.classList.remove('bg-gray-100');
                
                // İkon rengini beyaz yap
                const icon = iconDiv.querySelector('i');
                if (icon) {
                    icon.classList.add('text-white');
                    icon.classList.remove('text-gray-500', 'text-red-500');
                }
            }
        }
    });
    
    // 4. Özel durum: Blog ana sayfası
    if (currentUrl.includes('blog.php') && 
        !currentUrl.includes('blog-add.php') && 
        !currentUrl.includes('blog-categories.php') && 
        !currentUrl.includes('blog-tags.php') &&
        !currentUrl.includes('blog-edit.php')) {
        
        // Tüm Yazılar linkini aktif yap
        const blogLinks = document.querySelectorAll('aside nav a');
        blogLinks.forEach(link => {
            if (link.href.includes('/admin/modules/blog.php') && 
                !link.href.includes('blog-add.php') &&
                !link.href.includes('blog-categories.php') &&
                !link.href.includes('blog-tags.php')) {
                
                link.classList.add('bg-violet-50', 'text-violet-700');
                link.classList.remove('hover:bg-gray-50', 'text-gray-700');
                
                const iconDiv = link.querySelector('div.w-8');
                if (iconDiv) {
                    iconDiv.classList.add('bg-violet-700');
                    iconDiv.classList.remove('bg-gray-100');
                    
                    const icon = iconDiv.querySelector('i');
                    if (icon) {
                        icon.classList.add('text-white');
                        icon.classList.remove('text-gray-500');
                    }
                }
            }
        });
    }
});
</script>