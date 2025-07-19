<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header("Location: ../error.php");
    exit();
}

$baglanti = mysqli_connect("localhost", "root", "", "eticaret");
if (!$baglanti) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}
mysqli_set_charset($baglanti, "utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urun_ad = mysqli_real_escape_string($baglanti, $_POST['productName']);
    $urun_aciklama = mysqli_real_escape_string($baglanti, $_POST['description']);
    $fiyat = floatval($_POST['price']);
    $stok = intval($_POST['stock']);
    $kategori = mysqli_real_escape_string($baglanti, $_POST['category']);
    
    $resim_adi = '';
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
        $hedef_klasor = "../img/";
        $dosya_adi = basename($_FILES['productImage']['name']);
        $hedef_yol = $hedef_klasor . $dosya_adi;
        $dosya_tipi = strtolower(pathinfo($hedef_yol, PATHINFO_EXTENSION));
        
        $gecerli_tipler = array("jpg", "jpeg", "png", "gif");
        if (in_array($dosya_tipi, $gecerli_tipler)) {
            if (move_uploaded_file($_FILES['productImage']['tmp_name'], $hedef_yol)) {
                $resim_adi = $dosya_adi;
            }
        }
    }
    $ekle_sorgu = "INSERT INTO urunler (urun_ad, urun_aciklama, fiyat, stok, kategori, resim) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($baglanti, $ekle_sorgu);
    mysqli_stmt_bind_param($stmt, "ssdiss", $urun_ad, $urun_aciklama, $fiyat, $stok, $kategori, $resim_adi);
    
    if (mysqli_stmt_execute($stmt)) {
        $mesaj = "<div class='success-message'>Ürün başarıyla eklendi!</div>";
    } else {
        $mesaj = "<div class='error-message'>Ürün eklenirken hata oluştu: " . mysqli_error($baglanti) . "</div>";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekleme - Sonkezburada</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Paneli</h1>
        <nav>
            <ul>
                <li><a href="../index.php" class="nav-link">Siteye Dön</a></li>
                <li><a href="../cikis.php" class="nav-link">Admin Çıkış</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-container">
        <div class="admin-sidebar">
            <a href="index.php" class="menu-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="uyelik-onay.php" class="menu-item">
                <i class="fas fa-user-check"></i>
                Üyelik Onaylama
            </a>
            <a href="urun-ekle.php" class="menu-item active">
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
                <h2>Yeni Ürün Ekle</h2>
            </div>

            <?php if (isset($mesaj)) echo $mesaj; ?>

            <form class="product-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="productName">Ürün Adı</label>
                        <input type="text" id="productName" name="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <option value="default">Kategori Seçin</option>
                            <option value="elektronik">Elektronik</option>
                            <option value="kitap">Kitap</option>
                            <option value="giyim">Giyim</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Fiyat (₺)</label>
                        <input type="number" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stok Miktarı</label>
                        <input type="number" id="stock" name="stock" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Ürün Açıklaması</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label>Ürün Görseli</label>
                    <input type="file" id="productImage" name="productImage" accept="image/*" required>
                </div>
                <button type="submit" class="submit-btn">Ürünü Ekle</button>
            </form>
        </div>
    </div>

    <footer>
        <div>
            <p>&copy; 2025 Sonkezburada Admin Paneli. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
<?php
mysqli_close($baglanti);
?>