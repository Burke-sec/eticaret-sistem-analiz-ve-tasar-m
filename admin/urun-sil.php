<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header("Location: ../error.php");
    exit();
}

$baglanti = mysqli_connect("localhost", "root", "", "eticaret");
if (!$baglanti) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}
mysqli_set_charset($baglanti, "utf8");

if (isset($_GET['sil_id'])) {
    $sil_id = intval($_GET['sil_id']);
    $sorgu = "DELETE FROM urunler WHERE id = $sil_id";
    mysqli_query($baglanti, $sorgu);
    header("Location: urun-sil.php?silindi=1");
    exit();
}

$arama = '';
if (isset($_GET['arama']) && !empty($_GET['arama'])) {
    $arama = mysqli_real_escape_string($baglanti, $_GET['arama']);
    $sorgu = mysqli_query($baglanti, "SELECT * FROM urunler WHERE urun_ad LIKE '%$arama%'");
} else {
    $sorgu = mysqli_query($baglanti, "SELECT * FROM urunler");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Silme - Sonkezburada</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .search-form {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-form input {
            padding: 8px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-form .search-btn {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
        <a href="urun-ekle.php" class="menu-item">
            <i class="fas fa-plus-circle"></i> 
            Ürün Ekleme
        </a>
        <a href="urun-duzenle.php" class="menu-item">
            <i class="fas fa-edit"></i> 
            Ürün Düzenleme
        </a>
        <a href="urun-sil.php" class="menu-item active">
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
            <h2>Ürün Silme</h2>
            <form method="GET" action="urun-sil.php" class="search-form">
                <input type="text" name="arama" placeholder="Ürün Ara..." value="<?php echo htmlspecialchars($arama); ?>">
                <button type="submit" class="search-btn">Ara</button>
            </form>
        </div>

        <?php if (isset($_GET['silindi'])): ?>
            <div class="success-message">Ürün başarıyla silindi.</div>
        <?php endif; ?>

        <div class="product-list">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Görsel</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($sorgu) > 0) {
                        while ($row = mysqli_fetch_assoc($sorgu)) {
                            echo "<tr>";
                            echo "<td><img src='../img/" . htmlspecialchars($row['resim']) . "' class='product-image'></td>";
                            echo "<td>" . htmlspecialchars($row['urun_ad']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
                            echo "<td>₺" . number_format($row['fiyat'], 2, ',', '.') . "</td>";
                            echo "<td>" . $row['stok'] . "</td>";
                            echo "<td><a href='urun-sil.php?sil_id=" . $row['id'] . "' class='action-btn delete-btn' onclick='return confirm(\"Bu ürünü silmek istediğinize emin misiniz?\")'>Sil</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Hiç ürün bulunamadı.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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
<?php mysqli_close($baglanti); ?>
