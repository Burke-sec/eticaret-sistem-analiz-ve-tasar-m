<?php
session_start();

$db = mysqli_connect('localhost', 'root', '', 'eticaret');

if ($db->connect_error) {
    die("Veritabanı bağlantı hatası: " . $db->connect_error);
}
mysqli_set_charset($db, "utf8");

$urun_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sorgu = $db->prepare("SELECT * FROM urunler WHERE id = ?");
$sorgu->bind_param("i", $urun_id);
$sorgu->execute();
$sonuc = $sorgu->get_result();
$urun = $sonuc->fetch_assoc();

if (!$urun) {
    die("Ürün bulunamadı!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sepete_ekle'])) {
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: giris.php?uyari=sepet");
        exit();
    }
    
    $adet = intval($_POST['adet']);
    $kullanici_id = intval($_SESSION['kullanici_id']);
    
    $sepet = $db->query("SELECT id FROM sepet WHERE kullanici_id = $kullanici_id")->fetch_assoc();
    
    if (!$sepet) {
        $db->query("INSERT INTO sepet (kullanici_id) VALUES ($kullanici_id)");
        $sepet_id = $db->insert_id;
    } else {
        $sepet_id = $sepet['id'];
    }

    $sepetteki_urun = $db->query("SELECT * FROM sepet_urunleri WHERE sepet_id = $sepet_id AND urun_id = $urun_id")->fetch_assoc();
    
    if ($sepetteki_urun) {
        $yeni_adet = $sepetteki_urun['adet'] + $adet;
        $db->query("UPDATE sepet_urunleri SET adet = $yeni_adet WHERE id = {$sepetteki_urun['id']}");
    } else {
        $db->query("INSERT INTO sepet_urunleri (sepet_id, urun_id, adet) VALUES ($sepet_id, $urun_id, $adet)");
    }
    
    header("Location: sepet.php?eklendi=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($urun['urun_ad']); ?> - SONKEZBURADA</title>
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
        <div class="product-detail">
            <div class="product-image">
                <img src="img/<?php echo htmlspecialchars($urun['resim']); ?>" alt="<?php echo htmlspecialchars($urun['urun_ad']); ?>">
            </div>
            <div class="product-info">
                <h2><?php echo htmlspecialchars($urun['urun_ad']); ?></h2>
                <p class="price">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></p>
                <p class="description"><?php echo htmlspecialchars($urun['urun_aciklama']); ?></p>
                
                <form method="POST" action="detay.php?id=<?php echo $urun_id; ?>">
                    <div class="quantity">
                        <label for="quantity">Adet:</label>
                        <input type="number" id="quantity" name="adet" min="1" max="<?php echo $urun['stok']; ?>" value="1">
                        <span class="stock-info">Stok: <?php echo $urun['stok']; ?></span>
                    </div>

                    <button type="submit" name="sepete_ekle" class="button">Sepete Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 SONKEZBURADA. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>