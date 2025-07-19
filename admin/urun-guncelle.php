<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Güncelleme - Sonkezburada</title>
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
                <li><a href="#" class="nav-link">Admin Çıkış</a></li>
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
            <a href="urun-duzenle.php" class="menu-item active">
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
                <h2>Ürün Güncelleme</h2>
            </div>

            <?php
            $db = mysqli_connect('localhost', 'root', '', 'eticaret');
            if ($db->connect_error) {
                die("Bağlantı hatası: " . $db->connect_error);
            }
            mysqli_set_charset($db, "utf8");

            $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $productName = $db->real_escape_string($_POST['productName']);
                $category = $db->real_escape_string($_POST['category']);
                $price = (float)$_POST['price'];
                $stock = (int)$_POST['stock'];
                $description = $db->real_escape_string($_POST['description']);

                $updateQuery = "UPDATE urunler SET 
                                urun_ad = '$productName', 
                                kategori = '$category', 
                                fiyat = $price, 
                                stok = $stock, 
                                urun_aciklama = '$description' 
                                WHERE id = $productId";

                if ($db->query($updateQuery)) {
                    echo '<div class="success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            Ürün başarıyla güncellendi.
                          </div>';
                } else {
                    echo '<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            Ürün güncellenirken hata oluştu: ' . $db->error . '
                          </div>';
                }
            }

            $query = "SELECT * FROM urunler WHERE id = $productId";
            $result = $db->query($query);
            $product = $result->fetch_assoc();

            if (!$product) {
                echo '<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        Ürün bulunamadı.
                      </div>';
            } else {
            ?>
            <form class="product-form" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="productName">Ürün Adı</label>
                        <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($product['urun_ad']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <option value="elektronik" <?php echo $product['kategori'] == 'elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                            <option value="giyim" <?php echo $product['kategori'] == 'giyim' ? 'selected' : ''; ?>>Giyim</option>
                            <option value="kitap" <?php echo $product['kategori'] == 'kitap' ? 'selected' : ''; ?>>Kitap</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Fiyat (₺)</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($product['fiyat']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stok</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($product['stok']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($product['urun_aciklama']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Mevcut Görsel</label>
                    <div class="image-preview">
                        <img src="../img/<?php echo htmlspecialchars($product['resim']); ?>" alt="Ürün Görseli" style="max-width: 100%; max-height: 100%;">
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Değişiklikleri Kaydet</button>
                <a href="urun-duzenle.php" class="submit-btn" style="background: #6c757d; margin-top: 10px; text-align: center; display: block;">
                    Listeye Dön
                </a>
            </form>
            <?php
            }
            $db->close();
            ?>
        </div>
    </div>

    <footer>
        <div>
            <p>&copy; 2025 Sonkezburada Admin Paneli. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>