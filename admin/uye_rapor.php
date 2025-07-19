<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Listesi</title>
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

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            color: white;
        }

        .status-pending {
            background-color: orange;
        }

        .status-approved {
            background-color: green;
        }

        .status-rejected {
            background-color: red;
        }

        .status-user {
            background-color: blue;
        }

        .status-admin {
            background-color: purple;
        }
    </style>
</head>
<body>

<h1>Kullanıcı Listesi</h1>

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
        header("Content-Type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=Uye_rapor.doc"); 
        header('Content-Type: application/x-msword; charset=UTF-8; format=attachment;');
        $baglanti = mysqli_connect("localhost", "root", "", "eticaret");
        mysqli_set_charset($baglanti, "utf8");

        $sorgu_uyelik = mysqli_query($baglanti, "SELECT * FROM kullanicilar");

        while ($row = mysqli_fetch_assoc($sorgu_uyelik)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><strong>" . $row['ad'] . "</strong></td>";
            echo "<td>" . $row['soyad'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . ($row['onay'] == 0 ? "Onay Bekliyor" : ($row['onay'] == 1 ? "Onaylı" : "Reddedilmiş")) . "</td>";
            echo "<td>" . ($row['admin'] == 0 ? "UYE" : "ADMIN") . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>