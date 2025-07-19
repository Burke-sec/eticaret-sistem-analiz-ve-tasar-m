<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Listesi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        
        h1 {
            color: #333;
            text-align: center;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .report-table th, .report-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .report-table th {
            background-color: #3A7BD5;
            color: white;
        }
        
        .report-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .report-table tr:hover {
            background-color: #ddd;
        }
        
        .price-value {
            color: green;
            font-weight: bold;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

<h1>Ürün Listesi</h1>

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
        header("Content-Type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=Urun_rapor.doc"); 
        header('Content-Type: application/x-msword; charset=UTF-8; format=attachment;');
        $baglanti = mysqli_connect("localhost", "root", "", "eticaret");
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

</body>
</html>
