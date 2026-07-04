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
  `game_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chart_date` date DEFAULT NULL,
  `result` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=276 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_data`
--

LOCK TABLES `chart_data` WRITE;
/*!40000 ALTER TABLE `chart_data` DISABLE KEYS */;
INSERT INTO `chart_data` VALUES (1,'gwalior','2026-06-01','62'),(2,'gwalior','2026-06-02','37'),(3,'gwalior','2026-06-03','37'),(4,'gwalior','2026-06-04','56'),(5,'gwalior','2026-06-05','50'),(6,'gwalior','2026-06-06','50'),(7,'gwalior','2026-06-07','89'),(8,'gwalior','2026-06-08','37'),(9,'gwalior','2026-06-09','68'),(10,'gwalior','2026-06-10','50'),(11,'gwalior','2026-06-11','62'),(12,'gwalior','2026-06-12','37'),(13,'gwalior','2026-06-13','15'),(14,'gwalior','2026-06-14','29'),(15,'gwalior','2026-06-15','15'),(16,'gwalior','2026-06-16','83'),(17,'gwalior','2026-06-17','41'),(18,'gwalior','2026-06-18','48'),(19,'gwalior','2026-06-19','86'),(20,'gwalior','2026-06-20','34'),(21,'gwalior','2026-06-21','74'),(22,'gwalior','2026-06-22','56'),(23,'gwalior','2026-06-23','83'),(24,'gwalior','2026-06-24','78'),(25,'gwalior','2026-06-25','29'),(26,'gwalior','2026-06-26','89'),(27,'gwalior','2026-06-27','92'),(28,'gwalior','2026-06-28','35'),(29,'gwalior','2026-06-29','95'),(30,'gwalior','2026-06-30','23'),(31,'shri_ganesh','2026-06-01','78'),(32,'shri_ganesh','2026-06-02','29'),(33,'shri_ganesh','2026-06-03','71'),(34,'shri_ganesh','2026-06-04','37'),(35,'shri_ganesh','2026-06-05','12'),(36,'shri_ganesh','2026-06-06','78'),(37,'shri_ganesh','2026-06-07','83'),(38,'shri_ganesh','2026-06-08','83'),(39,'shri_ganesh','2026-06-09','23'),(40,'shri_ganesh','2026-06-10','50'),(41,'shri_ganesh','2026-06-11','68'),(42,'shri_ganesh','2026-06-12','71'),(43,'shri_ganesh','2026-06-13','43'),(44,'shri_ganesh','2026-06-14','59'),(45,'shri_ganesh','2026-06-15','78'),(46,'shri_ganesh','2026-06-16','43'),(47,'shri_ganesh','2026-06-17','71'),(48,'shri_ganesh','2026-06-18','89'),(49,'shri_ganesh','2026-06-19','90'),(50,'shri_ganesh','2026-06-20','90'),(51,'shri_ganesh','2026-06-21','41'),(52,'shri_ganesh','2026-06-22','86'),(53,'shri_ganesh','2026-06-23','23'),(54,'shri_ganesh','2026-06-24','12'),(55,'shri_ganesh','2026-06-25','41'),(56,'shri_ganesh','2026-06-26','18'),(57,'shri_ganesh','2026-06-27','35'),(58,'shri_ganesh','2026-06-28','56'),(59,'shri_ganesh','2026-06-29','78'),(60,'shri_ganesh','2026-06-30','86'),(61,'sadar_bazar','2026-06-01','92'),(62,'sadar_bazar','2026-06-02','92'),(63,'sadar_bazar','2026-06-03','53'),(64,'sadar_bazar','2026-06-04','50'),(65,'sadar_bazar','2026-06-05','45'),(66,'sadar_bazar','2026-06-06','89'),(67,'sadar_bazar','2026-06-07','10'),(68,'sadar_bazar','2026-06-08','59'),(69,'sadar_bazar','2026-06-09','78'),(70,'sadar_bazar','2026-06-10','95'),(71,'sadar_bazar','2026-06-11','18'),(72,'sadar_bazar','2026-06-12','53'),(73,'sadar_bazar','2026-06-13','53'),(74,'sadar_bazar','2026-06-14','59'),(75,'sadar_bazar','2026-06-15','78'),(76,'sadar_bazar','2026-06-16','68'),(77,'sadar_bazar','2026-06-17','71'),(78,'sadar_bazar','2026-06-18','76'),(79,'sadar_bazar','2026-06-19','92'),(80,'sadar_bazar','2026-06-20','67'),(81,'sadar_bazar','2026-06-21','37'),(82,'sadar_bazar','2026-06-22','34'),(83,'sadar_bazar','2026-06-23','26'),(84,'sadar_bazar','2026-06-24','29'),(85,'sadar_bazar','2026-06-25','45'),(86,'sadar_bazar','2026-06-26','78'),(87,'sadar_bazar','2026-06-27','45'),(88,'sadar_bazar','2026-06-28','53'),(89,'sadar_bazar','2026-06-29','32'),(90,'sadar_bazar','2026-06-30','67'),(121,'disawar','2026-06-01','18'),(122,'disawar','2026-06-02','53'),(123,'disawar','2026-06-03','48'),(124,'disawar','2026-06-04','92'),(125,'disawar','2026-06-05','68'),(126,'disawar','2026-06-06','23'),(127,'disawar','2026-06-07','95'),(128,'disawar','2026-06-08','62'),(129,'disawar','2026-06-09','10'),(130,'disawar','2026-06-10','89'),(131,'disawar','2026-06-11','89'),(132,'disawar','2026-06-12','41'),(133,'disawar','2026-06-13','35'),(134,'disawar','2026-06-14','15'),(135,'disawar','2026-06-15','43'),(136,'disawar','2026-06-16','62'),(137,'disawar','2026-06-17','37'),(138,'disawar','2026-06-18','10'),(139,'disawar','2026-06-19','41'),(140,'disawar','2026-06-20','50'),(141,'disawar','2026-06-21','67'),(142,'disawar','2026-06-22','37'),(143,'disawar','2026-06-23','35'),(144,'disawar','2026-06-24','37'),(145,'disawar','2026-06-25','10'),(146,'disawar','2026-06-26','76'),(147,'disawar','2026-06-27','43'),(148,'disawar','2026-06-28','83'),(149,'disawar','2026-06-29','34'),(150,'disawar','2026-06-30','53'),(151,'faridabad','2026-06-01','71'),(152,'faridabad','2026-06-02','71'),(153,'faridabad','2026-06-03','56'),(154,'faridabad','2026-06-04','29'),(155,'faridabad','2026-06-05','43'),(156,'faridabad','2026-06-06','34'),(157,'faridabad','2026-06-07','74'),(158,'faridabad','2026-06-08','26'),(159,'faridabad','2026-06-09','89'),(160,'faridabad','2026-06-10','90'),(161,'faridabad','2026-06-11','18'),(162,'faridabad','2026-06-12','23'),(163,'faridabad','2026-06-13','62'),(164,'faridabad','2026-06-14','78'),(165,'faridabad','2026-06-15','71'),(166,'faridabad','2026-06-16','10'),(167,'faridabad','2026-06-17','83'),(168,'faridabad','2026-06-18','71'),(169,'faridabad','2026-06-19','29'),(170,'faridabad','2026-06-20','18'),(171,'faridabad','2026-06-21','86'),(172,'faridabad','2026-06-22','86'),(173,'faridabad','2026-06-23','48'),(174,'faridabad','2026-06-24','90'),(175,'faridabad','2026-06-25','90'),(176,'faridabad','2026-06-26','48'),(177,'faridabad','2026-06-27','68'),(178,'faridabad','2026-06-28','56'),(179,'faridabad','2026-06-29','67'),(180,'faridabad','2026-06-30','41'),(181,'gali','2026-06-01','59'),(182,'gali','2026-06-02','62'),(183,'gali','2026-06-03','78'),(184,'gali','2026-06-04','83'),(185,'gali','2026-06-05','15'),(186,'gali','2026-06-06','48'),(187,'gali','2026-06-07','78'),(188,'gali','2026-06-08','53'),(189,'gali','2026-06-09','48'),(190,'gali','2026-06-10','34'),(191,'gali','2026-06-11','78'),(192,'gali','2026-06-12','68'),(193,'gali','2026-06-13','26'),(194,'gali','2026-06-14','89'),(195,'gali','2026-06-15','12'),(196,'gali','2026-06-16','90'),(197,'gali','2026-06-17','74'),(198,'gali','2026-06-18','67'),(199,'gali','2026-06-19','68'),(200,'gali','2026-06-20','34'),(201,'gali','2026-06-21','74'),(202,'gali','2026-06-22','95'),(203,'gali','2026-06-23','68'),(204,'gali','2026-06-24','34'),(205,'gali','2026-06-25','15'),(206,'gali','2026-06-26','95'),(207,'gali','2026-06-27','34'),(208,'gali','2026-06-28','12'),(209,'gali','2026-06-29','78'),(210,'gali','2026-06-30','68'),(211,'gaziabad','2026-06-01','43'),(212,'gaziabad','2026-06-02','86'),(213,'gaziabad','2026-06-03','23'),(214,'gaziabad','2026-06-04','48'),(215,'gaziabad','2026-06-05','15'),(216,'gaziabad','2026-06-06','18'),(217,'gaziabad','2026-06-07','90'),(218,'gaziabad','2026-06-08','48'),(219,'gaziabad','2026-06-09','15'),(220,'gaziabad','2026-06-10','45'),(221,'gaziabad','2026-06-11','29'),(222,'gaziabad','2026-06-12','68'),(223,'gaziabad','2026-06-13','95'),(224,'gaziabad','2026-06-14','89'),(225,'gaziabad','2026-06-15','23'),(226,'gaziabad','2026-06-16','89'),(227,'gaziabad','2026-06-17','59'),(228,'gaziabad','2026-06-18','78'),(229,'gaziabad','2026-06-19','50'),(230,'gaziabad','2026-06-20','41'),(231,'gaziabad','2026-06-21','89'),(232,'gaziabad','2026-06-22','41'),(233,'gaziabad','2026-06-23','86'),(234,'gaziabad','2026-06-24','67'),(235,'gaziabad','2026-06-25','23'),(236,'gaziabad','2026-06-26','95'),(237,'gaziabad','2026-06-27','71'),(238,'gaziabad','2026-06-28','41'),(239,'gaziabad','2026-06-29','42'),(240,'gaziabad','2026-06-30','42'),(241,'gwalior','2026-07-03','44'),(242,'gali','2026-07-03','58'),(243,'pushkar','2026-07-03','54'),(244,'disawar','2026-07-03','32'),(245,'sadar bazar','2026-07-03','55'),(246,'sadar bazar','2026-06-01','50'),(247,'sadar bazar','2026-06-02','43'),(248,'sadar bazar','2026-06-03','86'),(249,'sadar bazar','2026-06-04','62'),(250,'sadar bazar','2026-06-05','67'),(251,'sadar bazar','2026-06-06','86'),(252,'sadar bazar','2026-06-07','48'),(253,'sadar bazar','2026-06-08','67'),(254,'sadar bazar','2026-06-09','50'),(255,'sadar bazar','2026-06-10','18'),(256,'sadar bazar','2026-06-11','56'),(257,'sadar bazar','2026-06-12','76'),(258,'sadar bazar','2026-06-13','29'),(259,'sadar bazar','2026-06-14','10'),(260,'sadar bazar','2026-06-15','68'),(261,'sadar bazar','2026-06-16','26'),(262,'sadar bazar','2026-06-17','41'),(263,'sadar bazar','2026-06-18','29'),(264,'sadar bazar','2026-06-19','76'),(265,'sadar bazar','2026-06-20','45'),(266,'sadar bazar','2026-06-21','95'),(267,'sadar bazar','2026-06-22','45'),(268,'sadar bazar','2026-06-23','26'),(269,'sadar bazar','2026-06-24','53'),(270,'sadar bazar','2026-06-25','53'),(271,'sadar bazar','2026-06-26','68'),(272,'sadar bazar','2026-06-27','29'),(273,'sadar bazar','2026-06-28','59'),(274,'sadar bazar','2026-06-29','45'),(275,'sadar bazar','2026-06-30','68');
/*!40000 ALTER TABLE `chart_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_table_games`
--

DROP TABLE IF EXISTS `custom_table_games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_table_games` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_id` int NOT NULL,
  `game_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `today_result` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'WAIT',
  `yesterday_result` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '--',
  `result_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '--',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_table_id` (`table_id`),
  CONSTRAINT `custom_table_games_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `custom_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_table_games`
