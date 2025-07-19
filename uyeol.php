<?php
$db = mysqli_connect('localhost', 'root', '', 'eticaret');
mysqli_set_charset($db, "utf8");
$degisken = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad = $db->real_escape_string($_POST['ad']);
    $soyad = $db->real_escape_string($_POST['soyad']);
    $email = $db->real_escape_string($_POST['email']);
    $sifre = $db->real_escape_string($_POST['sifre']);
    
    $sorgu = $db->query("SELECT * FROM kullanicilar WHERE email = '$email'");
    
    if ($sorgu->num_rows > 0) {
        $degisken = "Bu e-posta adresi zaten kayıtlı.";
        header("refresh:3;url=uyeol.php");
    } else {
        $ekle = $db->query("INSERT INTO kullanicilar (ad, soyad, email, sifre) 
                   VALUES ('$ad', '$soyad', '$email', '$sifre')");
        
        if ($ekle) {
            header("Location: giris.php?kayit=basarili");
            exit();
        } else {
            $degisken = "Kayıt işlemi başarısız oldu!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Ol - SONKEZBURADA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display: flex;">
<header>
        <h1>SONKEZBURADA</h1>
        <nav>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="urunler.php">Ürünler</a></li>
                <li><a href="sepet.php">Sepet</a></li>
                <li><a href="giris.php">Giriş Yap</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2 class="auth-title">Üye Ol</h2>
            <?php if (!empty($degisken)): ?>
                <div class="alert"><?php echo $degisken; ?></div>
            <?php endif; ?>
            <form action="uyeol.php" method="POST">
                <div class="form-group">
                    <label for="firstName">Ad</label>
                    <input type="text" id="firstName" name="ad" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Soyad</label>
                    <input type="text" id="lastName" name="soyad" required>
                </div>
                <div class="form-group">
                    <label for="email">E-Posta</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="sifre" required>
                </div>
                <button type="submit" class="auth-btn">Üye Ol</button>
                <div class="auth-links">
                    Zaten üye misiniz? <a href="giris.php">Giriş Yap</a>
                </div>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 SONKEZBURADA. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>