<?php
        session_start();
        session_destroy();
        header("refresh:3;url='../index.php'");
        
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Sonkezburada</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../icon/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body style="display: flex;">
    <header>
        <h1>Sonkezburada</h1>
        <nav>
            <ul>
                <li><a href="index.php" class="nav-link">Ana Sayfa</a></li>
                <li><a href="urunler.php" class="nav-link">Ürünler</a></li>
                <li><a href="sepet.php" class="nav-link">Sepet</a></li>
                <li><a href="#" class="nav-link">Giriş</a></li>
            </ul>
        </nav>
    </header>
    <h2>ÇIKIŞ YAPILIYOR</h2>

    <h2>YÖNLENDİRİLİYORSUNUZ</h2>
</body>
</html>