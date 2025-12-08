<?php
// login.php
session_start();

// CSRF token oluştur
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Eğer giriş yapılmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$attempts = $_SESSION['login_attempts'] ?? 0;
$lock_time = $_SESSION['login_lock_time'] ?? 0;

// 5 başarısız denemeden sonra 15 dakika blok
if ($attempts >= 5 && time() - $lock_time < 900) { // 15 dakika = 900 saniye
    $remaining_time = ceil((900 - (time() - $lock_time)) / 60);
    $error = "Çok fazla başarısız giriş denemesi. Lütfen $remaining_time dakika sonra tekrar deneyin.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Güvenlik hatası. Lütfen sayfayı yenileyin.";
    } else {
        require_once 'config/database.php';
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Input validation
        if (empty($email) || empty($password)) {
            $error = "Lütfen tüm alanları doldurun.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Geçerli bir email adresi girin.";
        } else {
            try {
                $db = getDB();
                
                // Kullanıcıyı bul (aktif ve silinmemiş)
                $stmt = $db->prepare("
                    SELECT k.*, r.rol_adi 
                    FROM kullanicilar k
                    LEFT JOIN roller r ON k.rol_id = r.id
                    WHERE k.email = ? AND k.durum = 1
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Şifre kontrolü
                    if (password_verify($password, $user['sifre'])) {
                        // Başarılı giriş - session başlat
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['ad'] . ' ' . $user['soyad'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['rol_id'];
                        $_SESSION['role_name'] = $user['rol_adi'];
                        $_SESSION['user_avatar'] = $user['profil_resmi'] ?? '';
                        $_SESSION['login_time'] = time();
                        
                        // Remember me özelliği
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expiry = time() + (30 * 24 * 60 * 60); // 30 gün
                            
                            setcookie('remember_token', $token, $expiry, '/', '', false, true);
                            
                            // Token'ı veritabanına kaydet
                            $stmt = $db->prepare("UPDATE kullanicilar SET remember_token = ? WHERE id = ?");
                            $stmt->execute([$token, $user['id']]);
                        }
                        
                        // Başarılı giriş sayısını sıfırla
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['login_lock_time'] = 0;
                        
                        // Son giriş tarihini güncelle
                        $updateStmt = $db->prepare("UPDATE kullanicilar SET son_giris = NOW() WHERE id = ?");
                        $updateStmt->execute([$user['id']]);
                        
                        // Giriş logu kaydet
                        logMessage("{$user['email']} başarıyla giriş yaptı.", 'login');
                        
                        // Başarı mesajı ve yönlendirme
                        $_SESSION['login_success'] = true;
                        header('Location: index.php');
                        exit;
                    } else {
                        // Yanlış şifre
                        $_SESSION['login_attempts'] = ++$attempts;
                        if ($attempts >= 5) {
                            $_SESSION['login_lock_time'] = time();
                            $error = "Çok fazla başarısız giriş denemesi. Hesabınız 15 dakika boyunca kilitlendi.";
                            logMessage("$email için çok fazla başarısız giriş denemesi. Hesap kilitlendi.", 'security');
                        } else {
                            $remaining_attempts = 5 - $attempts;
                            $error = "Geçersiz şifre. Kalan deneme hakkı: $remaining_attempts";
                        }
                        logMessage("$email için yanlış şifre denemesi.", 'security');
                    }
                } else {
                    // Kullanıcı bulunamadı
                    $_SESSION['login_attempts'] = ++$attempts;
                    if ($attempts >= 5) {
                        $_SESSION['login_lock_time'] = time();
                        $error = "Çok fazla başarısız giriş denemesi. IP adresiniz 15 dakika boyunca kilitlendi.";
                    } else {
                        $remaining_attempts = 5 - $attempts;
                        $error = "Geçersiz email adresi veya şifre. Kalan deneme hakkı: $remaining_attempts";
                    }
                    logMessage("$email için kullanıcı bulunamadı.", 'security');
                }
            } catch (PDOException $e) {
                // Geliştirme ortamında detaylı hata
                $error_message = (ENVIRONMENT === 'development') 
                    ? "Sistem hatası: " . $e->getMessage() 
                    : "Sistem geçici olarak hizmet veremiyor. Lütfen daha sonra tekrar deneyin.";
                
                $error = $error_message;
                logMessage("Database error in login: " . $e->getMessage(), 'error');
            }
        }
    }
}

// Başarılı çıkış durumu
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success = "Başarıyla çıkış yaptınız. Tekrar görüşmek üzere!";
}

