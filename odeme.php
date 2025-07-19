<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'eticaret');
if ($db->connect_error) {
    die("Veritabanı bağlantı hatası: " . $db->connect_error);
}
mysqli_set_charset($db, "utf8");

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit();
}

$kullanici_id = intval($_SESSION['kullanici_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siparisi_onayla'])) {
    $db->query("INSERT INTO siparisler (user_id, siparis_tarihi) VALUES ($kullanici_id, NOW())");
    $siparis_id = $db->insert_id;
    
    $sepet_query = $db->query("SELECT id FROM sepet WHERE kullanici_id = $kullanici_id");
    $sepet = $sepet_query->fetch_assoc();
    $sepet_id = $sepet['id'];
    
    $sepet_urunleri = $db->query("
        SELECT urun_id, adet 
        FROM sepet_urunleri 
        WHERE sepet_id = $sepet_id
    ");
    
    while ($urun = $sepet_urunleri->fetch_assoc()) {
        $db->query("UPDATE urunler SET stok = stok - {$urun['adet']} WHERE id = {$urun['urun_id']}");
        
        $db->query("INSERT INTO siparis_detay (siparis_id, urun_id, adet, fiyat) 
                    SELECT $siparis_id, {$urun['urun_id']}, {$urun['adet']}, fiyat 
                    FROM urunler WHERE id = {$urun['urun_id']}");
    }
    
    $db->query("DELETE FROM sepet_urunleri WHERE sepet_id = $sepet_id");
    
    header("Location: word_rapor.php?siparis_id=$siparis_id");
    exit();
}

$sepet_query = $db->query("
    SELECT su.id, su.urun_id, su.adet, u.urun_ad, u.urun_aciklama, u.fiyat, u.resim 
    FROM sepet_urunleri su
    JOIN urunler u ON su.urun_id = u.id
    JOIN sepet s ON su.sepet_id = s.id
    WHERE s.kullanici_id = $kullanici_id
");

$sepet = $sepet_query->fetch_all(MYSQLI_ASSOC);

$toplam = 0;
foreach ($sepet as $urun) {
    $toplam += $urun['fiyat'] * $urun['adet'];
}
$kargo = $toplam > 200 ? 0 : 29.90;
$genel_toplam = $toplam + $kargo;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme - Sonkezburada</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
        <h1>SONKEZBURADA</h1>
        <nav>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="urunler.php">Ürünler</a></li>
                <li><a href="sepet.php">Sepet</a></li>
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                    <li><a href="admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['email'])): ?>
                    <li><a href="cikis.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <li><a href="giris.php">Giriş Yap</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if (isset($_SESSION['ad']) && isset($_SESSION['soyad'])): ?>
            <div class="user-info">
                Hoşgeldiniz, <?= htmlspecialchars($_SESSION['ad'] . ' ' . $_SESSION['soyad']); ?>
            </div>
        <?php endif; ?>
    </header>

    <main>
        <div class="container">
            <h2 class="section-title">Sipariş Onay</h2>
            
            <form method="POST" action="odeme.php">
                <div class="order-summary">
                    <h3>Sipariş Özeti</h3>
                    <div class="summary-item">
                        <span>Ara Toplam</span>
                        <span>₺<?php echo number_format($toplam, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Kargo</span>
                        <span><?php echo $kargo == 0 ? 'Ücretsiz' : '₺'.number_format($kargo, 2, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Toplam</span>
                        <span>₺<?php echo number_format($genel_toplam, 2, ',', '.'); ?></span>
                    </div>
                    
                    <button type="submit" name="siparisi_onayla" class="checkout-btn">Siparişi Onayla ve Word Raporu Oluştur</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Sonkezburada. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>