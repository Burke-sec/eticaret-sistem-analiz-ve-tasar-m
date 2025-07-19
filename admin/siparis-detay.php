<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Istanbul');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header("Location: ../error.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: siparisler.php");
    exit();
}

$siparis_id = intval($_GET['id']);

$baglanti = mysqli_connect("localhost", "root", "", "eticaret");
if (!$baglanti) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}
mysqli_set_charset($baglanti, "utf8");

$siparis_sorgu = mysqli_query($baglanti, "
    SELECT s.*, k.ad, k.soyad, k.email
    FROM siparisler s
    INNER JOIN kullanicilar k ON s.user_id = k.id
    WHERE s.id = $siparis_id
");

if (mysqli_num_rows($siparis_sorgu) == 0) {
    header("Location: siparisler.php");
    exit();
}

$siparis = mysqli_fetch_assoc($siparis_sorgu);

$detay_sorgu = mysqli_query($baglanti, "
    SELECT sd.*, u.urun_ad, u.resim
    FROM siparis_detay sd
    INNER JOIN urunler u ON sd.urun_id = u.id
    WHERE sd.siparis_id = $siparis_id
");

$toplam_tutar = 0;
while ($detay = mysqli_fetch_assoc($detay_sorgu)) {
    $toplam_tutar += $detay['adet'] * $detay['fiyat'];
}

mysqli_data_seek($detay_sorgu, 0);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Detayı - Admin Paneli</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .order-detail-container {
            padding: 20px;
            background: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .order-id {
            font-size: 1.5em;
            color: #3A7BD5;
            font-weight: bold;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            background: #e9ecef;
            color: #495057;
        }
        
        .order-status.Beklemede {
            background: #fff3cd;
            color: #856404;
        }
        
        .order-status.Hazırlanıyor {
            background: #cce5ff;
            color: #004085;
        }
        
        .order-status.Kargoda {
            background: #d4edda;
            color: #155724;
        }
        
        .order-status.Tamamlandı {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .order-status.İptal-Edildi {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-date {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .order-sections {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .order-section {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            font-size: 1.1em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #3A7BD5;
        }
        
        .customer-info p, .shipping-info p {
            margin: 5px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 100px;
        }
        
        .order-items {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            text-align: left;
            padding: 10px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .product-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .product-name {
            font-weight: bold;
        }
        
        .quantity {
            display: inline-block;
            padding: 3px 8px;
            background: #e9ecef;
            border-radius: 10px;
            font-size: 0.9em;
        }
        
        .price {
            font-weight: bold;
        }
        
        .order-summary {
            display: flex;
            justify-content: flex-end;
        }
        
        .summary-table {
            width: 300px;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .total-row {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .back-btn {
            display: inline-block;
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
    </style>
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
        <a href="urun-sil.php" class="menu-item">
            <i class="fas fa-trash-alt"></i>
            Ürün Silme
        </a>
        <a href="siparisler.php" class="menu-item active">
            <i class="fa-solid fa-folder"></i>
            Siparişler
        </a>
        <a href="raporlama.php" class="menu-item">
            <i class="fas fa-flag"></i>
            Raporlama
        </a>
    </div>
    
    <div class="order-detail-container">
        <div class="order-header">
            <div>
                <span class="order-id">Sipariş #<?= $siparis['id'] ?></span>
                <span class="order-date"><?= date('d M Y,H:i', strtotime($siparis['siparis_tarihi'])) ?></span>
            </div>
            <div class="order-status <?= str_replace(' ', '-', $siparis['durum']) ?>">
                <?= $siparis['durum'] ?>
            </div>
        </div>
        
        <div class="order-sections">
            <div class="order-section customer-info">
                <h3 class="section-title">Müşteri Bilgileri</h3>
                <p><span class="info-label">Ad Soyad:</span> <?= htmlspecialchars($siparis['ad'] . ' ' . $siparis['soyad']) ?></p>
                <p><span class="info-label">E-posta:</span> <?= htmlspecialchars($siparis['email']) ?></p>
            </div>

        </div>
        
        <div class="order-items">
            <h3 class="section-title">Sipariş Öğeleri</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Fiyat</th>
                        <th>Adet</th>
                        <th>Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($detay = mysqli_fetch_assoc($detay_sorgu)): ?>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <img src="../img/<?= htmlspecialchars($detay['resim']) ?>" alt="<?= htmlspecialchars($detay['urun_ad']) ?>" class="product-image">
                                    <span class="product-name"><?= htmlspecialchars($detay['urun_ad']) ?></span>
                                </div>
                            </td>
                            <td class="price">₺<?= number_format($detay['fiyat'], 2, ',', '.') ?></td>
                            <td><span class="quantity"><?= $detay['adet'] ?> adet</span></td>
                            <td class="price">₺<?= number_format($detay['adet'] * $detay['fiyat'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="order-summary">
            <table class="summary-table">
                <tr>
                    <td>Ara Toplam:</td>
                    <td class="text-right">₺<?= number_format($toplam_tutar, 2, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Kargo:</td>
                    <td class="text-right">₺0,00</td>
                </tr>
                <tr class="total-row">
                    <td>Toplam:</td>
                    <td class="text-right">₺<?= number_format($toplam_tutar, 2, ',', '.') ?></td>
                </tr>
            </table>
        </div>
        
        <a href="siparisler.php" class="back-btn"><i class="fas fa-arrow-left"></i> Sipariş Listesine Dön</a>
    </div>
    
</div>

</body>
</html>
<?php
mysqli_close($baglanti);
?>