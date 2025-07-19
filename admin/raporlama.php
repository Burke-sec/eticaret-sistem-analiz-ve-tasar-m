<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Sonkezburada</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .report-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .report-container h3 {
            padding: 15px 20px;
            background: #f8f9fa;
            color: #3A7BD5;
            font-size: 1.3rem;
            margin: 0;
            border-bottom: 1px solid #eee;
        }
        
        .report-container a {
            padding: 15px 20px;
            background: #f8f9fa;
            color:rgb(255, 0, 0);
            font-size: 1.6rem;
            margin: 0;
            border-bottom: 1px solid #eee;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        
        .report-table th {
            background: linear-gradient(0deg, #3A7BD5, #36D1DC);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .report-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .report-table tr:hover {
            background-color: #f1f7ff;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background: #ffeeba;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-user {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .status-admin {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .price-value {
            font-weight: bold;
            color: #3A7BD5;
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
        
    <div class="admin-content">
        <div class="admin-header">
            <h2>Raporlama</h2>
        </div>
        <div class="admin-body">
            <div class="report-container">
                <h3><i class="fas fa-box"></i> Ürün Raporu <a href="urun_rapor.php">Çıktı Almak İçin TIKLAYINIZ</a></h3>
                
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Ürün ID</th>
                            <th>Ürün Adı</th>
                            <th>Açıklama</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $baglanti=mysqli_connect("localhost","root","","eticaret");
                        mysqli_set_charset($baglanti, "utf8");
                        $sorgu_urun = mysqli_query($baglanti, "SELECT * FROM urunler");
                        
                        while ($row = mysqli_fetch_assoc($sorgu_urun)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td><strong>" . $row['urun_ad'] . "</strong></td>";
                            echo "<td>" . substr($row['urun_aciklama'], 0, 50) . (strlen($row['urun_aciklama']) > 50 ? '...' : '') . "</td>";
                            echo "<td class='price-value'>" . number_format($row['fiyat'], 2) . " ₺</td>";
                            echo "<td>" . $row['stok'] . "</td>";
                            echo "<td>" . $row['kategori'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="report-container">
                <h3><i class="fas fa-users"></i> Üyelik Raporu<a href="uye_rapor.php">Çıktı Almak İçin TIKLAYINIZ</a></h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Kullanıcı ID</th>
                            <th>Ad</th>
                            <th>Soyad</th>
                            <th>Email</th>
                            <th>Onay Durumu</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sorgu_uyelik = mysqli_query($baglanti, "SELECT * FROM kullanicilar");
                        
                        while ($row = mysqli_fetch_assoc($sorgu_uyelik)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td><strong>" . $row['ad'] . "</strong></td>";
                            echo "<td>" . $row['soyad'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td><span class='status-badge " . 
                                ($row['onay'] == 0 ? 'status-pending' : ($row['onay'] == 1 ? 'status-approved' : 'status-rejected')) . 
                                "'>" . 
                                ($row['onay'] == 0 ? "Onay Bekliyor" : ($row['onay'] == 1 ? "Onaylı" : "Reddedilmiş")) . 
                                "</span></td>";
                            echo "<td><span class='status-badge " . ($row['admin'] == 0 ? 'status-user' : 'status-admin') . "'>" . ($row['admin'] == 0 ? "Kullanıcı" : "Yetkili") . "</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>