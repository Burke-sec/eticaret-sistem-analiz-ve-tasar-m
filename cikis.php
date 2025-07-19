<?php
session_start();

$girisYapmis = isset($_SESSION['kullanici_id']);

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çıkış Yapıldı - SONKEZBURADA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .logout-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .logout-icon {
            font-size: 50px;
            color: #3A7BD5;
            margin-bottom: 20px;
        }
        .logout-message {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3A7BD5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: #2d62aa;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <h1>SONKEZBURADA</h1>
        <nav>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="urunler.php">Ürünler</a></li>
                <li><a href="sepet.php">Sepet</a></li>
                <li><a href="giris.php">Giriş Yap</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="logout-container">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h2>Çıkış Yapıldı</h2>
            <p class="logout-message">
                <?php 
                if ($girisYapmis) {
                    echo "Başarıyla çıkış yaptınız. Tekrar görüşmek üzere!";
                } else {
                    echo "Zaten çıkış yapmış durumdasınız.";
                }
                ?>
            </p>
            <a href="giris.php" class="btn">Tekrar Giriş Yap</a>
            <a href="index.php" class="btn" style="background-color: #28a745; margin-left: 10px;">Ana Sayfaya Dön</a>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sonkezburada. Tüm hakları saklıdır.</p>
    </footer>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
</html>