--

LOCK TABLES `custom_table_games` WRITE;
/*!40000 ALTER TABLE `custom_table_games` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_table_games` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_tables`
--

DROP TABLE IF EXISTS `custom_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_tables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_tables`
--

LOCK TABLES `custom_tables` WRITE;
/*!40000 ALTER TABLE `custom_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_multiple_results`
--

DROP TABLE IF EXISTS `game_multiple_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_multiple_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_name` varchar(100) NOT NULL,
  `result_date` date NOT NULL,
  `result_number` varchar(20) NOT NULL,
  `result_time` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_game_date` (`game_name`,`result_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_multiple_results`
--

LOCK TABLES `game_multiple_results` WRITE;
/*!40000 ALTER TABLE `game_multiple_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_multiple_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_rates`
--

DROP TABLE IF EXISTS `game_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rate_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_rates`
--

LOCK TABLES `game_rates` WRITE;
/*!40000 ALTER TABLE `game_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_results`
--

DROP TABLE IF EXISTS `game_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `yesterday_result` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '--',
  `today_result` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'WAIT',
  `result_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '--',
  `table_type` enum('table1','table2') COLLATE utf8mb4_unicode_ci DEFAULT 'table1',
  `status` tinyint(1) DEFAULT '1',
  `is_latest` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_name` (`game_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_results`
--

LOCK TABLES `game_results` WRITE;
/*!40000 ALTER TABLE `game_results` DISABLE KEYS */;
INSERT INTO `game_results` VALUES (1,'pushkar','PUSHKAR','22','54','12:30 PM','--','table1',1,0),(2,'sadar bazar','SADAR BAZAR','32','55','1:40 PM','--','table1',1,0),(3,'gwalior','GWALIOR','44','WAIT','2:40 PM','--','table1',1,1),(5,'shri ganesh','SHRI GANESH','--','WAIT','4:45 PM','--','table1',1,0),(6,'gaziabad','GAZIABAD','--','WAIT','9:00 PM','--','table2',1,0),(7,'gali','GALI','58','WAIT','11:20 PM','--','table2',1,0),(8,'disawar','DISAWAR','--','32','5:15 AM','--','table2',1,0),(9,'faridabad','FARIDABAD','--','WAIT','5:50 PM','--','table2',1,0),(12,'delhi bazar','DELHI BAZAR','--','WAIT','3:00 PM','--','table1',1,0),(13,'aligarh','ALIGARH','--','WAIT','5:35 PM','--','table1',1,0),(14,'vrindavan','VRINDAVAN','--','WAIT','7:40 PM','--','table1',1,0),(15,'uttarkashi','UTTARKASHI','--','WAIT','10:45 PM','--','table1',1,0);
/*!40000 ALTER TABLE `game_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_timings`
--

DROP TABLE IF EXISTS `game_timings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_timings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timing` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emoji` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_timings`
--

LOCK TABLES `game_timings` WRITE;
/*!40000 ALTER TABLE `game_timings` DISABLE KEYS */;
INSERT INTO `game_timings` VALUES (1,'पुष्कर','12:30 PM','\"',0,1),(2,'सदर बाजार','1:25 PM','\"',0,1),(3,'ग्वालियर','2:25 PM','\"',0,1),(4,'दिल्ली बाज़ार','3:00 PM','\"',0,1),(5,'श्री  गणेश','4:20 PM','\"',0,1),(6,'अलीगढ़','5:25 PM','\"',0,1),(7,'फरीदाबाद','5:30 PM','\"',0,1),(8,'वृन्दावन','7:15 PM','\"',0,1),(9,'गाज़ियाबाद','9:00 PM','\"',0,1),(10,'उत्तरकाशी','10:30 PM','\"',0,1),(11,'गली','11:20 PM','\"',0,1),(12,'दिसावर','1:30 AM','\"',0,1);
/*!40000 ALTER TABLE `game_timings` ENABLE KEYS */;
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
  `content_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_key` (`content_key`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_content`
--

LOCK TABLES `website_content` WRITE;
/*!40000 ALTER TABLE `website_content` DISABLE KEYS */;
INSERT INTO `website_content` VALUES (1,'khaiwal_line1','? Online khaiwal ?','2026-07-07 08:56:53'),(2,'khaiwal_line2','*( Raj Bhai Khaiwal )*','2026-07-03 05:55:11'),(3,'whatsapp_number','919812287328','2026-06-24 09:26:48'),(4,'whatsapp_text','WhatsApp','2026-06-24 09:26:48'),(5,'whatsapp_subtext','Click to Chat','2026-06-24 09:26:48'),(8,'top_whatsapp_number','919812287328','2026-06-29 13:44:50'),(9,'top_whatsapp_text','\"NOW WHATSAPP PLAYERS CAN ALSO JOIN OUR WHATSAPP CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS.\"','2026-06-24 09:40:45'),(10,'top_whatsapp_btn','Click to chat','2026-06-24 09:40:45');
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

-- Dump completed on 2026-07-08 20:11:24
