<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
$db = mysqli_connect('localhost', 'root', '', 'eticaret');

if ($db->connect_error) {
    die("Veritabanı bağlantı hatası: " . $db->connect_error);
}
mysqli_set_charset($db, "utf8");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sepete_ekle'])) {
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: giris.php?uyari=sepet");
        exit();
    }
    
    $urun_id = intval($_POST['urun_id']);
    $adet = intval($_POST['adet']);
    $kullanici_id = intval($_SESSION['kullanici_id']);
    
    $urun_sorgu = $db->query("SELECT stok FROM urunler WHERE id = $urun_id");
    if ($urun_sorgu->num_rows === 0) {
        header("Location: urunler.php?hata=urun_bulunamadi");
        exit();
    }
    
    $urun = $urun_sorgu->fetch_assoc();
    $stok = $urun['stok'];
    
    if ($adet <= 0 || $adet > $stok) {
        header("Location: urunler.php?hata=stok_yetersiz&urun_id=$urun_id");
        exit();
    }
    
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
        if ($yeni_adet > $stok) {
            header("Location: urunler.php?hata=stok_yetersiz&urun_id=$urun_id");
            exit();
        }
        $db->query("UPDATE sepet_urunleri SET adet = $yeni_adet WHERE id = {$sepetteki_urun['id']}");
    } else {
        $db->query("INSERT INTO sepet_urunleri (sepet_id, urun_id, adet) VALUES ($sepet_id, $urun_id, $adet)");
    }
    
    header("Location: urunler.php?eklendi=1");
    exit();
}

$sepet_sayisi = 0;
if (isset($_SESSION['kullanici_id'])) {
    $kullanici_id = intval($_SESSION['kullanici_id']);
    $sepet_sorgu = $db->query("SELECT id FROM sepet WHERE kullanici_id = $kullanici_id");
    
    if ($sepet_sorgu->num_rows > 0) {
        $sepet = $sepet_sorgu->fetch_assoc();
        $sepet_id = $sepet['id'];
        $sayac_sorgu = $db->query("SELECT SUM(adet) as toplam FROM sepet_urunleri WHERE sepet_id = $sepet_id");
        $sayac = $sayac_sorgu->fetch_assoc();
        $sepet_sayisi = isset($sayac['toplam']) ? $sayac['toplam'] : 0;
    }
}

$query = "SELECT * FROM urunler WHERE stok > 0";

if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $kategoriler = $_GET['kategori'];
    $kategoriList = "'" . implode("','", array_map([$db, 'real_escape_string'], $kategoriler)) . "'";
    $query .= " AND kategori IN ($kategoriList)";
}

if (isset($_GET['min_fiyat']) && is_numeric($_GET['min_fiyat'])) {
    $min_fiyat = floatval($_GET['min_fiyat']);
    $query .= " AND fiyat >= $min_fiyat";
}

if (isset($_GET['max_fiyat']) && is_numeric($_GET['max_fiyat'])) {
    $max_fiyat = floatval($_GET['max_fiyat']);
    $query .= " AND fiyat <= $max_fiyat";
}

if (isset($_GET['urun_ismi']) && !empty($_GET['urun_ismi'])) {
    $urun_ismi = $db->real_escape_string($_GET['urun_ismi']);
    $query .= " AND urun_ad LIKE '%$urun_ismi%'";
}

