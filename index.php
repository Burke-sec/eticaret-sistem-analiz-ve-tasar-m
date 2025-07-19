<?php
session_start();
$baglanti = mysqli_connect("localhost", "root", "", "eticaret");

if (!$baglanti) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}
mysqli_set_charset($baglanti, "utf8");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sepete_ekle'])) {
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: giris.php?uyari=sepet");
        exit();
    }

    $urun_id = intval($_POST['urun_id']);
    $adet = intval($_POST['adet']);
    $kullanici_id = intval($_SESSION['kullanici_id']);

    $sepet = mysqli_query($baglanti, "SELECT id FROM sepet WHERE kullanici_id = $kullanici_id")->fetch_assoc();

    if (!$sepet) {
        mysqli_query($baglanti, "INSERT INTO sepet (kullanici_id) VALUES ($kullanici_id)");
        $sepet_id = mysqli_insert_id($baglanti);
    } else {
        $sepet_id = $sepet['id'];
    }

    $sepetteki_urun = mysqli_query($baglanti, "SELECT * FROM sepet_urunleri WHERE sepet_id = $sepet_id AND urun_id = $urun_id")->fetch_assoc();

    if ($sepetteki_urun) {
        $yeni_adet = $sepetteki_urun['adet'] + $adet;
        mysqli_query($baglanti, "UPDATE sepet_urunleri SET adet = $yeni_adet WHERE id = {$sepetteki_urun['id']}");
    } else {
        mysqli_query($baglanti, "INSERT INTO sepet_urunleri (sepet_id, urun_id, adet) VALUES ($sepet_id, $urun_id, $adet)");
    }

    header("Location: index.php?eklendi=1");
    exit();
}

$sepet_sayisi = 0;
if (isset($_SESSION['kullanici_id'])) {
    $kullanici_id = intval($_SESSION['kullanici_id']);
    $sepet_sorgu = mysqli_query($baglanti, "SELECT id FROM sepet WHERE kullanici_id = $kullanici_id");

    if (mysqli_num_rows($sepet_sorgu) > 0) {
        $sepet = mysqli_fetch_assoc($sepet_sorgu);
        $sepet_id = $sepet['id'];
        $sayac_sorgu = mysqli_query($baglanti, "SELECT SUM(adet) as toplam FROM sepet_urunleri WHERE sepet_id = $sepet_id");
        $sayac = mysqli_fetch_assoc($sayac_sorgu);
        $sepet_sayisi = isset($sayac['toplam']) ? $sayac['toplam'] : 0;
    }
}

$cokSatanSorgu = mysqli_query($baglanti, "SELECT * FROM urunler WHERE kategori='elektronik' AND stok > 0 LIMIT 3");
$cokSatanUrunler = [];
while ($row = mysqli_fetch_assoc($cokSatanSorgu)) {
    $cokSatanUrunler[] = $row;
}

$yeniGelenSorgu = mysqli_query($baglanti, "SELECT * FROM urunler WHERE stok > 0 ORDER BY id DESC LIMIT 2");
$yeniGelenUrunler = [];
while ($row = mysqli_fetch_assoc($yeniGelenSorgu)) {
    $yeniGelenUrunler[] = $row;
}

$stokAzalanSorgu = mysqli_query($baglanti, "SELECT * FROM urunler WHERE stok > 0 AND stok <= 30 LIMIT 2");
$stokAzalanUrunler = [];
while ($row = mysqli_fetch_assoc($stokAzalanSorgu)) {
    $stokAzalanUrunler[] = $row;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>SONKEZBURADA</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="icon/css/all.min.css">
    <style>
        .product-card {
            position: relative;
        }
        .out-of-stock {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .low-stock {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff9800;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .stock-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .button.disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
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

    <div class="container">
        <?php if (isset($_GET['eklendi'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Ürün başarıyla sepete eklendi!
            </div>
        <?php endif; ?>

        <h2>Çok Satılan Ürünler</h2>
        <div class="product-list">
            <?php foreach ($cokSatanUrunler as $urun): ?>
                <div class="product-card">
                    <img src="img/<?= htmlspecialchars($urun['resim']) ?>" alt="<?= htmlspecialchars($urun['urun_ad']) ?>">
                    <h3><?= htmlspecialchars($urun['urun_ad']) ?></h3>
                    <p>₺<?= number_format($urun['fiyat'], 2, ',', '.') ?></p>
                    <p class="stock-info">Stok: <?= $urun['stok'] ?></p>

                    <?php if ($urun['stok'] > 0): ?>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                            <input type="hidden" name="adet" value="1">
                            <button type="submit" name="sepete_ekle" class="button">Sepete Ekle</button>
                        </form>
                    <?php else: ?>
                        <div class="out-of-stock">STOKTA YOK</div>
                        <button class="button disabled" disabled>Sepete Ekle</button>
                    <?php endif; ?>

                    <a href="detay.php?id=<?= $urun['id'] ?>" class="button">Detay Gör</a>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Yeni Gelenler</h2>
        <div class="product-list">
            <?php foreach ($yeniGelenUrunler as $urun): ?>
                <div class="product-card">
                    <img src="img/<?= htmlspecialchars($urun['resim']) ?>" alt="<?= htmlspecialchars($urun['urun_ad']) ?>">
                    <h3><?= htmlspecialchars($urun['urun_ad']) ?></h3>
                    <p>₺<?= number_format($urun['fiyat'], 2, ',', '.') ?></p>
                    <p class="stock-info">Stok: <?= $urun['stok'] ?></p>

                    <?php if ($urun['stok'] > 0): ?>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                            <input type="hidden" name="adet" value="1">
                            <button type="submit" name="sepete_ekle" class="button">Sepete Ekle</button>
                        </form>
                    <?php else: ?>
                        <div class="out-of-stock">STOKTA YOK</div>
                        <button class="button disabled" disabled>Sepete Ekle</button>
                    <?php endif; ?>

                    <a href="detay.php?id=<?= $urun['id'] ?>" class="button">Detay Gör</a>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Stoklar Tükeniyor</h2>
        <div class="product-list">
            <?php foreach ($stokAzalanUrunler as $urun): ?>
                <div class="product-card">
                    <img src="img/<?= htmlspecialchars($urun['resim']) ?>" alt="<?= htmlspecialchars($urun['urun_ad']) ?>">
                    <h3><?= htmlspecialchars($urun['urun_ad']) ?></h3>
                    <p>₺<?= number_format($urun['fiyat'], 2, ',', '.') ?></p>
                    <p class="stock-info">Stok: <?= $urun['stok'] ?></p>

                    <?php if ($urun['stok'] > 0): ?>
                        <?php if ($urun['stok'] <= 30): ?>
                            <div class="low-stock">STOK AZ</div>
                        <?php endif; ?>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                            <input type="hidden" name="adet" value="1">
                            <button type="submit" name="sepete_ekle" class="button">Sepete Ekle</button>
                        </form>
                    <?php else: ?>
                        <div class="out-of-stock">STOKTA YOK</div>
                        <button class="button disabled" disabled>Sepete Ekle</button>
                    <?php endif; ?>

                    <a href="detay.php?id=<?= $urun['id'] ?>" class="button">Detay Gör</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 SONKEZBURADA. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>