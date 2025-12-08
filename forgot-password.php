<?php
// forgot-password.php
session_start();

require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Lütfen geçerli bir email adresi girin.";
    } else {
        try {
            $db = getDB();
            
            // Kullanıcıyı kontrol et
            $stmt = $db->prepare("SELECT id, ad, soyad FROM kullanicilar WHERE email = ? AND durum = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Reset token oluştur
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Token'ı veritabanına kaydet
                $stmt = $db->prepare("UPDATE kullanicilar SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                // Email gönderimi (bu kısmı kendi email sisteminize göre düzenleyin)
                $reset_link = "https://yourdomain.com/reset-password.php?token=$token";
                $subject = "Aesthetica PMS - Şifre Sıfırlama";
                $body = "Merhaba {$user['ad']},\n\n";
                $body .= "Şifre sıfırlama isteğinde bulundunuz. Aşağıdaki linke tıklayarak şifrenizi sıfırlayabilirsiniz:\n\n";
                $body .= "$reset_link\n\n";
                $body .= "Bu link 1 saat boyunca geçerlidir.\n\n";
                $body .= "Eğer bu isteği siz yapmadıysanız, bu emaili görmezden gelebilirsiniz.\n\n";
                $body .= "Saygılarımızla,\nAesthetica PMS Ekibi";
                
                // Basit email gönderimi (gerçek uygulamada PHPMailer veya benzeri kullanın)
                $headers = "From: no-reply@aestheticapms.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                if (mail($email, $subject, $body, $headers)) {
                    $message = "Şifre sıfırlama bağlantısı email adresinize gönderildi. Lütfen email kutunuzu kontrol edin.";
                    logMessage("Password reset link sent to: $email", 'security');
                } else {
                    $error = "Email gönderilemedi. Lütfen daha sonra tekrar deneyin.";
                }
            } else {
                $message = "Eğer bu email adresi kayıtlıysa, şifre sıfırlama bağlantısı gönderilecektir.";
                // Güvenlik için kullanıcı olmasa da başarılı mesaj göster
                logMessage("Password reset requested for non-existent email: $email", 'security');
            }
        } catch (PDOException $e) {
            $error = "Sistem hatası. Lütfen daha sonra tekrar deneyin.";
            logMessage("Password reset error: " . $e->getMessage(), 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Aesthetica PMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-key text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Şifremi Unuttum</h2>
                <p class="text-gray-600 mt-2">Email adresinizi girerek şifrenizi sıfırlayabilirsiniz</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <div class="text-center mt-6">
                    <a href="login.php" class="text-violet-600 hover:text-violet-800 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Giriş Sayfasına Dön
                    </a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2"></i> Email Adresi
                        </label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition"
                               placeholder="ornek@otel.com">
                    </div>
                    
                    <button type="submit" class="w-full bg-violet-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-violet-700 transition mb-4">
                        <i class="fas fa-paper-plane mr-2"></i> Şifre Sıfırlama Bağlantısı Gönder
                    </button>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-gray-600 hover:text-gray-800 text-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Giriş sayfasına dön
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>