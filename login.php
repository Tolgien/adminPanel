<?php
// login.php
session_start();

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE email = ? AND durum = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['sifre'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['ad'] . ' ' . $user['soyad'];
            $_SESSION['user_role'] = $user['rol_id'];
            
            // Son giriş tarihini güncelle
            $updateStmt = $db->prepare("UPDATE kullanicilar SET son_giris = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = "Geçersiz email veya şifre!";
        }
    } catch(PDOException $e) {
        $error = "Sistem hatası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otel Yönetim Paneli - Giriş</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-violet-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="aesthetic-card p-8">
            <div class="text-center mb-8">
                <div class="text-4xl font-extrabold text-violet-700 mb-2">
                    AESTHETICA PMS
                </div>
                <p class="text-gray-600">Otel Yönetim Paneli Giriş</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i> Email Adresi
                    </label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition"
                           placeholder="admin@otel.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i> Şifre
                    </label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition"
                           placeholder="••••••••">
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-violet-600 focus:ring-violet-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-gray-700">Beni Hatırla</label>
                    </div>
                    <a href="#" class="text-sm text-violet-600 hover:text-violet-800">Şifremi Unuttum?</a>
                </div>
                
                <button type="submit" 
                        class="w-full bg-violet-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-violet-700 transition duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-sign-in-alt mr-2"></i> Giriş Yap
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    © <?php echo date('Y'); ?> Aesthetica PMS. Tüm hakları saklıdır.
                </p>
            </div>
        </div>
    </div>
</body>
</html>