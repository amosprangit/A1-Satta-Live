-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: a1satta_admin
-- ------------------------------------------------------
-- Server version	8.0.33

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
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (2,'admin','$2y$10$sAA1pfj9OkbFxP/CR636wuLQvUT7kgHlBiUOZ3v48DvGNiRVrOtwa','admin@test.com','2026-06-05 13:28:02');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_data`
--

DROP TABLE IF EXISTS `chart_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_name` varchar(100) NOT NULL,
  `result_date` date NOT NULL,
  `result_number` varchar(10) NOT NULL,
  `chart_table` varchar(50) DEFAULT 'main_chart',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entry` (`game_name`,`result_date`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_data`
--

LOCK TABLES `chart_data` WRITE;
/*!40000 ALTER TABLE `chart_data` DISABLE KEYS */;
INSERT INTO `chart_data` VALUES (1,'sadar bazar','2026-06-01','98','main_chart'),(2,'sadar bazar','2026-06-02','53','main_chart'),(3,'sadar bazar','2026-06-03','16','main_chart'),(4,'gwalior','2026-06-01','84','main_chart'),(5,'gwalior','2026-06-02','10','main_chart'),(6,'gwalior','2026-06-03','49','main_chart'),(7,'delhi bazar','2026-06-01','69','main_chart'),(8,'delhi bazar','2026-06-02','54','main_chart'),(9,'delhi bazar','2026-06-03','62','main_chart'),(10,'delhi matka','2026-06-01','27','main_chart'),(11,'delhi matka','2026-06-02','23','main_chart'),(12,'delhi matka','2026-06-03','72','main_chart'),(13,'shri ganesh','2026-06-01','22','main_chart'),(14,'shri ganesh','2026-06-02','03','main_chart'),(15,'shri ganesh','2026-06-03','88','main_chart'),(16,'agra','2026-06-01','6','main_chart'),(17,'agra','2026-06-02','4','main_chart'),(18,'agra','2026-06-03','7','main_chart'),(19,'faridabad','2026-06-01','03','main_chart'),(20,'faridabad','2026-06-02','51','main_chart'),(21,'faridabad','2026-06-03','65','main_chart'),(22,'alwar','2026-06-01','92','main_chart'),(23,'alwar','2026-06-02','25','main_chart'),(24,'alwar','2026-06-03','57','main_chart'),(25,'gaziabad','2026-06-01','52','main_chart'),(26,'gaziabad','2026-06-02','29','main_chart'),(27,'dwarka','2026-06-01','43','main_chart'),(28,'dwarka','2026-06-02','56','main_chart'),(29,'gali','2026-06-01','53','main_chart'),(30,'gali','2026-06-02','50','main_chart'),(31,'disawer','2026-06-01','-','main_chart'),(32,'disawer','2026-06-02','11','main_chart'),(33,'disawer','2026-06-03','48','main_chart'),(34,'hr satta','2026-06-01','83','main_chart'),(35,'hr satta','2026-06-02','30','main_chart'),(36,'hr satta','2026-06-03','01','main_chart'),(37,'ujjala super','2026-06-01','20','main_chart'),(38,'ujjala super','2026-06-02','76','main_chart'),(39,'ujjala super','2026-06-03','31','main_chart'),(40,'kkr city','2026-06-01','47','main_chart'),(41,'kkr city','2026-06-02','74','main_chart'),(42,'kkr city','2026-06-03','91','main_chart'),(43,'madhupuri','2026-06-01','72','main_chart'),(44,'madhupuri','2026-06-02','07','main_chart'),(45,'madhupuri','2026-06-03','30','main_chart'),(46,'karol bagh','2026-06-01','99','main_chart'),(47,'karol bagh','2026-06-02','72','main_chart'),(48,'karol bagh','2026-06-03','49','main_chart'),(49,'delhi darbar','2026-06-01','26','main_chart'),(50,'delhi darbar','2026-06-02','72','main_chart'),(51,'delhi darbar','2026-06-03','38','main_chart'),(52,'new ganga','2026-06-01','21','main_chart'),(53,'new ganga','2026-06-02','69','main_chart'),(54,'new ganga','2026-06-03','60','main_chart'),(55,'fatehabad','2026-06-01','30','main_chart'),(56,'fatehabad','2026-06-02','01','main_chart'),(57,'fatehabad','2026-06-03','29','main_chart'),(58,'raj shree','2026-06-01','61','main_chart'),(59,'raj shree','2026-06-02','59','main_chart'),(60,'raj shree','2026-06-03','38','main_chart'),(61,'mandi bazar','2026-06-01','12','main_chart'),(62,'mandi bazar','2026-06-02','28','main_chart'),(63,'mandi bazar','2026-06-03','87','main_chart'),(64,'dehradun city','2026-06-01','42','main_chart'),(65,'dehradun city','2026-06-02','38','main_chart'),(66,'daman','2026-06-01','76','main_chart'),(67,'daman','2026-06-02','10','main_chart');
/*!40000 ALTER TABLE `chart_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_results`
--

DROP TABLE IF EXISTS `game_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_name` varchar(100) NOT NULL,
  `today_result` varchar(10) DEFAULT NULL,
  `yesterday_result` varchar(10) DEFAULT NULL,
  `result_time` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_results`
--

LOCK TABLES `game_results` WRITE;
/*!40000 ALTER TABLE `game_results` DISABLE KEYS */;
INSERT INTO `game_results` VALUES (1,'sadar bazar','60','400','1:40 PM','active'),(2,'gwalior','49','10','2:40 PM','active'),(3,'delhi bazar','62','54','3:15 PM','active'),(4,'shri ganesh','88','03','4:40 PM','active'),(5,'faridabad','65','51','6:10 PM','active'),(6,'gaziabad','--','29','9:50 PM','active'),(7,'gali','--','50','11:50 PM','active'),(8,'disawer','48','11','5:15 AM','active');
/*!40000 ALTER TABLE `game_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `content` text,
  `status` enum('Y','N') DEFAULT 'Y',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_content`
--

DROP TABLE IF EXISTS `website_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `website_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content_key` varchar(100) NOT NULL,
  `content_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_key` (`content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_content`
--

LOCK TABLES `website_content` WRITE;
/*!40000 ALTER TABLE `website_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `website_content` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-13  9:29:00
