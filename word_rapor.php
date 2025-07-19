<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

$db = mysqli_connect('localhost', 'root', '', 'eticaret');
if ($db->connect_error) {
    die("Veritabanı bağlantı hatası: " . $db->connect_error);
}
mysqli_set_charset($db, "utf8");

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit();
}

$kullanici_id = intval($_SESSION['kullanici_id']);
$siparis_id = isset($_GET['siparis_id']) ? intval($_GET['siparis_id']) : 0;

$siparis_query = $db->query("
    SELECT s.*, u.ad, u.soyad, u.email 
    FROM siparisler s
    JOIN kullanicilar u ON s.user_id = u.id
    WHERE s.id = $siparis_id AND s.user_id = $kullanici_id
");

if ($siparis_query->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$siparis = $siparis_query->fetch_assoc();

$detay_query = $db->query("
    SELECT sd.*, u.urun_ad, u.fiyat 
    FROM siparis_detay sd
    JOIN urunler u ON sd.urun_id = u.id
    WHERE sd.siparis_id = $siparis_id
");

$detaylar = $detay_query->fetch_all(MYSQLI_ASSOC);

$toplam = 0;
foreach ($detaylar as $urun) {
    $toplam += $urun['fiyat'] * $urun['adet'];
}
$kargo = $toplam > 200 ? 0 : 29.90;
$genel_toplam = $toplam + $kargo;

header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=siparis_$siparis_id.doc");

echo "<html>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
echo "<body>";
echo "<h1 style='text-align:center;'>SİPARİŞ BİLGİLERİ</h1>";
echo "<h2 style='text-align:center;'>Sipariş No: #$siparis_id</h2>";
echo "<hr>";

echo "<h3>Müşteri Bilgileri</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0' width='100%'>";
$ad_soyad = htmlspecialchars($siparis['ad'] . ' ' . $siparis['soyad']);
echo "<tr><td width='30%'><strong>Ad Soyad:</strong></td><td>$ad_soyad</td></tr>";
echo "<tr><td><strong>E-posta:</strong></td><td>" . htmlspecialchars($siparis['email']) . "</td></tr>";
echo "<tr><td><strong>Sipariş Tarihi:</strong></td><td>" . date('d.m.Y H:i') . "</td></tr>";
echo "</table>";

echo "<h3>Sipariş Detayları</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0' width='100%'>";
echo "<tr>";
echo "<th width='50%'>Ürün Adı</th>";
echo "<th width='15%'>Adet</th>";
echo "<th width='15%'>Birim Fiyat</th>";
echo "<th width='20%'>Toplam</th>";
echo "</tr>";

foreach ($detaylar as $urun) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($urun['urun_ad']) . "</td>";
    echo "<td>" . $urun['adet'] . "</td>";
    echo "<td>₺" . number_format($urun['fiyat'], 2, ',', '.') . "</td>";
    echo "<td>₺" . number_format($urun['fiyat'] * $urun['adet'], 2, ',', '.') . "</td>";
    echo "</tr>";
}

echo "<tr>";
echo "<td colspan='3' align='right'><strong>Ara Toplam:</strong></td>";
echo "<td>₺" . number_format($toplam, 2, ',', '.') . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='3' align='right'><strong>Kargo:</strong></td>";
echo "<td>" . ($kargo == 0 ? 'Ücretsiz' : '₺' . number_format($kargo, 2, ',', '.')) . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='3' align='right'><strong>Genel Toplam:</strong></td>";
echo "<td>₺" . number_format($genel_toplam, 2, ',', '.') . "</td>";
echo "</tr>";
echo "</table>";

echo "<hr>";
echo "<p style='text-align:center;'>Teşekkür ederiz. Siparişiniz alınmıştır.</p>";
echo "</body>";
echo "</html>";
exit();
?>