$sorgu = $db->query($query);
$urunler = [];
while ($urun = $sorgu->fetch_assoc()) {
    $urunler[] = $urun;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - SONKEZBURADA</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="icon/css/all.min.css">
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
        }
        .stock-info {
            font-size: 0.9rem;
            color: #666;
            margin-left: 10px;
        }
        .filters {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-section {
            margin-bottom: 15px;
        }
        .filter-section h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        .category-filters label {
            display: block;
            margin-bottom: 5px;
        }
        .price-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .price-inputs input {
            padding: 8px;
            width: 100px;
        }
        .apply-filters, .clear-filters {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .clear-filters {
            background: #f44336;
        }
        .no-products {
            text-align: center;
            padding: 20px;
            color: #666;
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
        <?php if(isset($_GET['eklendi'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Ürün başarıyla sepete eklendi!
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['hata']) && $_GET['hata'] == 'stok_yetersiz'): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> İstediğiniz miktarda ürün stokta bulunmamaktadır!
            </div>
        <?php elseif(isset($_GET['hata']) && $_GET['hata'] == 'urun_bulunamadi'): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> Ürün bulunamadı!
            </div>
        <?php endif; ?>

        <h2>Tüm Ürünler</h2>
        
        <form method="GET" action="urunler.php" class="filters">
            <div class="filter-section">
                <h3>Ürün İsmi</h3>
                <input type="text" name="urun_ismi" placeholder="Ürün Ara" value="<?php echo isset($_GET['urun_ismi']) ? htmlspecialchars($_GET['urun_ismi']) : ''; ?>">
            </div>

            <div class="filter-section">
                <h3>Kategoriler</h3>
                <div class="category-filters">
                    <label><input type="checkbox" name="kategori[]" value="elektronik" <?php echo (isset($_GET['kategori']) && in_array('elektronik', $_GET['kategori'])) ? 'checked' : ''; ?>> Elektronik</label>
                    <label><input type="checkbox" name="kategori[]" value="giyim" <?php echo (isset($_GET['kategori']) && in_array('giyim', $_GET['kategori'])) ? 'checked' : ''; ?>> Giyim</label>
                    <label><input type="checkbox" name="kategori[]" value="kitap" <?php echo (isset($_GET['kategori']) && in_array('kitap', $_GET['kategori'])) ? 'checked' : ''; ?>> Kitap</label>
                </div>
            </div>
            
            <div class="filter-section">
                <h3>Fiyat Aralığı</h3>
                <div class="price-inputs">
                    <input type="number" name="min_fiyat" placeholder="Min Fiyat" value="<?php echo isset($_GET['min_fiyat']) ? htmlspecialchars($_GET['min_fiyat']) : ''; ?>">
                    <span>-</span>
                    <input type="number" name="max_fiyat" placeholder="Max Fiyat" value="<?php echo isset($_GET['max_fiyat']) ? htmlspecialchars($_GET['max_fiyat']) : ''; ?>">
                </div>
            </div>
            
            <button type="submit" class="apply-filters">Filtreleri Uygula</button>
            <?php if(isset($_GET['kategori']) || isset($_GET['min_fiyat']) || isset($_GET['max_fiyat']) || isset($_GET['urun_ismi'])): ?>
                <a href="urunler.php" class="clear-filters" style="margin-left: 10px;">Filtreleri Temizle</a>
            <?php endif; ?>
        </form>

        <div class="product-list">
            <?php if(!empty($urunler)): ?>
                <?php foreach ($urunler as $urun): ?>
                <div class="product-card">
                    <img src="img/<?php echo htmlspecialchars($urun['resim']); ?>" alt="<?php echo htmlspecialchars($urun['urun_ad']); ?>">
                    <h3><?php echo htmlspecialchars($urun['urun_ad']); ?></h3>
                    <p class="price">₺<?php echo number_format($urun['fiyat'], 2, ',', '.'); ?></p>
                    
                    <form method="POST" action="urunler.php" class="product-form">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                        <div class="quantity-control">
                            <label for="quantity">Adet:</label>
                            <input type="number" id="quantity" name="adet" min="1" max="<?php echo $urun['stok']; ?>" value="1" class="quantity-input">
                            <span class="stock-info">Stok: <?php echo $urun['stok']; ?></span>
                        </div>
                        <button type="submit" name="sepete_ekle" class="button">Sepete Ekle</button>
                    </form>
                    
                    <a href="detay.php?id=<?php echo $urun['id']; ?>" class="button">Ürün Detayı</a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-products">Filtre kriterlerinize uygun ürün bulunamadı.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 SONKEZBURADA. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>