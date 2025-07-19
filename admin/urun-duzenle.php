<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Düzenleme - Sonkezburada</title>
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
                <h2>Ürün Düzenleme</h2>
            </div>

            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Ürün ara..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                        Ara
                    </button>
                </form>
            </div>

            <div class="product-list">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $db = mysqli_connect('localhost', 'root', '', 'eticaret');
                        if ($db->connect_error) {
                            die("Bağlantı hatası: " . $db->connect_error);
                        }
                        mysqli_set_charset($db, "utf8");
                        $limit = 3;
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($page - 1) * $limit;

                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                        $searchCondition = '';
                        if (!empty($search)) {
                            $searchCondition = " WHERE urun_ad LIKE '%" . $db->real_escape_string($search) . "%'";
                        }

                        $totalQuery = "SELECT COUNT(*) as total FROM urunler" . $searchCondition;
                        $totalResult = $db->query($totalQuery);
                        $totalRow = $totalResult->fetch_assoc();
                        $totalProducts = $totalRow['total'];
                        $totalPages = ceil($totalProducts / $limit);

                        $query = "SELECT * FROM urunler" . $searchCondition . " LIMIT $limit OFFSET $offset";
                        $result = $db->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><img src='../img/" . htmlspecialchars($row['resim']) . "' alt='Ürün' class='product-image'></td>";
                                echo "<td>" . htmlspecialchars($row['urun_ad']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
                                echo "<td>₺" . number_format($row['fiyat'], 2, ',', '.') . "</td>";
                                echo "<td>" . htmlspecialchars($row['stok']) . "</td>";
                                echo "<td><a href='urun-guncelle.php?id=" . $row['id'] . "' class='action-btn edit-btn'>Düzenle</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Ürün bulunamadı.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ürün Düzenle</h3>
                <span class="close-btn">&times;</span>
            </div>
            <form class="product-form" method="POST" action="urun-guncelle.php">
                <input type="hidden" id="productId" name="productId">
                <div class="form-group">
                    <label for="productName">Ürün Adı</label>
                    <input type="text" id="productName" name="productName" required>
                </div>
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="elektronik">Elektronik</option>
                        <option value="giyim">Giyim</option>
                        <option value="kitap">Kitap</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Fiyat (₺)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                <div class="form-group">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Değişiklikleri Kaydet</button>
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