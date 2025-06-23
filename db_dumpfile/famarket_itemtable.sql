-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: famarket
-- ------------------------------------------------------
-- Server version	8.4.5

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `itemtable`
--

DROP TABLE IF EXISTS `itemtable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itemtable` (
  `itemid` int DEFAULT NULL,
  `itemname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `itemprice` int DEFAULT '0',
  `itemstock` int DEFAULT '0',
  `itemtag` text COLLATE utf8mb4_unicode_ci,
  `image_url` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itemtable`
--

LOCK TABLES `itemtable` WRITE;
/*!40000 ALTER TABLE `itemtable` DISABLE KEYS */;
INSERT INTO `itemtable` VALUES (2,'Caja roja',10000,96,NULL,'https://i.ibb.co/35H4K5rV/caja.png'),(4,'Creamy Chocobar',3000,97,NULL,'https://i.ibb.co/0VzWMNLr/creamychocobar.png'),(9,'Goreabab',3500,71,NULL,'https://i.ibb.co/23kYTyKR/goraebob.png'),(3,'Mc Chocolaty chips cookies',5000,86,NULL,'https://i.ibb.co/m5X0KSkH/chocochip.png'),(10,'Milka Ceralis',5000,95,NULL,'https://i.ibb.co/0y3Q1yn0/milkacereals.png'),(5,'Milka White Chocolate',4500,98,NULL,'https://i.ibb.co/HT2Pq6nv/milkawhite.png'),(6,'Nesquik',8000,82,NULL,'https://i.ibb.co/N6wnB6Ys/nesquik.png'),(8,'Nova choco bar',6800,91,NULL,'https://i.ibb.co/fV7DFwkS/novachocobar.png'),(7,'Oreo',5000,98,NULL,'https://i.ibb.co/5XkGRxz8/oreo.png'),(1,'Pocky',1900,91,NULL,'https://i.ibb.co/YFqgSHsG/pocky.png');
/*!40000 ALTER TABLE `itemtable` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-23 14:39:32
