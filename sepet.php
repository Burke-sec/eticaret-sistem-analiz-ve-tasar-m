<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Istanbul');
$db = mysqli_connect('localhost', 'root', '', 'eticaret');

if ($db->connect_error) {
    die("Veritabanı bağlantı hatası: " . $db->connect_error);
}
mysqli_set_charset($db, "utf8");

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php?uyari=sepet");
    exit();
}

$kullanici_id = intval($_SESSION['kullanici_id']);

if (isset($_GET['sil'])) {
    $silinecek_id = intval($_GET['sil']);
    $db->query("DELETE FROM sepet_urunleri WHERE id = $silinecek_id AND sepet_id = (SELECT id FROM sepet WHERE kullanici_id = $kullanici_id)");
    header("Location: sepet.php?silindi=1");
    exit();
} elseif (isset($_POST['adet_guncelle'])) {
    foreach ($_POST['adet'] as $urun_id => $yeni_adet) {
        $urun_id = intval($urun_id);
        $yeni_adet = intval($yeni_adet);
        
        $stok_sorgu = $db->query("SELECT u.stok FROM urunler u 
                                 JOIN sepet_urunleri su ON u.id = su.urun_id 
                                 WHERE su.id = $urun_id");
        if ($stok_sorgu->num_rows > 0) {
            $stok = $stok_sorgu->fetch_assoc()['stok'];
            if ($yeni_adet > $stok) {
                header("Location: sepet.php?hata=stok_yetersiz&urun_id=$urun_id");
                exit();
            }
        }
        
        if ($yeni_adet > 0) {
            $db->query("UPDATE sepet_urunleri SET adet = $yeni_adet WHERE id = $urun_id AND sepet_id = (SELECT id FROM sepet WHERE kullanici_id = $kullanici_id)");
        }
    }
    header("Location: sepet.php?guncellendi=1");
    exit();
} elseif (isset($_POST['odeme'])) {
    header("Location: odeme.php");
    exit();
}

$sepet_query = $db->query("
    SELECT su.id, su.urun_id, su.adet, u.urun_ad, u.urun_aciklama, u.fiyat, u.resim, u.stok 
    FROM sepet_urunleri su
    JOIN urunler u ON su.urun_id = u.id
    JOIN sepet s ON su.sepet_id = s.id
    WHERE s.kullanici_id = $kullanici_id
");

if (!$sepet_query) {
    die("Sorgu hatası: " . $db->error);
}

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
    <title>Sepetim - SONKEZBURADA</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="icon/css/all.min.css">
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .empty-cart {
            text-align: center;
            padding: 40px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        .empty-cart i {
            font-size: 50px;
            color: #ccc;
            margin-bottom: 20px;
        }
        .empty-cart p {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        .quantity-controls input {
            width: 60px;
            padding: 8px;
            text-align: center;
        }
        .stock-info {
            font-size: 0.8rem;
            color: #666;
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body style="display: flex;">
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
            <h2 class="section-title">Alışveriş Sepetim</h2>

            <?php if (isset($_GET['silindi'])): ?>
                <div class='success-message'>Ürün başarıyla silindi!</div>
            <?php elseif (isset($_GET['guncellendi'])): ?>
                <div class='success-message'>Ürün başarıyla güncellendi!</div>
            <?php elseif (isset($_GET['hata']) && $_GET['hata'] == 'stok_yetersiz'): ?>
                <div class='error-message'>
                    <i class="fas fa-exclamation-circle"></i> İstediğiniz miktarda ürün stokta bulunmamaktadır!
                </div>
            <?php endif; ?>

            <form method="POST" action="sepet.php">
                <div class="cart-container">
                    <div class="cart-items">
                        <?php if (empty($sepet)): ?>
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <p>Sepetiniz boş</p>
                                <a href="urunler.php" class="button">Alışverişe Başla</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($sepet as $urun): ?>
                                <div class="cart-item">
                                    <img src="img/<?php echo htmlspecialchars($urun['resim']); ?>" alt="<?php echo htmlspecialchars($urun['urun_ad']); ?>">
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($urun['urun_ad']); ?></h3>
                                        <p class="item-description">
                                            <?php echo htmlspecialchars($urun['urun_aciklama']); ?>
                                        </p>
                                    </div>
                                    <div class="quantity-controls">
                                        <input type="number" name="adet[<?php echo $urun['id']; ?>]" value="<?php echo $urun['adet']; ?>" min="1" max="<?php echo $urun['stok']; ?>" class="quantity-input">
                                        <span class="stock-info">Maksimum: <?php echo $urun['stok']; ?></span>
                                    </div>
                                    <div class="item-price">₺<?php echo number_format($urun['fiyat'] * $urun['adet'], 2, ',', '.'); ?></div>
                                    <a href="sepet.php?sil=<?php echo $urun['id']; ?>" class="remove-item"><i class="fas fa-trash"></i></a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($sepet)): ?>
                        <div class="cart-summary">
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
                            <button type="submit" name="adet_guncelle" class="checkout-btn secondary">Sepeti Güncelle</button>
                            <button type="submit" name="odeme" class="checkout-btn">Ödemeye Geç</button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 SONKEZBURADA. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>