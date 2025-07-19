<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - SONKEZBURADA</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="icon/css/all.min.css">
</head>
<body style="display: flex;">
    <header>
        <h1>SONKEZBURADA</h1>
        <nav>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="urunler.php">Ürünler</a></li>
                <li><a href="sepet.php">Sepet</a></li>
                <?php if (isset($_SESSION['email'])): ?>
                    <li><a href="cikis.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <li><a href="giris.php">Giriş Yap</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2 class="auth-title">Giriş Yap</h2>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <div class="form-group">
                    <?php
                    header('Content-Type: text/html; charset=utf-8');
                    session_start();

                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $ad = $_POST["ad"];
                        $sifre = $_POST["sifre"];

                        $baglanti = mysqli_connect("localhost", "root", "", "eticaret");
                        mysqli_set_charset($baglanti, "utf8");

                        $sorgu = mysqli_query($baglanti, "SELECT * FROM kullanicilar WHERE ad='$ad' AND sifre='$sifre'");
                        $dizi = mysqli_fetch_array($sorgu);
                        $adet = mysqli_num_rows($sorgu);

                        if ($adet > 0) {
                            if ($dizi['onay'] == 1) {
                                $_SESSION['kullanici_id'] = $dizi['id'];
                                $_SESSION['ad'] = $dizi['ad'];
                                $_SESSION['soyad'] = $dizi['soyad'];
                                $_SESSION['email'] = $dizi['email'];
                                $_SESSION['sifre'] = $dizi['sifre'];
                                $_SESSION['admin'] = $dizi['admin'];

                                echo "<h4 style='margin-top: 15px; padding: 10px; text-align: center; color: rgb(23, 225, 70); font-size: 25px;'>Giriş Başarılı</h4>";

                                if ($_SESSION['admin'] == 1) {
                                    header("refresh:3;url='admin/index.php'");
                                } else {
                                    header("refresh:3;url='index.php'");
                                }
                            } else {
                                echo "<h4 style='margin-top: 15px; padding: 10px; text-align: center; color: rgb(234, 10, 10); font-size: 25px;'>Yönetici Onayı Gerekli.</h4>";
                                header("refresh:3;url='giris.php'");
                            }
                        } else {
                            echo "<h4 style='margin-top: 15px; padding: 10px; text-align: center; color: rgb(234, 10, 10); font-size: 25px;'>Ad veya şifre hatalı. Lütfen tekrar deneyin.</h4>";
                            header("refresh:3;url='giris.php'");
                        }
                    }
                    ?>
                    <label for="ad">Adınız</label>
                    <input type="text" id="ad" name="ad" required>
                </div>
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="sifre" required>
                </div>
                <button type="submit" class="auth-btn">Giriş Yap</button>
                <div class="auth-links">
                    <a href="uyeol.php">Üye Ol</a>
                </div>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 SONKEZBURADA. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>