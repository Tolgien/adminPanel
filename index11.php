<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otel Yönetim Paneli - Estetik Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc; /* Çok Açık Mavi-Gri */
        }
        
        /* Estetik Kart Stili */
        .aesthetic-card {
            background-color: white;
            box-shadow: 0 12px 40px rgba(10, 20, 30, 0.06); /* Hafif ve Yayılan Gölge */
            border-radius: 24px; /* Yüksek Yuvarlaklık */
            border: 1px solid rgba(220, 220, 230, 0.3); /* Çok İnce Çerçeve */
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .aesthetic-card:hover {
            transform: translateY(-5px); /* Daha belirgin hover */
            box-shadow: 0 20px 50px rgba(10, 20, 30, 0.1);
        }

        /* Modern Buton Stili */
        .modern-btn {
            padding: 12px 28px;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }
        .modern-btn-primary {
            background-color: #5b21b7; /* Koyu Mor (Violet-700) */
            color: white;
        }
        .modern-btn-primary:hover {
            background-color: #4c1d95; /* Daha Koyu Mor */
            box-shadow: 0 6px 15px rgba(91, 33, 183, 0.4);
        }
        
        /* Flat-Icon/Sidebar Simge Stili (Daha Estetik) */
        .flat-icon-styled {
            display: inline-flex;
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe); /* Yumuşak Gradient */
            color: #4f46e5; /* İndigo */
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1);
        }
        
        /* Sidebar Active Link Stili */
        .sidebar-link.active {
            background-color: #4f46e5; /* Indigo */
            color: white;
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
        }
        .sidebar-link.active .flat-icon-styled {
            background: white;
            color: #4f46e5;
        }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-72 bg-white fixed h-full p-6 shadow-2xl z-50 overflow-y-auto">
        <div class="text-3xl font-extrabold text-violet-700 mb-10 tracking-wide border-b pb-4">
            AESTHETICA <span class="text-gray-900">PMS</span>
        </div>

        <nav class="space-y-6">
            <div>
                <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">OTEL OPERASYON</h3>
                <a href="#" class="sidebar-link active flex items-center p-3 rounded-xl transition duration-200">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-chart-line"></i></span>
                    <span class="text-sm font-semibold">Dashboard İstatistikleri</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-calendar-alt"></i></span>
                    <span class="text-sm font-medium">Müsaitlik Takvimi</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-bookmark"></i></span>
                    <span class="text-sm font-medium">Rezervasyonlar</span>
                </a>
            </div>

            <div>
                <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">ODA VE MÜŞTERİ</h3>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-bed"></i></span>
                    <span class="text-sm font-medium">Odalar</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-camera"></i></span>
                    <span class="text-sm font-medium">Oda Resimleri / Galeri</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-users"></i></span>
                    <span class="text-sm font-medium">Müşteriler</span>
                </a>
            </div>
            
            <div>
                <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">DİJİTAL İÇERİK</h3>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-pencil-alt"></i></span>
                    <span class="text-sm font-medium">Blog Yönetimi</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-file-alt"></i></span>
                    <span class="text-sm font-medium">Hakkımızda Sayfası</span>
                </a>
            </div>
            
            <div>
                <h3 class="text-xs uppercase font-bold text-gray-400 mb-3 ml-2">SİSTEM</h3>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-inbox"></i></span>
                    <span class="text-sm font-medium">İletişim Mesajları</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-bell"></i></span>
                    <span class="text-sm font-medium">Bildirimler</span>
                </a>
                <a href="#" class="sidebar-link flex items-center p-3 rounded-xl hover:bg-gray-50 text-gray-700">
                    <span class="flat-icon-styled mr-3"><i class="fas fa-user-shield"></i></span>
                    <span class="text-sm font-medium">Kullanıcılar & Roller</span>
                </a>
            </div>
        </nav>
    </aside>

    <div class="flex-grow ml-72">
        
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
                </div>
            </div>
        </header>

        <main class="p-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-4 gap-6">

                <div class="aesthetic-card p-6 flex items-center justify-between border-b-4 border-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Güncel Doluluk Oranı</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-1">92<span class="text-2xl">%</span></p>
                        <span class="text-xs text-green-600 font-semibold flex items-center"><i class="fas fa-caret-up mr-1"></i> %3.1 Artış</span>
                    </div>
                    <div class="flat-icon-styled bg-indigo-100 text-indigo-500"><i class="fas fa-hotel"></i></div>
                </div>

                <div class="aesthetic-card p-6 flex items-center justify-between border-b-4 border-green-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Beklenen Gelir (AY)</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-1">₺2.1M</p>
                        <span class="text-xs text-gray-500 font-semibold">₺30K Fatura Bekliyor</span>
                    </div>
                    <div class="flat-icon-styled bg-green-100 text-green-500"><i class="fas fa-wallet"></i></div>
                </div>

                <div class="aesthetic-card p-6 flex items-center justify-between border-b-4 border-yellow-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Aktif Misafir Sayısı</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-1">195</p>
                        <span class="text-xs text-yellow-600 font-semibold">15 Check-in Geliyor</span>
                    </div>
                    <div class="flat-icon-styled bg-yellow-100 text-yellow-500"><i class="fas fa-user-friends"></i></div>
                </div>
                
                <div class="aesthetic-card p-6 flex items-center justify-between border-b-4 border-rose-500">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Ort. Kalış Süresi</p>
                        <p class="text-4xl font-extrabold text-gray-900 mt-1">4.2 <span class="text-2xl">Gün</span></p>
                        <span class="text-xs text-rose-600 font-semibold flex items-center"><i class="fas fa-caret-down mr-1"></i> %0.5 Düşüş</span>
                    </div>
                    <div class="flat-icon-styled bg-rose-100 text-rose-500"><i class="fas fa-clock"></i></div>
                </div>

            </div>
            
            <div class="lg:col-span-3 space-y-8">
                
                <div class="aesthetic-card p-6">
                    <div class="flex justify-between items-center mb-4 border-b pb-3">
                        <h2 class="text-2xl font-semibold text-gray-800">Rezervasyon Kanal Analizi</h2>
                        <button class="text-xs text-gray-600 hover:text-violet-600 font-medium">Yıllık Verileri Gör</button>
                    </div>
                    <div class="h-80 bg-gray-50 flex items-center justify-center rounded-xl text-gray-400 border-dashed border-2 p-4">
                        
                        <p class="font-medium text-sm">Çubuk ve Pasta Grafik Placeholder: Kanal Bazlı Rezervasyon ve Gelir Dağılımı</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div class="aesthetic-card p-5 bg-violet-600 text-white">
                        <h3 class="text-lg font-bold mb-3 border-b border-violet-400 pb-2">Oda Hazırlık Durumu</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex justify-between items-center">
                                <span>Boş & Temiz:</span>
                                <span class="text-xl font-extrabold text-lime-300">22</span>
                            </li>
                            <li class="flex justify-between items-center">
                                <span>Bakımda (Engelli):</span>
                                <span class="text-xl font-extrabold text-red-300">3</span>
                            </li>
                            <li class="flex justify-between items-center">
                                <span>Temizlik Bekleyen:</span>
                                <span class="text-xl font-extrabold text-amber-300">5</span>
                            </li>
                        </ul>
                        <button class="modern-btn bg-white bg-opacity-20 text-white hover:bg-opacity-30 mt-4 w-full py-2 text-sm">
                            Detaylı Oda Planı <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    <div class="aesthetic-card p-5">
                        <h3 class="text-lg font-semibold mb-3 border-b pb-2 text-gray-800">Yanıt Bekleyenler</h3>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <span class="flat-icon-styled bg-blue-100 text-blue-600"><i class="fas fa-envelope"></i></span>
                                <div>
                                    <p class="font-medium text-gray-700">İletişim Mesajları</p>
                                    <p class="text-sm text-blue-500 font-bold">5 Yeni Talep</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="flat-icon-styled bg-orange-100 text-orange-600"><i class="fas fa-comments"></i></span>
                                <div>
                                    <p class="font-medium text-gray-700">Blog Yorumları</p>
                                    <p class="text-sm text-orange-500 font-bold">2 Onay Bekliyor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="aesthetic-card p-5">
                        <h3 class="text-lg font-semibold mb-3 border-b pb-2 text-gray-800">Blog Performansı</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <p class="text-gray-600">Bu Ay Okunma:</p>
                                <span class="font-bold text-lg text-violet-600">12.4K</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <p class="text-gray-600">Yeni Yayınlanan Yazı:</p>
                                <span class="font-bold text-lg text-green-600">3</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <p class="text-gray-600">En Popüler Etiket:</p>
                                <span class="font-bold text-sm text-gray-800">#YazFırsatları</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="lg:col-span-1 space-y-8">
                
                <div class="aesthetic-card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Hızlı Giriş</h2>
                    <form class="space-y-4">
                        <div class="relative">
                            <input type="text" placeholder="Misafir Adı Soyadı" class="w-full py-3 pl-4 pr-10 text-sm border border-gray-200 rounded-xl focus:border-violet-400 modern-input">
                            <i class="fas fa-user absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                        <div class="relative">
                            <input type="date" class="w-full py-3 pl-4 pr-10 text-sm border border-gray-200 rounded-xl focus:border-violet-400 modern-input">
                            <i class="fas fa-sign-in-alt absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                        <select class="w-full py-3 pl-4 pr-10 text-sm border border-gray-200 rounded-xl focus:border-violet-400 modern-input bg-white appearance-none">
                            <option>Oda Tipi (Rezervasyonlar)</option>
                            <option>Deluxe Suit</option>
                            <option>Ekonomi</option>
                        </select>
                        <button type="submit" class="w-full modern-btn modern-btn-primary py-3">
                            Rezervasyon Oluştur
                        </button>
                    </form>
                </div>
                
                <div class="aesthetic-card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Sistem Kullanıcıları</h2>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center text-sm">
                            <p class="font-medium text-gray-700">Müdür: <span class="text-xs text-violet-600 font-semibold">1</span></p>
                            <i class="fas fa-user-tie text-lg text-violet-400"></i>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <p class="font-medium text-gray-700">Resepsiyon: <span class="text-xs text-indigo-600 font-semibold">4</span></p>
                            <i class="fas fa-concierge-bell text-lg text-indigo-400"></i>
                        </li>
                        <li class="flex justify-between items-center text-sm">
                            <p class="font-medium text-gray-700">Housekeeping: <span class="text-xs text-green-600 font-semibold">6</span></p>
                            <i class="fas fa-broom text-lg text-green-400"></i>
                        </li>
                    </ul>
                    <a href="#" class="inline-block text-sm font-semibold mt-4 text-gray-500 hover:text-violet-600 border-b border-dashed">
                        Rolleri ve İzinleri Yönet
                    </a>
                </div>

            </div>
            
        </main>
    </div>
</body>
</html>