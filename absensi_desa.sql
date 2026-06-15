-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: absensi_desa
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `absensi`
--

DROP TABLE IF EXISTS `absensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Alpha','SPPD','cuti') NOT NULL DEFAULT 'Hadir',
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `absensi_user_id_foreign` (`user_id`),
  CONSTRAINT `absensi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi`
--

LOCK TABLES `absensi` WRITE;
/*!40000 ALTER TABLE `absensi` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laporan`
--

DROP TABLE IF EXISTS `laporan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `total_hadir` int(11) NOT NULL DEFAULT 0,
  `total_izin` int(11) NOT NULL DEFAULT 0,
  `total_alpha` int(11) NOT NULL DEFAULT 0,
  `total_cuti` int(11) NOT NULL DEFAULT 0,
  `persentase_kehadiran` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laporan_user_id_foreign` (`user_id`),
  CONSTRAINT `laporan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laporan`
--

LOCK TABLES `laporan` WRITE;
/*!40000 ALTER TABLE `laporan` DISABLE KEYS */;
/*!40000 ALTER TABLE `laporan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2026_06_02_122124_create_users_table',1),(3,'2026_06_02_122235_create_sessions_table',1),(4,'2026_06_02_143152_create_absensi_table',1),(5,'2026_06_02_143242_create_sppd_table',1),(6,'2026_06_02_143300_create_laporan_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expired_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sessions_token_unique` (`token`),
  KEY `sessions_user_id_foreign` (`user_id`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (3,2,'bSdBMhe1FuF5PeA5Xsw61sEpU900HZVcfczhIJPh1P6QgCwHTU6VC6BPrwdgo9cu','2026-06-06 15:08:37','2026-06-06 07:08:38','2026-06-06 07:08:38'),(10,3,'JLUvvFKPxYKEbtG9uq5c2oPueo4DrVNBxnkIx0MRaK8p0TAXPnqtjgZfJga5MWQQ','2026-06-08 08:45:09','2026-06-08 00:45:09','2026-06-08 00:45:09'),(11,1,'nWE3vJJ9UZtg4800EykZ3fEyp9pCM0B3WKbcoAOpFkJOmFEDyzO9lP9RmxfgOEjl','2026-06-08 08:47:49','2026-06-08 00:47:49','2026-06-08 00:47:49');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sppd`
--

DROP TABLE IF EXISTS `sppd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sppd` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `nomor_sppd` varchar(50) NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `keperluan` text NOT NULL,
  `tanggal_berangkat` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `status` enum('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sppd_nomor_sppd_unique` (`nomor_sppd`),
  KEY `sppd_user_id_foreign` (`user_id`),
  CONSTRAINT `sppd_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sppd`
--

LOCK TABLES `sppd` WRITE;
/*!40000 ALTER TABLE `sppd` DISABLE KEYS */;
/*!40000 ALTER TABLE `sppd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `role` enum('admin','operator','user') NOT NULL DEFAULT 'user',
  `aktif` tinyint(4) NOT NULL DEFAULT 1,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_nik_unique` (`nik`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'3201076807790004','Hj. Nasih','$2y$12$zjoPKLcJ0N4KxzX2lTYISeX7O3DDElS7J9q1RmigwfT2ZMgGiKKSe','Kepala Desa','admin',1,NULL,'2026-06-06 06:38:05','2026-06-06 06:38:05'),(2,'3201071111920014','Dede Firdaus','$2y$12$hIZvInDUobSFlmX3jiJMO.dOTWmIV60WlsbO/.uUCojuPMtLlXbHG','Sekretaris Desa','operator',1,NULL,'2026-06-06 06:38:05','2026-06-06 06:38:05'),(3,'3201076102010004','Annisa Sahida','$2y$12$dWxJFdDwAv2ju3mDhnQVDu1AWIEXYP1JiOl0J4/tXpjjUz5P7pV/y','Kasi Pemerintahan','user',1,NULL,'2026-06-06 06:38:05','2026-06-06 06:38:05'),(4,'3201076801960001','Lika Lestanti','$2y$12$1QBe36k.LeM1PoNslo7JEeWr64dfBNFp7NRHWUM5arZxPlPbb220a','Kasi Pelayanan','user',1,NULL,'2026-06-06 06:38:06','2026-06-06 06:38:06'),(5,'3201071801810001','Ican','$2y$12$T8fWsZcwGyaNWShFX11zl.8VGSfojDtg7gxqw8LRVixh7.OrdX1OO','Kasi Kesra','user',1,NULL,'2026-06-06 06:38:06','2026-06-06 06:38:06'),(6,'3201074105980006','Selpi Melati Sukma','$2y$12$BB5yS2P8urwkmqs6uEHj7OdPn1CzCgsQNfe2vAfmxadKsvEaUxNqC','Kaur Keuangan','user',1,NULL,'2026-06-06 06:38:06','2026-06-06 06:38:06'),(7,'3201071012030002','Muhamad Wahyu Firmansyah','$2y$12$jISZl2midI/ataWyZURJbOLbzpY0wX/iVZ2CBKhhpkkIjbmsVYwqW','Kaur Perencanaan','user',1,NULL,'2026-06-06 06:38:07','2026-06-06 06:38:07'),(8,'3201071610820005','Ugim Mulyana','$2y$12$fd8lmC/7HwZa0y1N2.kGH.y6Bkf1B1kBw9DnTznFfUfud8I7vxj32','Kaur Tata Usaha & Umum','user',1,NULL,'2026-06-06 06:38:07','2026-06-06 06:38:07'),(9,'3201070705750024','Iman Sudarmaji','$2y$12$JHOk1aJ1gy.SBQl5p5STcu3RQQFjmy0ejj.hwdlj5tI.L/E.FC8om','Kepala Dusun I','user',1,NULL,'2026-06-06 06:38:07','2026-06-06 06:38:07'),(10,'3201072402970012','Soepiyan Mulya Ilham Febriana','$2y$12$Ai9iCX7BYooq6xtSqhTqqOEsdjU2MgPrpKJP3vjCxhgzuJ9B/2qGC','Kepala Dusun II','user',1,NULL,'2026-06-06 06:38:08','2026-06-06 06:38:08'),(11,'3201071904820006','Agus Pitroh','$2y$12$353X.729rrS379EKhvbBn.Qm5RNVfpEdNVqg1blHMzYbkRMknrOVi','Kepala Dusun III','user',1,NULL,'2026-06-06 06:38:08','2026-06-06 06:38:08'),(12,'3201071212660010','Boing','$2y$12$yeD0PXyOreABQvRTRXlJHebiF0iJO3EjNDlB5roi1Zk.5o4zZfuOK','Kepala Dusun IV','user',1,NULL,'2026-06-06 06:38:08','2026-06-06 06:38:08'),(13,'3201070706780013','Anim Bin Kirun','$2y$12$IUYQLXpL7cnSFCkA/3F.wuFPlbKccVEA64H8hDRvt6YCzQWr961JO','-','user',1,NULL,'2026-06-06 06:38:09','2026-06-06 06:38:09'),(14,'3201061703730010','Dedi','$2y$12$h3Gr4QHQ9H33uqVY8FcdhO95fD3VxIfDw0HP/4R2KNuAr.l3htbOS','-','user',1,NULL,'2026-06-06 06:38:09','2026-06-06 06:38:09'),(15,'3201070802600002','Idja','$2y$12$eZrUauM0Zd5yjHiHGCSEJ.O3dp0cycCKxGxX5.FrlTmw/IrUAJ2Pi','-','user',1,NULL,'2026-06-06 06:38:09','2026-06-06 06:38:09'),(16,'3201075712710005','Imawati Utaminingsih','$2y$12$Hx9gLL107HzqIE18lJ6Q2eAvw/XLF8kXnEwmjaniyKZyFWeQSWqm6','-','user',1,NULL,'2026-06-06 06:38:10','2026-06-06 06:38:10'),(17,'3201071512000007','M. Syahro Romadhon','$2y$12$adwQEqxuwkJpYjVbe960HeeiFp669woE.Ll6jGDAZCQUF/R9OPfsK','-','user',1,NULL,'2026-06-06 06:38:10','2026-06-06 06:38:10'),(18,'3201071402700006','Matroji','$2y$12$l1E6piJQYfK0hF9.kmqMh.ZbnBLacECawBTWRHyUxtj/P5d2xbdju','-','user',1,NULL,'2026-06-06 06:38:10','2026-06-06 06:38:10'),(19,'3201071203720023','Sabin','$2y$12$nD5EVP/gETqQyoyM3dSaTOc/CP0811RrweJ2Zk9vrmhgcqe2JH5R2','-','user',1,NULL,'2026-06-06 06:38:11','2026-06-06 06:38:11'),(20,'3201070205730007','Saenan Permana','$2y$12$GLiobyczdDGhcirKHO/TBOjAdBnCazK4CzjbJb3xDZVB7C4s8WbYO','-','user',1,NULL,'2026-06-06 06:38:11','2026-06-06 06:38:11'),(21,'3201070508710008','Wawan Kurniawan','$2y$12$TdhLTv1.niPnqZvBwxMKHux7hi5b8IFdWFSfLImhWRY0GpmB0PLFG','-','user',1,NULL,'2026-06-06 06:38:11','2026-06-06 06:38:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-08 16:28:06
