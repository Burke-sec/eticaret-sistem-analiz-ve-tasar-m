
DROP TABLE IF EXISTS `kullanicilar`;
CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad` varchar(30) COLLATE utf8_turkish_ci NOT NULL,
  `soyad` varchar(30) COLLATE utf8_turkish_ci NOT NULL,
  `sifre` varchar(50) COLLATE utf8_turkish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `onay` tinyint(1) NOT NULL DEFAULT '0',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

#
# Data for table "kullanicilar"
#

INSERT INTO `kullanicilar` VALUES (1,'admin','admin','1234','admin@gmail.com',1,1),(3,'Burak','Bozkır','burak','burak@gmail.com',1,0),(4,'Can','Kaplan','can','can@gmail.com',-1,0),(7,'Ömer','Senar','omer','omer@gmail.com',0,0);

#
# Structure for table "sepet"
#

DROP TABLE IF EXISTS `sepet`;
CREATE TABLE `sepet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kullanici_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kullanici_id` (`kullanici_id`),
  CONSTRAINT `sepet_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

#
# Data for table "sepet"
#


#
# Structure for table "sepet_urunleri"
#

DROP TABLE IF EXISTS `sepet_urunleri`;
CREATE TABLE `sepet_urunleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sepet_id` int(11) NOT NULL,
  `urun_id` int(11) NOT NULL,
  `adet` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

#
# Data for table "sepet_urunleri"
#


#
# Structure for table "siparisler"
#

DROP TABLE IF EXISTS `siparisler`;
CREATE TABLE `siparisler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `siparis_tarihi` date DEFAULT NULL,
  `durum` varchar(20) COLLATE utf8_turkish_ci DEFAULT 'Beklemede',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `siparisler_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `kullanicilar` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

#
# Data for table "siparisler"
#


#
# Structure for table "urunler"
#

DROP TABLE IF EXISTS `urunler`;
CREATE TABLE `urunler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `urun_ad` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `urun_aciklama` varchar(255) COLLATE utf8_turkish_ci NOT NULL DEFAULT '',
  `fiyat` decimal(10,2) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT '0',
  `kategori` varchar(30) COLLATE utf8_turkish_ci NOT NULL DEFAULT '',
  `resim` varchar(30) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

#
# Data for table "urunler"
#

INSERT INTO `urunler` VALUES (7,'Kot Pantolon','Beyaz-Mavi Kot Pantolon',900.00,36,'giyim','kiyafet1.png'),(8,'Deri Ayakkabı','Kahverenginde gerçek deri ayakakabı',2000.00,75,'giyim','kiyafet2.png'),(9,'Mavi Gömlek','100% Pamuk Mavi Gömlek',700.00,55,'giyim','kiyafet3.png'),(17,'İphone 16','Yeni Çıkan İphone 16 Mor',100000.00,21,'elektronik','iphone16.png'),(18,'Samsung S24+','Samsung S24+ Modeli Gri Renk',60000.00,47,'elektronik','s24.png'),(19,'MacBook Air M4','MacBook Air M4 İşlemcili',48999.00,49,'elektronik','macbook.jpg'),(20,'Hp Victus 16','HP Victus 16 Oyuncu/Ofis Bilgisayarı',36999.00,68,'elektronik','hpvictus.jpg'),(21,'Psikanaliz Üzerine','Pskianaliz Üzerine Düşünceler - Sigmund Freud',200.00,5,'kitap','freud.jpeg'),(23,'Devlet','Devlet - Platon',50.00,75,'kitap','devlet.jpeg'),(25,'Küçük Prens','Küçük Prens - Antoine de Saint-Exupéry',35.00,22,'kitap','kucukprens-1.jpg'),(26,'Simyacı','Simyacı - Paulo Coelho',150.00,39,'kitap','simyaci.jpg'),(27,'Iphone 13','Iphone 13 Yıldız Işığı renk',35999.00,128,'elektronik','iphone13.png');

#
# Structure for table "siparis_detay"
#

DROP TABLE IF EXISTS `siparis_detay`;
CREATE TABLE `siparis_detay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siparis_id` int(11) NOT NULL,
  `urun_id` int(11) NOT NULL,
  `adet` int(11) NOT NULL,
  `fiyat` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `siparis_id` (`siparis_id`),
  KEY `urun_id` (`urun_id`),
  CONSTRAINT `siparis_detay_ibfk_1` FOREIGN KEY (`siparis_id`) REFERENCES `siparisler` (`id`),
  CONSTRAINT `siparis_detay_ibfk_2` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

#
# Data for table "siparis_detay"
#

