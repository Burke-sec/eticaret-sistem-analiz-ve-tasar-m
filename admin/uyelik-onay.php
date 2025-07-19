<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION['email']) || $_SESSION['email'] == "") {
    header("Location: error.php");
    exit();
}
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == 0) {
    header("Location: error.php");
    exit();
}

$baglanti = mysqli_connect("localhost","root","","eticaret");
if (!$baglanti) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}

mysqli_set_charset($baglanti, "utf8");
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = intval($_POST['user_id']);
    $status = intval($_POST['status']);
    
    $update_query = "UPDATE kullanicilar SET onay = ? WHERE id = ?";
    $stmt = mysqli_prepare($baglanti, $update_query);
    mysqli_stmt_bind_param($stmt, "ii", $status, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Kullanıcı durumu güncellendi'); window.location.href='uyelik-onay.php';</script>";
    } else {
        echo "<script>alert('Güncelleme hatası: " . mysqli_error($baglanti) . "');</script>";
    }
    mysqli_stmt_close($stmt);
    exit();
}

$where_clause = "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['ad']) || isset($_GET['soyad']))) {
    $ad = mysqli_real_escape_string($baglanti, trim($_GET['ad']));
    $soyad = mysqli_real_escape_string($baglanti, trim($_GET['soyad']));
    
    if (!empty($ad) && !empty($soyad)) {
        $where_clause = " WHERE ad LIKE '%$ad%' AND soyad LIKE '%$soyad%'";
    } elseif (!empty($ad)) {
        $where_clause = " WHERE ad LIKE '%$ad%'";
    } elseif (!empty($soyad)) {
        $where_clause = " WHERE soyad LIKE '%$soyad%'";
    }
}

$sorgu_kullanici = mysqli_query($baglanti, "SELECT * FROM kullanicilar $where_clause ORDER BY id DESC");
if (!$sorgu_kullanici) {
    die("Sorgu hatası: " . mysqli_error($baglanti));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyelik Onaylama - Sonkezburada</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
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
            <a href="uyelik-onay.php" class="menu-item active">
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
                <h2>Üyelik Onaylama</h2>
            </div>


            <div class="search-box">
                <form method="get" action="">
                    <input type="text" name="ad" placeholder="Ad" value="<?php echo isset($_GET['ad']) ? htmlspecialchars($_GET['ad']) : ''; ?>">
                    <input type="text" name="soyad" placeholder="Soyad" value="<?php echo isset($_GET['soyad']) ? htmlspecialchars($_GET['soyad']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                        Ara
                    </button>
                    <a href="uyelik-onay.php" class="reset-btn">
                        <i class="fas fa-undo"></i>
                        Sıfırla
                    </a>
                </form>
            </div>

            <div class="user-list">
                <table>
                    <thead>
                        <tr>
                            <th>Üye ID</th>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($kullanici = mysqli_fetch_assoc($sorgu_kullanici)) {
                            $status_badge = '';
                            $action_buttons = '';
                            
                            switch($kullanici['onay']) {
                                case 1:
                                    $status_badge = '<span class="status-badge status-approved">Onaylandı</span>';
                                    break;
                                case -1:
                                    $status_badge = '<span class="status-badge status-rejected">Reddedildi</span>';
                                    break;
                                default:
                                    $status_badge = '<span class="status-badge status-pending">Beklemede</span>';
                            }
                            
                            switch($kullanici['onay']) {
                                case 1:
                                    $action_buttons = '
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="'.$kullanici['id'].'">
                                            <input type="hidden" name="status" value="0">
                                            <button type="submit" class="reject-btn" onclick="return confirm(\'Kullanıcı onayını iptal etmek istediğinize emin misiniz?\')">İptal Et</button>
                                        </form>';
                                    break;
                                case -1:
                                    $action_buttons = '
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="'.$kullanici['id'].'">
                                            <input type="hidden" name="status" value="1">
                                            <button type="submit" class="approve-btn" onclick="return confirm(\'Kullanıcıyı onaylamak istediğinize emin misiniz?\')">Onayla</button>
                                        </form>';
                                    break;
                                default:
                                    $action_buttons = '
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="'.$kullanici['id'].'">
                                            <input type="hidden" name="status" value="1">
                                            <button type="submit" class="approve-btn" onclick="return confirm(\'Kullanıcıyı onaylamak istediğinize emin misiniz?\')">Onayla</button>
                                        </form>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="'.$kullanici['id'].'">
                                            <input type="hidden" name="status" value="-1">
                                            <button type="submit" class="reject-btn" onclick="return confirm(\'Kullanıcıyı reddetmek istediğinize emin misiniz?\')">Reddet</button>
                                        </form>';
                            }
                            
                            echo '
                            <tr>
                                <td>#'.$kullanici['id'].'</td>
                                <td>'.$kullanici['ad'].' '.$kullanici['soyad'].'</td>
                                <td>'.$kullanici['email'].'</td>
                                <td>'.$status_badge.'</td>
                                <td class="user-actions">'.$action_buttons.'</td>
                            </tr>';
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
<?php
mysqli_close($baglanti);
?>