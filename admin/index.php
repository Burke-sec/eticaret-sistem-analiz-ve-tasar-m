<?php
session_start();

if ($_SESSION['email']=="") {
    header("Location: error.php");
    exit();
}

if ($_SESSION['admin'] == 0) {
    header("Location: error.php");
    exit();
}

$baglanti=mysqli_connect("localhost","root","","eticaret");
mysqli_set_charset($baglanti, "utf8");
$sorgu_urun = mysqli_query($baglanti, "SELECT * FROM urunler");
$adet=mysqli_num_rows($sorgu_urun);

$sorgu_uyelik = mysqli_query($baglanti, "SELECT * FROM kullanicilar WHERE onay=0");
$adet_uyelik=mysqli_num_rows($sorgu_uyelik);


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Sonkezburada</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <h1>Admin Paneli</h1>
        <nav>
            <ul>
                <li><a href="../index.php" class="nav-link">Siteye Dön</a></li>
                <li><a href="cikis.php" class="nav-link">Admin Çıkış</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-container">
        <div class="admin-sidebar">
            <a href="index.php" class="menu-item active">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="uyelik-onay.php" class="menu-item">
                <i class="fas fa-user-check"></i>
                Üyelik Onaylama
            </a>
            <a href="urun-ekle.php" class="menu-item">
                <i class="fas fa-plus-circle"></i>
                Ürün Ekleme
            </a>
            <a href="urun-duzenle.php" class="menu-item">
                <i class="fas fa-edit"></i>
                Ürün Düzenleme
            </a>
            <a href="urun-sil.php" class="menu-item">
                <i class="fas fa-trash-alt"></i>
                Ürün Silme
            </a>
            <a href="siparisler.php" class="menu-item">
                <i class="fa-solid fa-folder"></i>
                Siparişler
            </a>
            <a href="raporlama.php" class="menu-item">
                <i class="fas fa-flag"></i>
                Raporlama
            </a>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <h2>Hoş Geldiniz, <?php echo $_SESSION['ad'] ;?></h2>
                <span>Son Giriş: <?php date_default_timezone_set('Europe/Istanbul'); echo date('d M Y,H:i'); ?></span>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Toplam Ürün</h3>
                    <div class="value"><?php echo $adet;?></div>
                </div>
                <div class="stat-card">
                    <h3>Bekleyen Üyelikler</h3>
                    <div class="value"><?php echo $adet_uyelik;?></div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div>
            <p>&copy; 2025 Sonkezburada Admin Paneli. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html> 