// Forgot password başarı durumu
if (isset($_GET['reset']) && $_GET['reset'] == 'sent') {
    $success = "Şifre sıfırlama bağlantısı email adresinize gönderildi.";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aesthetica PMS - Giriş Yap</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 440px;
            animation: fadeIn 0.5s ease-out;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-icon {
            display: inline-flex;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .logo-icon i {
            font-size: 36px;
            color: white;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(90deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }
        
        .logo p {
            color: #6b7280;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f9fafb;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .form-input.with-icon {
            padding-left: 48px;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
        }
        
        .btn-primary {
            width: 100%;
            padding: 18px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #059669;
        }
        
        .alert i {
            margin-right: 12px;
            font-size: 20px;
        }
        
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 24px 0;
            font-size: 14px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            color: #4b5563;
            cursor: pointer;
        }
        
        .checkbox-label input {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid #d1d5db;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .forgot-link:hover {
            color: #5a67d8;
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            position: relative;
            margin: 32px 0;
            color: #9ca3af;
            font-size: 14px;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e5e7eb;
        }
        
        .divider::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e5e7eb;
        }
        
        .demo-credentials {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin-top: 32px;
            border: 1px dashed #d1d5db;
        }
        
        .demo-credentials h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .demo-credentials h3 i {
            margin-right: 8px;
            color: #667eea;
        }
        
        .demo-credentials p {
            font-size: 13px;
            color: #6b7280;
            margin: 4px 0;
        }
        
        .demo-credentials strong {
            color: #374151;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #6b7280;
            font-size: 13px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <h1>Aesthetica PMS</h1>
                <p>Otel Yönetim Sistemi</p>
            </div>
            
            <!-- Hata/Success Mesajları -->
            <?php if ($error): ?>
                <div class="alert alert-error shake">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope mr-2"></i> Email Adresi
                    </label>
                    <div style="position: relative;">
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input with-icon" 
                               placeholder="ornek@otel.com" 
                               required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               autocomplete="email">
                    </div>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock mr-2"></i> Şifre
                        </label>
                        <a href="forgot-password.php" class="forgot-link">
                            Şifremi Unuttum?
                        </a>
                    </div>
                    <div style="position: relative;">
                        <div class="input-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input with-icon" 
                               placeholder="••••••••" 
                               required
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Options -->
                <div class="options">
                    <label class="checkbox-label">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Beni Hatırla</span>
                    </label>
                    
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-shield-alt mr-1"></i>
                        <span>Güvenli Bağlantı</span>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-primary" id="submitBtn">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    <span>Giriş Yap</span>
                </button>
            </form>
            
            <!-- Demo Credentials (sadece development'ta) -->
            <?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
                <div class="demo-credentials">
                    <h3>
                        <i class="fas fa-flask"></i>
                        Demo Giriş Bilgileri
                    </h3>
                    <p><strong>Email:</strong> admin@otel.com</p>
                    <p><strong>Şifre:</strong> admin123</p>
                    <p class="text-xs mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Bu bilgiler sadece test ortamında geçerlidir.
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="footer">
                <p>
                    <i class="far fa-copyright mr-1"></i>
                    <?php echo date('Y'); ?> Aesthetica PMS. Tüm hakları saklıdır.
                </p>
                <p class="mt-1">
                    <span class="text-xs">v1.0.0</span>
                </p>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Şifre göster/gizle
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // İkon değiştir
                this.innerHTML = type === 'password' 
                    ? '<i class="fas fa-eye"></i>' 
                    : '<i class="fas fa-eye-slash"></i>';
            });
            
            // Form submission kontrolü
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            
            loginForm.addEventListener('submit', function() {
                // Butonu devre dışı bırak ve loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Giriş Yapılıyor...';
                
                // Formu gönder
                return true;
            });
            
            // Input focus efektleri
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.input-icon').style.color = '#667eea';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('.input-icon').style.color = '#9ca3af';
                });
            });
            
            // Enter tuşu ile submit
            document.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !submitBtn.disabled) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName === 'INPUT' && activeElement.type !== 'checkbox') {
                        loginForm.requestSubmit();
                    }
                }
            });
            
            // Hata durumunda input'u vurgula
            <?php if ($error): ?>
                const errorInputs = document.querySelectorAll('#email, #password');
                errorInputs.forEach(input => {
                    input.classList.add('shake');
                    setTimeout(() => {
                        input.classList.remove('shake');
                    }, 500);
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>