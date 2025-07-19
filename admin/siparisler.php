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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siparis_id']) && isset($_POST['durum'])) {
    $siparis_id = intval($_POST['siparis_id']);
    $durum = mysqli_real_escape_string($baglanti, $_POST['durum']);
    
    $update_query = "UPDATE siparisler SET durum = '$durum' WHERE id = $siparis_id";
    if (mysqli_query($baglanti, $update_query)) {
        $_SESSION['message'] = "Sipariş durumu güncellendi!";
    } else {
        $_SESSION['error'] = "Hata: " . mysqli_error($baglanti);
    }
    
    header("Location: siparisler.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sil_id'])) {
    $sil_id = intval($_POST['sil_id']);
    $delete_query = "DELETE FROM siparis_detay WHERE siparis_id = $sil_id";
    $delete_query1 = "DELETE FROM siparisler WHERE id = $sil_id";
    if (mysqli_query($baglanti, $delete_query)) {
        $_SESSION['message'] = "Sipariş başarıyla silindi!";
    } else {
        $_SESSION['error'] = "Hata: " . mysqli_error($baglanti);
    }
    mysqli_query($baglanti, $delete_query1);
    header("Location: siparisler.php");
    exit();
}

$sorgu = mysqli_query($baglanti, "
    SELECT s.id AS siparis_id, s.siparis_tarihi, s.durum, k.ad, k.soyad, k.email, 
           GROUP_CONCAT(u.urun_ad SEPARATOR ', ') AS urunler,
           SUM(sd.adet) AS toplam_adet,
           SUM(sd.adet * sd.fiyat) AS toplam_tutar
    FROM siparisler s
    INNER JOIN kullanicilar k ON s.user_id = k.id
    INNER JOIN siparis_detay sd ON s.id = sd.siparis_id
    INNER JOIN urunler u ON sd.urun_id = u.id
    GROUP BY s.id
    ORDER BY s.siparis_tarihi DESC
");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Siparişler - Admin Paneli</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
    .orders-container {
        padding: 20px;
        background: #f9f9f9;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 0.9em;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    .orders-table thead tr {
        background-color: #3A7BD5;
        color: #ffffff;
        text-align: left;
    }
    .orders-table th,
    .orders-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dddddd;
    }
    .orders-table tbody tr {
        transition: all 0.3s;
    }
    .orders-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }
    .orders-table tbody tr:last-of-type {
        border-bottom: 2px solid #3A7BD5;
    }
    .orders-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .status-select {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ddd;
        background: white;
        cursor: pointer;
        font-size: 0.85rem;
        transition: border-color 0.3s;
    }
    .status-select:focus {
        outline: none;
        border-color: #3A7BD5;
    }
    .update-status-btn {
        padding: 5px 10px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.85rem;
        margin-left: 5px;
        transition: background 0.2s;
    }
    .update-status-btn:hover {
        background: #218838;
    }
    .status-form {
        display: flex;
        align-items: center;
    }
    .customer-info {
        display: flex;
        flex-direction: column;
    }
    .customer-name {
        font-weight: bold;
    }
    .customer-email {
        font-size: 0.8em;
        color: #666;
    }
    .products-list {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .quantity-badge {
        display: inline-block;
        padding: 3px 8px;
        background: #e9ecef;
        border-radius: 10px;
        font-size: 0.8em;
    }
    .total-price {
        font-weight: bold;
        color: #28a745;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .view-btn, .delete-btn {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8em;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .view-btn {
        background: #17a2b8;
        color: white;
    }
    .view-btn:hover {
        background: #138496;
    }
    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        cursor: pointer;
    }
    .delete-btn:hover {
        background: #c82333;
    }
    .no-orders {
        text-align: center;
        padding: 30px;
        color: #666;
    }
    .alert {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
    }
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
    }
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 5px;
    }
    .pagination a {
        padding: 8px 16px;
        text-decoration: none;
        border: 1px solid #ddd;
        color: #3A7BD5;
        border-radius: 5px;
    }
    .pagination a.active {
        background-color: #3A7BD5;
        color: white;
        border: 1px solid #3A7BD5;
    }
    .pagination a:hover:not(.active) {
        background-color: #ddd;
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
    <div class="orders-container">
        <div class="orders-header">
            <h2><i class="fas fa-shopping-cart"></i> Siparişler</h2>
            <div class="search-box">
                <input type="text" placeholder="Sipariş ara...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?= $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Sipariş No</th>
                    <th>Müşteri</th>
                    <th>Tarih</th>
                    <th>Ürünler</th>
                    <th>Adet</th>
                    <th>Toplam</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($sorgu) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($sorgu)): ?>
                        <tr>
                            <td class="order-id">#<?= htmlspecialchars($row['siparis_id']); ?></td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name"><?= htmlspecialchars($row['ad'] . ' ' . htmlspecialchars($row['soyad'])); ?></span>
                                    <span class="customer-email"><?= htmlspecialchars($row['email']); ?></span>
                                </div>
                            </td>
                            <td class="order-date"><?= date('d.m.Y H:i', strtotime($row['siparis_tarihi'])); ?></td>
                            <td class="products-list" title="<?= htmlspecialchars($row['urunler']); ?>">
                                <?= htmlspecialchars($row['urunler']); ?>
                            </td>
                            <td><span class="quantity-badge"><?= htmlspecialchars($row['toplam_adet']); ?> adet</span></td>
                            <td class="total-price">₺<?= number_format($row['toplam_tutar'], 2, ',', '.'); ?></td>
                            <td>
                                <form class="status-form" method="POST" action="siparisler.php">
                                    <input type="hidden" name="siparis_id" value="<?= $row['siparis_id']; ?>">
                                    <select name="durum" class="status-select" onchange="this.form.submit()">
                                        <option value="Beklemede" <?= $row['durum'] == 'Beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                                        <option value="Hazırlanıyor" <?= $row['durum'] == 'Hazırlanıyor' ? 'selected' : ''; ?>>Hazırlanıyor</option>
                                        <option value="Kargoda" <?= $row['durum'] == 'Kargoda' ? 'selected' : ''; ?>>Kargoda</option>
                                        <option value="Tamamlandı" <?= $row['durum'] == 'Tamamlandı' ? 'selected' : ''; ?>>Tamamlandı</option>
                                        <option value="İptal Edildi" <?= $row['durum'] == 'İptal Edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="siparis-detay.php?id=<?= $row['siparis_id']; ?>" class="view-btn">
                                        <i class="fas fa-eye"></i> Detay
                                    </a>
                                    <form method="POST" action="siparisler.php" style="display:inline;">
                                        <input type="hidden" name="sil_id" value="<?= $row['siparis_id']; ?>">
                                        <button type="submit" class="delete-btn">
                                            <i class="fas fa-trash"></i> Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-orders">
                            <i class="fas fa-shopping-cart fa-2x" style="color: #ddd; margin-bottom: 15px;"></i><br>
                            Henüz sipariş bulunmamaktadır.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
</div>

<script>
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>

</body>
</html>