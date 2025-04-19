-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: student_portal
-- ------------------------------------------------------
-- Server version	10.4.27-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tbl_class`
--

DROP TABLE IF EXISTS `tbl_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_class` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(50) NOT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `school_year` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`class_id`),
  KEY `grade_id` (`grade_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `tbl_class_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `tbl_grade` (`grade_id`),
  CONSTRAINT `tbl_class_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `tbl_teachers` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_class`
--

LOCK TABLES `tbl_class` WRITE;
/*!40000 ALTER TABLE `tbl_class` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_enrollment`
--

DROP TABLE IF EXISTS `tbl_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_enrollment` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  PRIMARY KEY (`enrollment_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `tbl_enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `tbl_students` (`student_id`),
  CONSTRAINT `tbl_enrollment_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `tbl_class` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_enrollment`
--

LOCK TABLES `tbl_enrollment` WRITE;
/*!40000 ALTER TABLE `tbl_enrollment` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_enrollment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_grade`
--

DROP TABLE IF EXISTS `tbl_grade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_grade` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `grade_level` varchar(20) NOT NULL,
  PRIMARY KEY (`grade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_grade`
--

LOCK TABLES `tbl_grade` WRITE;
/*!40000 ALTER TABLE `tbl_grade` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_grade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_school_info`
--

DROP TABLE IF EXISTS `tbl_school_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_school_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_name` varchar(255) NOT NULL DEFAULT 'BESTLINK COLLEGE OF THE PHILIPPINES',
  `school_id` varchar(50) NOT NULL DEFAULT '404887',
  `school_address` varchar(255) NOT NULL DEFAULT '#1071 Brgy. Kaligayahan, Quirino Hi-way, Novaliches Quezon City',
  `school_division` varchar(255) NOT NULL DEFAULT 'Division of Quezon City',
  `school_region` varchar(255) NOT NULL DEFAULT 'National Capital Region',
  `principal_name` varchar(255) NOT NULL DEFAULT 'Dr. Elena A. Centeno',
  `school_year` varchar(20) NOT NULL DEFAULT '2023-2024',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_school_info`
--

LOCK TABLES `tbl_school_info` WRITE;
/*!40000 ALTER TABLE `tbl_school_info` DISABLE KEYS */;
INSERT INTO `tbl_school_info` VALUES (1,'Bantay National High School','SF9-SHS','National Highway, Bulag, Bantay, Ilocos Sur','Division of Ilocos Sur','I','Mrs. Maryjane V. Medina','2025-2026');
/*!40000 ALTER TABLE `tbl_school_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_students`
--

DROP TABLE IF EXISTS `tbl_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `birthplace` varchar(100) DEFAULT NULL,
  `parent_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_students`
--

LOCK TABLES `tbl_students` WRITE;
/*!40000 ALTER TABLE `tbl_students` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_teachers`
--

DROP TABLE IF EXISTS `tbl_teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_teachers` (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_teachers`
--

LOCK TABLES `tbl_teachers` WRITE;
/*!40000 ALTER TABLE `tbl_teachers` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblannouncement`
--

DROP TABLE IF EXISTS `tblannouncement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblannouncement` (
  `announcementId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` varchar(2555) NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`announcementId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblannouncement`
--

LOCK TABLES `tblannouncement` WRITE;
/*!40000 ALTER TABLE `tblannouncement` DISABLE KEYS */;
INSERT INTO `tblannouncement` VALUES (14,'Walang Pasok','Due to Typhoon','2024-09-08 21:03:19',0),(22,'Periodical Exam','September 4-7','2024-08-26 05:53:34',0),(26,'Semestral Break','December-15-24','2024-08-30 04:09:50',0),(27,'Walang Pasok','August 21','2024-08-30 04:13:30',0),(45,'No Classes','August 26 - National Heroes Day','2024-08-22 00:22:25',0),(75,'Walang Pasok','Due to typhoon \"Pepito\"','2024-11-18 06:33:25',0),(97,'Semestrial Break','467764357_551551694452017_9070101319972836087_n.jpg','2024-12-06 01:47:15',0);
/*!40000 ALTER TABLE `tblannouncement` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=``*/ /*!50003 TRIGGER `before_insert_announcement` BEFORE INSERT ON `tblannouncement` FOR EACH ROW BEGIN
    DECLARE v_random_id INT;

    -- Generate a random two-digit number
    SET v_random_id = FLOOR(10 + (RAND() * 90));

    -- Check if the generated ID already exists
    WHILE EXISTS (SELECT 1 FROM tblannouncement WHERE announcementId = v_random_id) DO
        SET v_random_id = FLOOR(10 + (RAND() * 90));
    END WHILE;

    -- Set the random ID
    SET NEW.announcementId = v_random_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tblattendance`
--

DROP TABLE IF EXISTS `tblattendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblattendance` (
  `attendanceId` int(11) NOT NULL AUTO_INCREMENT,
  `studentId` varchar(10) NOT NULL,
  `attendance` varchar(15) NOT NULL,
  `classDate` date NOT NULL,
  `subjectId` int(11) NOT NULL,
  PRIMARY KEY (`attendanceId`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblattendance`
--

LOCK TABLES `tblattendance` WRITE;
/*!40000 ALTER TABLE `tblattendance` DISABLE KEYS */;
INSERT INTO `tblattendance` VALUES (1,'91-14366','Present','2025-07-29',9),(2,'91-14366','Present','2025-07-29',12),(3,'91-14366','Present','2025-07-29',14),(4,'91-14366','Present','2025-07-29',17),(5,'91-14366','Present','2025-07-29',19),(6,'91-14366','Present','2025-07-29',21),(7,'91-14366','Present','2025-07-30',9),(8,'91-14366','Present','2025-07-30',12),(9,'91-14366','Present','2025-07-30',14),(10,'91-14366','Present','2025-07-30',17),(11,'91-14366','Present','2025-07-30',19),(12,'91-14366','Present','2025-07-30',21),(13,'91-14366','Present','2025-07-31',9),(14,'91-14366','Present','2025-07-31',12),(15,'91-14366','Present','2025-07-31',14),(16,'91-14366','Present','2025-07-31',17),(17,'91-14366','Present','2025-07-31',19),(18,'91-14366','Present','2025-07-31',21),(19,'90-34132','Present','2025-07-30',9),(20,'90-34132','Present','2025-07-30',12),(21,'90-34132','Present','2025-07-30',14),(22,'90-34132','Present','2025-07-30',17),(23,'90-34132','Present','2025-07-30',19),(24,'90-34132','Present','2025-07-30',21),(25,'90-34132','Present','2025-07-31',9),(26,'90-34132','Present','2025-07-31',12),(27,'90-34132','Present','2025-07-31',14),(28,'90-34132','Present','2025-07-31',17),(29,'90-34132','Present','2025-07-31',19),(30,'90-34132','Present','2025-07-31',21),(31,'90-34132','Present','2025-07-29',9),(32,'90-34132','Present','2025-07-30',29),(33,'90-34132','Present','2025-07-29',14),(34,'90-34132','Present','2025-07-29',17),(35,'90-34132','Present','2025-07-29',19),(36,'90-34132','Present','2025-07-29',21),(37,'67-23061','Absent','2025-07-30',9),(38,'67-23061','Absent','2025-07-30',12),(39,'67-23061','Absent','2025-07-30',14),(40,'67-23061','Absent','2025-07-30',17),(41,'67-23061','Absent','2025-07-30',19),(42,'67-23061','Absent','2025-07-30',21),(43,'67-23061','Absent','2025-07-31',9),(44,'67-23061','Absent','2025-07-31',12),(45,'67-23061','Absent','2025-07-31',14),(46,'67-23061','Absent','2025-07-31',17),(47,'67-23061','Absent','2025-07-31',19),(48,'67-23061','Absent','2025-07-31',21),(49,'67-23061','Absent','2025-07-29',9),(50,'67-23061','Absent','2025-07-29',12),(51,'67-23061','Absent','2025-07-29',14),(52,'67-23061','Absent','2025-07-29',17),(53,'67-23061','Absent','2025-07-29',19),(54,'67-23061','Absent','2025-07-29',21),(55,'62-49041','Present','2025-07-30',9),(56,'62-49041','Present','2025-07-30',12),(57,'62-49041','Present','2025-07-30',14),(58,'62-49041','Present','2025-07-30',17),(59,'62-49041','Present','2025-07-30',19),(60,'62-49041','Present','2025-07-30',21),(61,'62-49041','Present','2025-07-31',9),(62,'62-49041','Present','2025-07-31',12),(63,'62-49041','Present','2025-07-31',14),(64,'62-49041','Present','2025-07-31',17),(65,'62-49041','Present','2025-07-31',19),(66,'62-49041','Present','2025-07-31',21),(67,'62-49041','Present','2025-07-29',9),(68,'62-49041','Present','2025-07-29',12),(69,'62-49041','Present','2025-07-29',14),(70,'62-49041','Present','2025-07-29',17),(71,'62-49041','Present','2025-07-29',19),(72,'62-49041','Present','2025-07-29',21),(73,'62-48148','Present','2025-07-29',9),(74,'62-48148','Present','2025-07-29',12),(75,'62-48148','Present','2025-07-29',14),(76,'62-48148','Present','2025-07-29',17),(77,'62-48148','Present','2025-07-29',19),(78,'62-48148','Present','2025-07-29',21),(79,'62-48148','Present','2025-07-30',9),(80,'62-48148','Present','2025-07-30',12),(81,'62-48148','Present','2025-07-30',14),(82,'62-48148','Present','2025-07-30',17),(83,'62-48148','Present','2025-07-30',19),(84,'62-48148','Present','2025-07-30',21),(85,'62-48148','Present','2025-07-31',9),(86,'62-48148','Present','2025-07-31',12),(87,'62-48148','Present','2025-07-31',14),(88,'62-48148','Present','2025-07-31',17),(89,'62-48148','Present','2025-07-31',19),(90,'62-48148','Present','2025-07-31',21),(91,'57-33940','Present','2025-07-30',9),(92,'57-33940','Present','2025-07-30',12),(93,'57-33940','Present','2025-07-30',14),(94,'57-33940','Present','2025-07-30',17),(95,'57-33940','Present','2025-07-30',19),(96,'57-33940','Present','2025-07-30',21),(97,'57-33940','Present','2025-07-31',9),(98,'57-33940','Present','2025-07-31',12),(99,'57-33940','Present','2025-07-31',14),(100,'57-33940','Present','2025-07-31',17),(101,'57-33940','Present','2025-07-31',19),(102,'57-33940','Present','2025-07-31',21),(103,'57-33940','Absent','2025-07-29',9),(104,'57-33940','Absent','2025-07-29',12),(105,'57-33940','Absent','2025-07-29',14),(106,'57-33940','Absent','2025-07-29',17),(107,'57-33940','Absent','2025-07-29',19),(108,'57-33940','Absent','2025-07-29',21),(109,'30-92714','Present','2025-07-30',9),(110,'30-92714','Present','2025-07-30',12),(111,'30-92714','Present','2025-07-30',14),(112,'30-92714','Present','2025-07-30',17),(113,'30-92714','Present','2025-07-30',19),(114,'30-92714','Present','2025-07-30',21),(115,'30-92714','Present','2025-07-31',9),(116,'30-92714','Present','2025-07-31',12),(117,'30-92714','Present','2025-07-31',14),(118,'30-92714','Present','2025-07-31',17),(119,'30-92714','Present','2025-07-31',19),(120,'30-92714','Present','2025-07-31',21),(121,'30-92714','Present','2025-07-29',9),(122,'30-92714','Present','2025-07-29',12),(123,'30-92714','Present','2025-07-29',14),(124,'30-92714','Present','2025-07-29',17),(125,'30-92714','Present','2025-07-29',19),(126,'30-92714','Present','2025-07-29',21),(127,'24-05440','Present','2025-07-30',9),(128,'24-05440','Present','2025-07-30',12),(129,'24-05440','Present','2025-07-30',14),(130,'24-05440','Present','2025-07-30',17),(131,'24-05440','Present','2025-07-30',19),(132,'24-05440','Present','2025-07-30',21),(133,'24-05440','Present','2025-07-31',9),(134,'24-05440','Present','2025-07-31',12),(135,'24-05440','Present','2025-07-31',14),(136,'24-05440','Present','2025-07-31',17),(137,'24-05440','Present','2025-07-31',19),(138,'24-05440','Present','2025-07-31',21),(139,'24-05440','Present','2025-07-29',9),(140,'24-05440','Present','2025-07-29',12),(141,'24-05440','Present','2025-07-29',14),(142,'24-05440','Present','2025-07-29',17),(143,'24-05440','Present','2025-07-29',19),(144,'24-05440','Present','2025-07-29',21),(145,'23-67760','Present','2025-07-29',9),(146,'23-67760','Present','2025-07-29',12),(147,'23-67760','Present','2025-07-29',14),(148,'23-67760','Present','2025-07-29',17),(149,'23-67760','Present','2025-07-29',19),(150,'23-67760','Present','2025-07-29',21),(151,'23-67760','Present','2025-07-30',9),(152,'23-67760','Present','2025-07-30',12),(153,'23-67760','Present','2025-07-30',14),(154,'23-67760','Present','2025-07-30',17),(155,'23-67760','Present','2025-07-30',19),(156,'23-67760','Present','2025-07-30',21),(157,'23-67760','Present','2025-07-31',9),(158,'23-67760','Present','2025-07-31',12),(159,'23-67760','Present','2025-07-31',14),(160,'23-67760','Present','2025-07-31',17),(161,'23-67760','Present','2025-07-31',19),(162,'23-67760','Present','2025-07-31',21),(163,'21-16689','Present','2025-07-29',9),(164,'21-16689','Present','2025-07-29',12),(165,'21-16689','Present','2025-07-29',14),(166,'21-16689','Present','2025-07-29',17),(167,'21-16689','Present','2025-07-29',19),(168,'21-16689','Present','2025-07-29',21),(169,'21-16689','Present','2025-07-30',9),(170,'21-16689','Present','2025-07-30',12),(171,'21-16689','Present','2025-07-30',14),(172,'21-16689','Present','2025-07-30',17),(173,'21-16689','Present','2025-07-30',19),(174,'21-16689','Present','2025-07-30',21),(175,'21-16689','Present','2025-07-31',9),(176,'21-16689','Present','2025-07-31',12),(177,'21-16689','Present','2025-07-31',14),(178,'21-16689','Present','2025-07-31',17),(179,'21-16689','Present','2025-07-31',19),(180,'21-16689','Present','2025-07-31',21),(181,'91-14366','Absent','2025-08-01',9),(182,'91-14366','Present','2025-08-02',9),(183,'91-14366','Present','2025-08-05',9),(184,'91-14366','Present','2025-08-06',9),(185,'91-14366','Present','2025-08-07',17),(186,'91-14366','Present','2025-08-09',21),(187,'91-14366','Present','2025-08-12',9),(188,'91-14366','Present','2025-08-13',9),(189,'91-14366','Present','2025-08-14',9),(190,'91-14366','Present','2025-08-15',9);
/*!40000 ALTER TABLE `tblattendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblaudit_trail`
--

DROP TABLE IF EXISTS `tblaudit_trail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblaudit_trail` (
  `auditId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`auditId`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblaudit_trail`
--

LOCK TABLES `tblaudit_trail` WRITE;
/*!40000 ALTER TABLE `tblaudit_trail` DISABLE KEYS */;
INSERT INTO `tblaudit_trail` VALUES (1,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-08 07:27:59'),(2,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-08 07:28:01'),(3,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-08 07:28:03'),(4,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-08 07:28:06'),(5,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-08 07:28:08'),(6,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-08 07:28:35'),(7,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-08 07:32:49'),(8,'Mary Jane','Principal','Approved grades for section Socrates - Contemporary Phil Arts from the Regions','2025-03-08 07:33:06'),(9,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-08 07:33:49'),(10,'Admin1','Admin','Viewed grades of section Socrates','2025-03-09 22:23:07'),(11,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-09 22:32:33'),(12,'Admin1','Admin','Viewed grades of section Socrates','2025-03-09 22:45:18'),(13,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-09 22:45:42'),(14,'Maria Kristine Pelayo Paz','Teacher','Input grades','2025-03-10 07:29:26'),(15,'Maria Kristine Pelayo Paz','Teacher','Input grades','2025-03-10 07:29:55'),(16,'Maria Kristine Pelayo Paz','Teacher','Input grades','2025-03-10 07:30:13'),(17,'Maria Kristine Pelayo Paz','Teacher','Input grades','2025-03-10 07:34:47'),(18,'Maria Kristine Pelayo Paz','Teacher','Input grades','2025-03-10 07:36:02'),(19,'Maria Kristine Pelayo Paz','Teacher','Input grades','2025-03-10 07:37:02'),(20,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 07:38:43'),(21,'John Cy Doe','Teacher','Input grades','2025-03-10 07:40:01'),(22,'John Cy Doe','Teacher','Input grades','2025-03-10 07:41:10'),(23,'John Cy Doe','Teacher','Input grades','2025-03-10 07:42:56'),(24,'John Cy Doe','Teacher','Input grades','2025-03-10 07:44:04'),(25,'John Cy Doe','Teacher','Input grades','2025-03-10 07:45:30'),(26,'John Cy Doe','Teacher','Input grades','2025-03-10 07:46:38'),(27,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 07:47:51'),(28,'Ailene Ilar Roldan','Teacher','Input grades','2025-03-10 07:48:50'),(29,'Ailene Ilar Roldan','Teacher','Input grades','2025-03-10 07:59:46'),(30,'Ailene Ilar Roldan','Teacher','Input grades','2025-03-10 08:00:59'),(31,'Ailene Ilar Roldan','Teacher','Input grades','2025-03-10 08:01:58'),(32,'Admin1','Admin','Viewed grades of section Socrates','2025-03-10 08:03:28'),(33,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:03:48'),(34,'Mary Jane','Principal','Approved grades for section Socrates - HOPE III','2025-03-10 08:03:50'),(35,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:03:55'),(36,'Mary Jane','Principal','Approved grades for section Socrates - Media and Information Literacy','2025-03-10 08:03:58'),(37,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:02'),(38,'Mary Jane','Principal','Approved grades for section Socrates - Cooker NCII','2025-03-10 08:04:06'),(39,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:10'),(40,'Mary Jane','Principal','Approved grades for section Socrates - English for Academic &amp; Professional Purposes','2025-03-10 08:04:13'),(41,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:17'),(42,'Mary Jane','Principal','Approved grades for section Socrates - Entrepreneurship','2025-03-10 08:04:21'),(43,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:25'),(44,'Mary Jane','Principal','Approved grades for section Socrates - Practical Research 2','2025-03-10 08:04:29'),(45,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:33'),(46,'Mary Jane','Principal','Approved grades for section Socrates - 21st Century Literature from the Philippines and the World ','2025-03-10 08:04:37'),(47,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:41'),(48,'Mary Jane','Principal','Approved grades for section Socrates - Contemporary Philippine Arts from the Regions','2025-03-10 08:04:45'),(49,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:49'),(50,'Mary Jane','Principal','Approved grades for section Socrates - Understanding Culture, Society and Politics','2025-03-10 08:04:54'),(51,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:04:57'),(52,'Mary Jane','Principal','Approved grades for section Socrates - HOPE IV','2025-03-10 08:05:02'),(53,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:05:06'),(54,'Mary Jane','Principal','Approved grades for section Socrates - Filipino sa Piling Larang','2025-03-10 08:05:14'),(55,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:05:17'),(56,'Mary Jane','Principal','Approved grades for section Socrates - Inquiries, Investigation, Immersion','2025-03-10 08:05:23'),(57,'Mary Jane','Principal','Viewed grades of section Socrates','2025-03-10 08:05:27'),(58,'Mary Jane','Principal','Approved grades for section Socrates - Work Immersion','2025-03-10 08:05:31'),(59,'John Cy Doe','Teacher','Input grades','2025-03-10 08:13:33'),(60,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 07:49:43'),(61,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 07:49:50'),(62,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 07:49:53'),(63,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 07:50:49'),(64,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 07:50:52'),(65,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 07:53:28'),(66,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 07:53:31'),(67,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 07:53:33'),(68,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 07:53:39'),(69,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 07:54:16'),(70,'Emely Pablico Cariaga','Parent','Viewed grades','2025-03-12 07:55:14'),(71,'Emely Pablico Cariaga','Parent','Viewed attendance','2025-03-12 07:55:36'),(72,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 08:51:58'),(73,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 08:52:07'),(74,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 08:52:26'),(75,'Trhishane Nicole Pablico Cariaga','Student','Viewed grades','2025-03-12 08:52:29'),(76,'Admin1','Admin','Viewed grades of section Socrates','2025-03-12 09:11:30'),(77,'John Cy Doe','Teacher','Input grades','2025-03-12 09:30:17'),(78,'John Cy Doe','Teacher','Input grades','2025-03-12 09:30:47'),(79,'John Cy Doe','Teacher','Input grades','2025-03-12 09:31:22'),(80,'Admin1','Admin','Viewed grades of section Socrates','2025-03-12 21:27:24'),(81,'Emely Pablico Cariaga','Parent','Viewed attendance','2025-03-12 21:59:00'),(82,'Emely Pablico Cariaga','Parent','Viewed grades','2025-03-12 22:00:24'),(83,'Emely Pablico Cariaga','Parent','Viewed grades','2025-03-12 22:01:03'),(84,'Emely Pablico Cariaga','Parent','Viewed grades','2025-03-12 22:03:06'),(85,'Emely Pablico Cariaga','Parent','Viewed attendance','2025-03-12 22:04:54'),(86,'Trhishane Nicole Pablico Cariaga','Student','Viewed attendance','2025-03-12 22:17:46'),(87,'Emely Pablico Cariaga','Parent','Viewed grades','2025-03-17 07:53:40'),(88,'Admin1','Admin','Viewed grades of section Socrates','2025-03-19 22:39:44');
/*!40000 ALTER TABLE `tblaudit_trail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblgrades`
--

DROP TABLE IF EXISTS `tblgrades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblgrades` (
  `gradeId` int(11) NOT NULL AUTO_INCREMENT,
  `semester` varchar(20) NOT NULL,
  `syId` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL,
  `userId` varchar(10) NOT NULL,
  `quarter1_grade` int(11) NOT NULL,
  `quarter2_grade` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`gradeId`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblgrades`
--

LOCK TABLES `tblgrades` WRITE;
/*!40000 ALTER TABLE `tblgrades` DISABLE KEYS */;
INSERT INTO `tblgrades` VALUES (1,'1st Semester',900,10,'78-28145',90,91,1),(2,'1st Semester',900,10,'15-17094',90,92,1),(3,'1st Semester',900,10,'20-53249',90,90,1),(4,'1st Semester',900,10,'37-70841',90,91,1),(5,'1st Semester',900,10,'93-86245',90,91,1),(6,'1st Semester',900,10,'31-86444',90,90,1),(7,'1st Semester',900,10,'37-77144',90,91,1),(8,'1st Semester',900,10,'29-61282',90,92,1),(9,'1st Semester',900,10,'19-69318',90,91,1),(10,'1st Semester',900,10,'60-23906',90,92,1),(11,'1st Semester',900,13,'78-28145',89,90,1),(12,'1st Semester',900,13,'15-17094',88,90,1),(13,'1st Semester',900,13,'20-53249',90,90,1),(14,'1st Semester',900,13,'37-70841',89,89,1),(15,'1st Semester',900,13,'93-86245',80,89,1),(16,'1st Semester',900,13,'31-86444',87,88,1),(17,'1st Semester',900,13,'37-77144',86,88,1),(18,'1st Semester',900,13,'29-61282',88,89,1),(19,'1st Semester',900,13,'19-69318',87,88,1),(20,'1st Semester',900,13,'60-23906',90,90,1),(21,'2nd Semester',900,20,'78-28145',90,90,1),(22,'2nd Semester',900,20,'15-17094',90,90,1),(23,'2nd Semester',900,20,'20-53249',90,90,1),(24,'2nd Semester',900,20,'37-70841',92,90,1),(25,'2nd Semester',900,20,'93-86245',90,90,1),(26,'2nd Semester',900,20,'31-86444',91,90,1),(27,'2nd Semester',900,20,'37-77144',89,90,1),(28,'2nd Semester',900,20,'29-61282',93,90,1),(29,'2nd Semester',900,20,'19-69318',90,90,1),(30,'2nd Semester',900,20,'60-23906',94,90,1),(31,'1st Semester',900,9,'78-28145',90,90,1),(32,'1st Semester',900,9,'15-17094',81,88,1),(33,'1st Semester',900,9,'20-53249',88,89,1),(34,'1st Semester',900,9,'37-70841',87,88,1),(35,'1st Semester',900,9,'93-86245',84,86,1),(36,'1st Semester',900,9,'31-86444',90,90,1),(37,'1st Semester',900,9,'37-77144',88,88,1),(38,'1st Semester',900,9,'29-61282',84,89,1),(39,'1st Semester',900,9,'19-69318',88,89,1),(40,'1st Semester',900,9,'60-23906',88,88,1),(41,'1st Semester',900,12,'78-28145',90,91,1),(42,'1st Semester',900,12,'15-17094',90,90,1),(43,'1st Semester',900,12,'20-53249',90,91,1),(44,'1st Semester',900,12,'37-70841',92,90,1),(45,'1st Semester',900,12,'93-86245',88,89,1),(46,'1st Semester',900,12,'31-86444',89,89,1),(47,'1st Semester',900,12,'37-77144',88,88,1),(48,'1st Semester',900,12,'29-61282',82,84,1),(49,'1st Semester',900,12,'19-69318',88,90,1),(50,'1st Semester',900,12,'60-23906',91,92,1),(51,'1st Semester',900,14,'78-28145',85,88,1),(52,'1st Semester',900,14,'15-17094',85,88,1),(53,'1st Semester',900,14,'20-53249',85,88,1),(54,'1st Semester',900,14,'37-70841',81,85,1),(55,'1st Semester',900,14,'93-86245',82,85,1),(56,'1st Semester',900,14,'31-86444',85,88,1),(57,'1st Semester',900,14,'37-77144',82,85,1),(58,'1st Semester',900,14,'29-61282',83,85,1),(59,'1st Semester',900,14,'19-69318',85,88,1),(60,'1st Semester',900,14,'60-23906',85,88,1),(61,'2nd Semester',900,17,'78-28145',90,91,1),(62,'2nd Semester',900,17,'15-17094',90,92,1),(63,'2nd Semester',900,17,'20-53249',90,92,1),(64,'2nd Semester',900,17,'37-70841',90,91,1),(65,'2nd Semester',900,17,'93-86245',90,92,1),(66,'2nd Semester',900,17,'31-86444',90,90,1),(67,'2nd Semester',900,17,'37-77144',90,91,1),(68,'2nd Semester',900,17,'29-61282',90,92,1),(69,'2nd Semester',900,17,'19-69318',90,90,1),(70,'2nd Semester',900,17,'60-23906',90,92,1),(71,'2nd Semester',900,19,'78-28145',90,90,1),(72,'2nd Semester',900,19,'15-17094',90,90,1),(73,'2nd Semester',900,19,'20-53249',89,90,1),(74,'2nd Semester',900,19,'37-70841',88,90,1),(75,'2nd Semester',900,19,'93-86245',89,90,1),(76,'2nd Semester',900,19,'31-86444',88,90,1),(77,'2nd Semester',900,19,'37-77144',89,90,1),(78,'2nd Semester',900,19,'29-61282',78,80,1),(79,'2nd Semester',900,19,'19-69318',88,89,1),(80,'2nd Semester',900,19,'60-23906',89,90,1),(81,'2nd Semester',900,21,'78-28145',90,90,1),(82,'2nd Semester',900,21,'15-17094',89,90,1),(83,'2nd Semester',900,21,'20-53249',90,90,1),(84,'2nd Semester',900,21,'37-70841',90,90,1),(85,'2nd Semester',900,21,'93-86245',88,89,1),(86,'2nd Semester',900,21,'31-86444',89,90,1),(87,'2nd Semester',900,21,'37-77144',90,91,1),(88,'2nd Semester',900,21,'29-61282',89,90,1),(89,'2nd Semester',900,21,'19-69318',88,89,1),(90,'2nd Semester',900,21,'60-23906',90,92,1),(91,'2nd Semester',900,15,'78-28145',90,90,1),(92,'2nd Semester',900,15,'15-17094',81,82,1),(93,'2nd Semester',900,15,'20-53249',88,89,1),(94,'2nd Semester',900,15,'37-70841',89,88,1),(95,'2nd Semester',900,15,'93-86245',90,90,1),(96,'2nd Semester',900,15,'31-86444',90,90,1),(97,'2nd Semester',900,15,'37-77144',81,82,1),(98,'2nd Semester',900,15,'29-61282',82,82,1),(99,'2nd Semester',900,15,'19-69318',85,88,1),(100,'2nd Semester',900,15,'60-23906',89,88,1),(101,'1st Semester',900,11,'78-28145',90,90,1),(102,'1st Semester',900,11,'15-17094',90,90,1),(103,'1st Semester',900,11,'20-53249',90,90,1),(104,'1st Semester',900,11,'37-70841',90,90,1),(105,'1st Semester',900,11,'93-86245',90,90,1),(106,'1st Semester',900,11,'31-86444',90,90,1),(107,'1st Semester',900,11,'37-77144',90,90,1),(108,'1st Semester',900,11,'29-61282',90,90,1),(109,'1st Semester',900,11,'19-69318',90,90,1),(110,'1st Semester',900,11,'60-23906',90,90,1),(111,'2nd Semester',900,16,'78-28145',90,91,1),(112,'2nd Semester',900,16,'15-17094',88,90,1),(113,'2nd Semester',900,16,'20-53249',89,90,1),(114,'2nd Semester',900,16,'37-70841',89,90,1),(115,'2nd Semester',900,16,'93-86245',90,90,1),(116,'2nd Semester',900,16,'31-86444',88,88,1),(117,'2nd Semester',900,16,'37-77144',86,86,1),(118,'2nd Semester',900,16,'29-61282',82,82,1),(119,'2nd Semester',900,16,'19-69318',83,83,1),(120,'2nd Semester',900,16,'60-23906',88,90,1),(121,'2nd Semester',900,18,'78-28145',90,90,1),(122,'2nd Semester',900,18,'15-17094',92,90,1),(123,'2nd Semester',900,18,'20-53249',88,90,1),(124,'2nd Semester',900,18,'37-70841',90,90,1),(125,'2nd Semester',900,18,'93-86245',91,91,1),(126,'2nd Semester',900,18,'31-86444',90,90,1),(127,'2nd Semester',900,18,'37-77144',93,90,1),(128,'2nd Semester',900,18,'29-61282',89,88,1),(129,'2nd Semester',900,18,'19-69318',90,90,1),(130,'2nd Semester',900,18,'60-23906',91,92,1);
/*!40000 ALTER TABLE `tblgrades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblgradingstatus`
--

DROP TABLE IF EXISTS `tblgradingstatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblgradingstatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarter1` tinyint(1) NOT NULL DEFAULT 0,
  `quarter2` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblgradingstatus`
--

LOCK TABLES `tblgradingstatus` WRITE;
/*!40000 ALTER TABLE `tblgradingstatus` DISABLE KEYS */;
INSERT INTO `tblgradingstatus` VALUES (1,1,0,'2025-03-20 05:46:38');
/*!40000 ALTER TABLE `tblgradingstatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblparent_student`
--

DROP TABLE IF EXISTS `tblparent_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblparent_student` (
  `parent_studentId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` varchar(10) NOT NULL,
  `studentId` varchar(10) NOT NULL,
  PRIMARY KEY (`parent_studentId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblparent_student`
--

LOCK TABLES `tblparent_student` WRITE;
/*!40000 ALTER TABLE `tblparent_student` DISABLE KEYS */;
INSERT INTO `tblparent_student` VALUES (1,'12-38600','01-19344'),(2,'12-38600','91-14366'),(3,'66-76644','62-49041'),(4,'63-63503','62-49041'),(5,'27-99424','62-49041'),(6,'91-85239','62-49041'),(7,'63-36679','62-49041'),(8,'69-97373','62-49041'),(9,'88-57172','67-23061'),(10,'92-43527','23-67760'),(11,'87-15986','21-16689'),(12,'89-64719','24-05440'),(13,'67-23323','57-33940'),(14,'89-27066','62-48148'),(15,'40-43966','90-34132'),(16,'89-08740','30-92714');
/*!40000 ALTER TABLE `tblparent_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblparentinfo`
--

DROP TABLE IF EXISTS `tblparentinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblparentinfo` (
  `parentId` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `userId` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `birthDate` date NOT NULL,
  `age` int(3) NOT NULL,
  `sex` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `contactNumber` varchar(13) NOT NULL,
  `email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`parentId`),
  KEY `parentId` (`parentId`),
  KEY `parentId_2` (`parentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblparentinfo`
--

LOCK TABLES `tblparentinfo` WRITE;
/*!40000 ALTER TABLE `tblparentinfo` DISABLE KEYS */;
INSERT INTO `tblparentinfo` VALUES ('12-38600','08-88691','1981-04-06',43,'Female','+6395554335','trhishanec@gmail.com'),('27-99424','36-56970','1981-09-05',43,'Male','+639555433523','trhishanec@gmail.com'),('40-43966','67-66551','1975-09-02',49,'Male','09662813642','bplanta@gmail.com'),('67-23323','86-34518','1980-01-01',44,'Female','09257812631','rquijano01@gmail.com'),('87-15986','50-14169','1978-09-09',46,'Male','09256341782','cAbaoag@gmail.com'),('88-57172','11-60273','1982-05-02',42,'Male','09223156372','soberano@gmail.com'),('89-08740','96-13004','1971-05-29',53,'Female','09223456781','melyV@gmail.com'),('89-27066','23-49685','1979-04-29',45,'Female','09262687390','trhishanec@gmail.com'),('89-64719','19-57782','1987-08-29',37,'Female','09358127354','wendy@gmail.com'),('92-43527','93-06852','1989-02-14',35,'Female','09123461728','amelia@gmail.com');
/*!40000 ALTER TABLE `tblparentinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblschoolyear`
--

DROP TABLE IF EXISTS `tblschoolyear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblschoolyear` (
  `syId` int(11) NOT NULL AUTO_INCREMENT,
  `school_year` varchar(20) NOT NULL,
  `status` enum('Yes','No') NOT NULL,
  PRIMARY KEY (`syId`)
) ENGINE=InnoDB AUTO_INCREMENT=969 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblschoolyear`
--

LOCK TABLES `tblschoolyear` WRITE;
/*!40000 ALTER TABLE `tblschoolyear` DISABLE KEYS */;
INSERT INTO `tblschoolyear` VALUES (533,'2023-2024','No'),(900,'2024-2025','Yes');
/*!40000 ALTER TABLE `tblschoolyear` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblsection`
--

DROP TABLE IF EXISTS `tblsection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblsection` (
  `sectionId` int(10) NOT NULL AUTO_INCREMENT,
  `sectionName` varchar(50) NOT NULL,
  `gradeLevel` int(2) NOT NULL,
  `strand` varchar(50) NOT NULL,
  PRIMARY KEY (`sectionId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblsection`
--

LOCK TABLES `tblsection` WRITE;
/*!40000 ALTER TABLE `tblsection` DISABLE KEYS */;
INSERT INTO `tblsection` VALUES (1,'Rizal',11,'GAS'),(2,'Mabini',11,'TVL'),(3,'Bonifacio',11,'TVL'),(4,'Aristotle',12,'GAS'),(5,'Socrates',12,'TVL'),(6,'Plato',12,'TVL');
/*!40000 ALTER TABLE `tblsection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblstudentinfo`
--

DROP TABLE IF EXISTS `tblstudentinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblstudentinfo` (
  `studentId` varchar(10) NOT NULL,
  `userId` varchar(10) NOT NULL,
  `contactNumber` varchar(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `birthDate` date NOT NULL,
  `age` int(5) NOT NULL,
  `sex` varchar(20) NOT NULL,
  `sectionId` int(10) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `municipality` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `mothersName` varchar(50) NOT NULL,
  `fathersName` varchar(50) NOT NULL,
  `guardian` varchar(50) NOT NULL,
  `parentsNum` varchar(11) NOT NULL,
  `studentsCreatedDate` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `lrn` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`studentId`),
  KEY `studentId` (`studentId`),
  KEY `studentId_2` (`studentId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblstudentinfo`
--

LOCK TABLES `tblstudentinfo` WRITE;
/*!40000 ALTER TABLE `tblstudentinfo` DISABLE KEYS */;
INSERT INTO `tblstudentinfo` VALUES ('01-04158','18-43457','09555433523','trhishanec@gmail.com','2003-02-28',21,'Male',1,'Puspus','Bantay','Ilocos Sur','Emely Alvarez','Rodel Alvarez','Rodel Alvarez','09265143891','2025-03-05 23:30:00.355788','123456789012'),('01-19344','46-68970','09555433523','trhishanec@gmail.com','2003-02-10',21,'Female',1,'Sinabaan','Bantay','Ilocos Sur','Emely','Richard','Richard','09555433523','2025-03-09 06:19:31.783194','100038308099'),('20-25624','42-20482','09275275123','trhishanec@gmail.com','2003-11-29',20,'Female',1,'','','','Clara Pipo','Mario Pipo','Mario Pipo','09275317239','2025-03-10 15:25:52.940157','100052314523'),('21-16689','78-28145','09251788632','charmelA@gmail.com','2002-01-21',22,'Female',5,'Naguidayan','Bantay','Ilocos Sur','Mila Abaoag','Carlo Abaoag','Carlo Abaoag','09772433512','2025-03-10 15:25:35.036960','100042356712'),('23-67760','15-17094','09256782134','frances@gmail.com','2001-07-14',23,'Female',5,'Puspus','Bantay','Ilocos Sur','Amelia Toricer','Rony Toricer','Amelia Toricer','09265143891','2025-03-10 15:25:16.236797','100042315623'),('24-05440','20-53249','09258129371','wvitocruz@gmail.com','2002-11-18',22,'Female',5,'Taleb','Bantay','Ilocos Sur','Wendy Vito Cruz','Ronan Vito Cruz','Wendy Vito Cruz','09275317239','2025-03-10 15:24:56.125241','100864251789'),('29-89002','37-77330','09532561234','trhishanec@gmail.com','2002-04-21',22,'Female',3,'Puspus','Bantay','Ilocos Sur','Celine Sotello','Archie Sotello','Celine Sotello','09167823945','2025-03-10 15:24:38.198918','100042312569'),('30-92714','37-70841','09555433523','trhishanec@gmail.com','2003-10-17',21,'Female',5,'Sinabaan','Bantay','Ilocos Sur','jonna','rodel','jonna','09555433523','2025-03-10 15:24:18.684466','100084231853'),('57-33940','93-86245','09876543123','cjquijano@gmail.com','2003-10-10',21,'Male',5,'Battog','Sinait','Ilocos Sur','Rosemarie Quijano','Joshua Quijano','Rosemarie Quijano','09999123467','2025-03-10 15:23:59.190871','100023156321'),('62-48148','31-86444','09155782562','russelbaldo@gmail.com','2005-10-16',19,'Male',5,'Ora East','Bantay','Ilocos Sur','Reden Baldovino','Rodel Baldovino','Reden Baldovino','09345677284','2025-03-10 15:23:41.961349','100062345198'),('62-49041','37-77144','09876543123','trhishanec@gmail.com','2003-10-12',20,'Male',5,'Nalasin','Sto Domingo','Ilocos Sur','Jonna','Rodel','Rodel','09555433523','2025-03-10 15:23:21.830601','100098231235'),('67-23061','29-61282','09444788273','jrsoberano@gmail.com','2003-04-16',21,'Male',5,'Ora West','Bantay','Ilocos Sur','Amelia Soberano','John Rey Soberano Sr.','John Rey Soberano Sr.','09167823945','2025-03-10 15:23:02.502854','100052134589'),('76-38270','90-30465','09666422134','trhishanec@gmail.com','2004-05-18',20,'Female',1,'Puspus','Bantay','Ilocos Sur','Arceli Pablico','Nick Pablico','Arceli Pablico','09974231872','2025-03-10 15:22:42.978650','100045324781'),('90-34132','19-69318','09256874241','beneplanta@gmail.com','2004-06-21',20,'Male',5,'Puspus','Bantay','Ilocos Sur','Liza Planta','Benedicto Planta Jr.','Benedicto Planta Jr.','09756732145','2025-03-10 15:22:25.085537','100808020011'),('91-14366','60-23906','09555433523','trhishanec@gmail.com','2003-02-10',21,'Female',5,'Puspus','Bantay','Ilocos Sur','Emely','Richard','Richard','09555433523','2025-03-09 06:20:55.473359','100038308009');
/*!40000 ALTER TABLE `tblstudentinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblsubject`
--

DROP TABLE IF EXISTS `tblsubject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblsubject` (
  `subjectId` int(11) NOT NULL AUTO_INCREMENT,
  `subjectName` varchar(255) NOT NULL,
  `semester` varchar(25) NOT NULL,
  `sectionId` int(10) NOT NULL,
  `userId` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `subjectType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`subjectId`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblsubject`
--

LOCK TABLES `tblsubject` WRITE;
/*!40000 ALTER TABLE `tblsubject` DISABLE KEYS */;
INSERT INTO `tblsubject` VALUES (1,'General Mathematics','1st Semester',3,'07-38589','Core'),(2,'Komunikasyon at Pananaliksik sa Wika at Kulturang Filipino','1st Semester',3,'29-28996','Core'),(3,'Introduction to Philosophy of the Human Person','1st Semester',3,'63-75442','Core'),(4,'Oral Communication','1st Semester',3,'68-24798','Core'),(5,'Personal Development','1st Semester',3,'71-54150','Core'),(6,'Earth and Life Science','1st Semester',3,'07-38589','Core'),(7,'HOPE 1','1st Semester',3,'29-28996','Core'),(8,'Food and Beverage Services (NCII)','1st Semester',3,'29-28996','Applied'),(9,'HOPE III','1st Semester',5,'71-54150','Core'),(10,'Media and Information Literacy','1st Semester',5,'07-38589','Core'),(11,'Cooker NCII','1st Semester',5,'29-28996','Applied'),(12,'English for Academic & Professional Purposes','1st Semester',5,'71-54150','Applied'),(13,'Entrepreneurship','1st Semester',5,'07-38589','Applied'),(14,'Practical Research 2','1st Semester',5,'71-54150','Applied'),(15,'21st Century Literature from the Philippines and the World ','2nd Semester',5,'29-28996','Core'),(16,'Contemporary Philippine Arts from the Regions','2nd Semester',5,'29-28996','Core'),(17,'Understanding Culture, Society and Politics','2nd Semester',5,'71-54150','Core'),(18,'HOPE IV','2nd Semester',5,'29-28996','Core'),(19,'Filipino sa Piling Larang','2nd Semester',5,'71-54150','Applied'),(20,'Inquiries, Investigation, Immersion','2nd Semester',5,'07-38589','Applied'),(21,'Work Immersion','2nd Semester',5,'71-54150','Applied');
/*!40000 ALTER TABLE `tblsubject` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblsuperadmin`
--

DROP TABLE IF EXISTS `tblsuperadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblsuperadmin` (
  `superAdminId` varchar(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`superAdminId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblsuperadmin`
--

LOCK TABLES `tblsuperadmin` WRITE;
/*!40000 ALTER TABLE `tblsuperadmin` DISABLE KEYS */;
INSERT INTO `tblsuperadmin` VALUES ('F5693B6ABA','hail','hydra','Admin'),('T5678B6ABB','principal','password','Principal');
/*!40000 ALTER TABLE `tblsuperadmin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblteacherinfo`
--

DROP TABLE IF EXISTS `tblteacherinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblteacherinfo` (
  `teacherId` varchar(10) NOT NULL,
  `userId` varchar(10) NOT NULL,
  `position` varchar(50) NOT NULL,
  `birthDate` date NOT NULL,
  `contactNumber` varchar(11) NOT NULL,
  `age` int(5) NOT NULL,
  `sex` varchar(20) NOT NULL,
  `civilStatus` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `municipality` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `sectionId` int(11) DEFAULT NULL,
  `isAdviser` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`teacherId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblteacherinfo`
--

LOCK TABLES `tblteacherinfo` WRITE;
/*!40000 ALTER TABLE `tblteacherinfo` DISABLE KEYS */;
INSERT INTO `tblteacherinfo` VALUES ('02-27722','13-43237','Instructor','1999-03-29','09876543210',25,'Male','Married','eddeimar@gmail.com','Barangay Caellayan ','Cabugao','Ilocos Sur',NULL,0),('11-22535','68-24798','Teacher III','1995-05-28','09623206014',29,'Female','Single','trhishanec@gmail.com','Bulag West','Bantay','Ilocos Sur',NULL,0),('13-03681','07-38589','Teacher II','1989-10-16','09275275123',35,'Female','Single','trhishanec@gmail.com','Taguiporo','Bantay','Ilocos Sur',1,1),('13-68722','71-54150','Teacher 1','1987-11-16','09876543123',37,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',5,1),('14-12075','37-74077','Instructor','1995-02-23','09876543210',29,'Male','Married','mars@gmail.com','Barangay Dos','San Vicente','Ilocos Sur',NULL,0),('15-68833','71-54150','Teacher 1','1987-11-16','09876543123',37,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',5,1),('24-19312','71-54150','Teacher 1','1987-11-16','09876543123',37,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',5,1),('37-14708','46-71408','Teacher 1','0000-00-00','09555433523',26,'Male','Single','trhishanec@gmail.com','Sinabaan','Bantay','Ilocos Sur',NULL,0),('40-94862','63-75442','Teacher 1','1985-03-25','09876543123',39,'Male','Married','trhishanec@gmail.com','Zone 2','Bantay','Ilocos Sur',NULL,0),('41-74172','71-54150','Teacher 1','1997-02-10','09555433523',28,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',0,0),('58-11589','46-71408','Teacher 1','0000-00-00','09555433523',26,'Male','Single','trhishanec@gmail.com','Sinabaan','Bantay','Ilocos Sur',NULL,0),('66-04005','71-54150','Teacher 1','1987-11-16','09876543123',37,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',5,1),('79-88983','71-54150','Teacher 1','1987-11-16','09876543123',37,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',5,1),('86-35609','29-28996','Teacher II','1995-01-10','09771771826',29,'Female','Married','aileneR@gmail.com','Nalasin','Sto Domingo','Ilocos Sur',NULL,0),('99-29030','71-54150','Teacher 1','1987-11-16','09876543123',37,'Male','Single','trhishanec@gmail.com','Aggay','Bantay','Ilocos Sur',5,1);
/*!40000 ALTER TABLE `tblteacherinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblusersaccount`
--

DROP TABLE IF EXISTS `tblusersaccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblusersaccount` (
  `userId` varchar(10) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `middleName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) NOT NULL,
  `suffixName` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL,
  `accountCreatedDate` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `archived` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblusersaccount`
--

LOCK TABLES `tblusersaccount` WRITE;
/*!40000 ALTER TABLE `tblusersaccount` DISABLE KEYS */;
INSERT INTO `tblusersaccount` VALUES ('05-44078','Melisa','Dela Rosa','Paz','','trhishanec@gmail.com','mPaz5','$2y$10$Ju0TEUMEjxw21w.br0whuOlprrm4D1Y5toCt.MrFO34N/zHSUjkaq','Student','2025-03-17 13:58:09.732692',0),('07-15734','Heila','','Piano','','trhishanec@gmail.com','hPiano66','$2y$10$cKeyU0IuXJyRnArxeS4Y4Ok9yEzeDyfmzncCxfCVDAyS.FG2DIOja','Teacher','2025-03-13 04:21:45.627104',0),('07-38589','Maria Kristine','Pelayo','Paz','N/A','trhishanec@gmail.com','mPaz1','$2y$10$IqMOJl7oan6lvmWABPTjr.xbkqN1tldvkC1tAK4AaArNy6/K97C7i','Teacher','2025-03-08 14:41:21.940084',0),('08-88691','Emely','Pablico','Cariaga','','','eCariaga54','$2y$10$lcXyWapJHmAW35S8iSdeGuffShasUWaPdM9ZO5cAa9sMe8nB/clde','Parent','2025-03-12 14:56:24.444744',0),('11-60273','John Rey','N/A','Soberano','Sr.','','jSoberano84','$2y$10$GgQ7t8JuuLVVTsQE9HUiZehZHBL2l0Xd7AnTURKjCmTjIpSxOdzh6','Parent','2024-11-18 15:17:07.652429',0),('15-17094','Frances','Pacleb','Toricer','N/A','','fToricer61','$2y$10$ZHe9xUuaEcry01XQWb5HXe3H1I43v92fxdMaNqLdm4h.MqUhidLMG','Student','2024-11-18 08:48:32.565876',0),('17-24138','Shiela','Angel','Paz','','trhishanec@gmail.com','sPaz35','$2y$10$3Qru0sU/NNG62TnfwWD0yuQra2fzfmS/0b2iYXs4Qkz0Fc5v/p2ki','Student','2025-02-02 14:19:12.861998',0),('18-43457','Justine ','','Alvarez','Jr.','','jAlvarez38','$2y$10$XeMSbY66Fh3W1gE1glBn6eqvbyFnaKgQcJ1MGbMVtep89b5B14Oim','Student','2024-11-19 00:23:57.443103',0),('19-57782','Wendy','N/A','Vito Cruz','N/A','','wVito Cruz98','$2y$10$FoKtPGCJl2zYhPPA1yghqu1/.dDHEauyGF.8BqhOlVr2CiUkwaatu','Parent','2024-11-18 15:23:57.908417',0),('19-69318','Benedicto','Lagang','Planta','I','','bPlanta9','$2y$10$a6STlMRzgzALuCBIJT6XW.RPMeLvmM1K2Ub0Ay3zZASSMV9Dg1dPO','Student','2024-11-15 15:29:02.515602',0),('20-53249','Whenzy','N/A','Vito Cruz','N/A','','wVito Cruz35','$2y$10$Dr8xV/5YmCrXa.bj0gRXP.NZ3T.ZKQmm5VQDxdYIyMp0sDdCALcDC','Student','2024-11-18 08:36:41.860814',0),('22-84817','Twixy','','Lazo','','trhishanec@gmail.com','tLazo40','$2y$10$IwUBIKq/iGdwv8N3eodwyu/C68rsu35/s8E2red1.OA7lXPenKahq','Student','2025-03-12 16:10:59.977744',0),('23-49685','Reden','Galinato','Baldovino','','','rBaldovino9','$2y$10$1cGRxKRq/OOQJV49hqwo1.8mKntVzck9OZVASVFj8bqORjUldHglS','Parent','2024-11-18 15:13:34.210024',0),('29-28996','Ailene','Ilar','Roldan','N/A','','aRoldan73','$2y$10$GCY78udIw3SBfcOT42uKN.Pu9LUYceVSMjUSdsmZGiFAGYLr6TzEq','Teacher','2024-11-18 14:16:49.455883',0),('29-61282','John Rey','N/A','Soberano','Jr.','','jSoberano59','$2y$10$KRfFLOWJBCDyVfiyqTWWP.AxFvibxK3wY7z1SMMwYAW6vQxwxOmou','Student','2024-11-18 08:32:51.238499',0),('31-86444','Russel','Galinato','Baldovino','Jr.','','rBaldovino48','$2y$10$cc5phyXuAygy6Rmk6DMND.yVS.60rvUwIgCaPgbPsnM2.ecWa8DGS','Student','2024-11-18 08:45:42.984523',0),('36-56970','Richard','Remillo','Cariaga','','','rCariaga79','$2y$10$9t9cmmUrZCN3cAncZAq3oe8j0LMKlH0/Eo1420OyN/hVjiF1XHacO','Parent','2024-11-01 05:35:46.871679',0),('37-70841','Jonna Fe','Salonga','Vasquez','','','jVasquez72','$2y$10$23jw86ORCHB3MScdnOyZW.tKiKqC96SoFNSJ9qVn82m26GcLEj8ei','Student','2024-11-15 08:11:02.365636',0),('37-77144','JC','P','Laxamana','','','jLaxamana79','$2y$10$mlRK2iEyZMyGktaJbE/iWO5Ed6DPwTPBTIe5S9fwzAQgP6QgQ78d.','Student','2024-11-14 14:54:07.367565',0),('37-77330','Mika','Pablico','Sotello','N/A','trhishanec@gmail.com','mSotello3','$2y$10$KFe6.wg5BKPU1QPIOq5gHu9Y4IB4osHKiffzNPEgVEJG3YEyqJnrq','Student','2025-02-02 14:09:09.924254',0),('38-50395','Andrea','Bermudez','Pre','','','aPre24','$2y$10$qZgfdbS.bihp/OKz5Xlsd.3CX0FZKiSFg/usGrxC/23rPDSbMpoGq','Student','2025-03-13 04:10:14.020700',0),('42-16981','John','Tabangcura','Pigao','Jr','trhishanec@gmail.com','jPigao70','$2y$10$DuV.ICp1ZOtWhtQ2G2XUGOWla1HdF1fC6X4PGXWACt2h.bt.6sH7W','Teacher','2025-03-13 04:21:53.265881',0),('42-20482','Ellaine','Marcos','Pipo','N/A','','ePipo54','$2y$10$rYz2dFNr7yScqCJvtCrAUOtLPEq373r5tC6Xmxt7P6ZUDnKvn1yVm','Student','2024-12-13 09:11:43.386765',0),('47-74261','Shanley','','Cruz','','trhishanec@gmail.com','sCruz47','$2y$10$jEtrnqwbigiTnzI7GhOseulir65YxrDgSumYFy1/k8veT1wRHaqyu','Student','2025-03-17 13:56:04.417741',0),('49-63928','Shayra','Pablico','Pre','','trhishanec@gmail.com','sPre95','$2y$10$eLWm2t9HKioHnTBaBgpf0uJDkVTE8ZSOsbHCGZYCxuqNbGV24Cxr.','Teacher','2025-03-13 04:21:49.658053',0),('50-14169','Carlo','N/A','Abaoag','N/A','','cAbaoag40','$2y$10$eiLDT75qXFPrR9r4QQgGcefojs0tmQfw84ZMO0SXuAVp7SXHm.FXe','Parent','2024-11-18 15:21:50.179533',0),('50-47115','Euan','Marcos','Pipo','','trhishanec@gmail.com','ePipo42','$2y$10$tvLSauQtWY7rjhLxiErEV.dGy75y3KSMsqAdk14xDP8G46enGKBMK','Student','2025-03-12 14:52:55.259536',0),('51-93349','Melise','Ong','Cortez','','trhishanec@gmail.com','mCortez76','$2y$10$lwG73QPTM0WnP0XNwxKPZeaqRPsMVbQvB2NkfsSRbddE5IajH1yPK','Student','2025-03-13 04:10:47.285056',0),('56-93633','Jezriel','Pablico','Pipo','','trhishanec@gmail.com','jPipo97','$2y$10$dEYzpS4s6ZPnuPvMUIzneONOV7FIRbIyOJ7Tw.vvm8bX7CpmRBFIm','Student','2024-12-13 09:11:49.742672',0),('57-04041','Davey','Sy','Xu','Jr','trhishanec@gmail.com','dXu26','$2y$10$SXbUfLpGhJ17HibJ/xGiC.mcM4BIU2Fhqb1UbDTkQ/E2xJ6nb6TkS','Student','2025-03-13 04:30:07.270814',0),('60-23906','Trhishane Nicole','Pablico','Cariaga','N/A','trhishanec@gmail.com','tCariaga','$2y$10$mT7kEnF.tXAQyQ2Cas6kIOULO0GJhjQ2bC7Yh4Q.P3RUp8CN8MUrG','Student','2025-03-12 14:52:22.469993',0),('63-75442','Cristian','Marcus','Bravo','','','cBravo76','$2y$10$j2YtSJ123U0o80jw29pdNOU4sRf2svL9sywNSfm4QjKQtlmAf9tEW','Teacher','2024-09-25 09:28:18.431930',0),('67-66551','Benedicto','Lagang','Planta','Jr.','','bPlanta92','$2y$10$ZyPt32UKjwYA0mv1JaXCcuPc199uH5AKXuV8vK74S9gdD1vRKBrDO','Parent','2024-12-13 14:05:31.217068',0),('68-24798','Michelle','Gorospe','Paz','','','mPaz96','$2y$10$YjepMFM0KysTtpkOrCnPV.rIrhMrF4ptsxuT2nnMlfz0AjSZnLnpu','Teacher','2024-11-18 08:12:17.763638',0),('71-54150','John','Cy','Doe','N/A','trhishanec@gmail.com','jDoe47','$2y$10$kawV684x//ZK.j8HKkm9w.APpj8TrBywfSoKZ1wcgfpMOBPfJLS72','Teacher','2025-03-12 14:57:51.876889',0),('74-70498','John','D','Doe','','richard.arruejo@unp.edu.ph','jDoe95','$2y$10$KjOxH7Ml6hxxb4ZgAVnKwO9Q3FTHOR7Q1e8Ommgj11DckQUnNbk8u','Student','2025-03-05 05:49:36.412538',0),('78-28145','Charmel','Niqui','Abaoag','N/A','','cAbaoag68','$2y$10$PYlxLarfECZJOSMXSyB8GuNYhxAzBLo7C1jSuaPhzfvL1Hgzru9wm','Student','2024-11-18 08:51:03.217762',0),('78-46629','Celine','Borce','Padre','','trhishanec@gmail.com','cPadre42','$2y$10$NfUtUCpgsrmLsjYqNJKBL.UN1JVOPiaEj/1TjpHZdNUPMMovlGxmm','Student','2025-01-21 03:05:05.936436',0),('80-35192','Shane','','Segui','','trhishanec@gmail.com','sSegui87','$2y$10$dnCFYT4In3qzDqhcVohRX.YOIrLjbxreb6xzgdJRkP9BHB.DgX.Le','Student','2025-03-13 04:10:51.743110',0),('86-34518','Rosemarie','Villegas','Quijano','N/A','','rQuijano88','$2y$10$Rcl719JnphYVNfHUA5cjIelXbTZ.NF.u8QqPR4TUgBA6BbYXdvGzi','Parent','2024-11-18 15:26:07.260509',0),('87-61705','John','D','Doe','','richard.arruejo@unp.edu.ph','jDoe8','$2y$10$3vIkmO3nb3l6PJirXoigHOA/cjd2BItdTRULpq6w6ahDNVqEmU9HS','Teacher','2025-02-27 14:27:30.607029',0),('90-30465','Alexandra','Reyes','Pablico','','','aPablico51','$2y$10$Twh5JeZvcbZCvfxkAIiiVOXvg1L4jpsXuJYnFXJRgDfSACMFVSPw2','Student','2024-11-18 13:34:13.765543',0),('91-16265','Davey','Peru','Llanes','','trhishanec@gmail.com','dLlanes60','$2y$10$J2oS3xqLJx.U18cXbGdYl.L4ZQ0OPW3.b/Re/EygCa7jxuGWEa3bK','Student','2025-03-17 13:55:32.243526',0),('93-06852','Amelia','Pacleb','Torricer','N/A','','aTorricer85','$2y$10$d6QPkcAIAXPpbz0dmxm4ne.2vmfNL/kydQJGAOPg2hayN88NSpAj.','Parent','2024-11-18 15:19:02.760739',0),('93-86245','Carlo Jay ','Villegas','Quijano','N/A','','cQuijano53','$2y$10$ku8rZ3yB9YOzkygkyVSywuq6OTmL42PLgx6V3oS2aBYUZ7xnN.w8W','Student','2024-11-18 08:39:13.101270',0),('96-13004','Mely','Salonga','Vasquez','N/A','trhishanec@gmail.com','mVasquez40','$2y$10$ZIAZnFH4gQ5jhY0KA9ZFt.Q/v86.FrZZA/QlsRxn1XUGVgQXwdHXW','Parent','2025-02-02 14:16:18.162934',0),('96-40977','Davey','Peru','Llanes','','','dLlanes14','$2y$10$6WoaMnXla81NKu9D8xKFi.WdJ.AYTwUPjUVp2UkGYTDuxJNyEYnB2','Student','2025-03-17 13:54:09.555261',0);
/*!40000 ALTER TABLE `tblusersaccount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblverificationcodes`
--

DROP TABLE IF EXISTS `tblverificationcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblverificationcodes` (
  `verificationId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `verificationCode` int(6) NOT NULL,
  `verifyStatus` int(1) NOT NULL,
  PRIMARY KEY (`verificationId`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblverificationcodes`
--

LOCK TABLES `tblverificationcodes` WRITE;
/*!40000 ALTER TABLE `tblverificationcodes` DISABLE KEYS */;
INSERT INTO `tblverificationcodes` VALUES (1,'61-26031','owellvennekimseguban58@gmail.com',222511,0),(2,'61-26031','owellvennekimseguban58@gmail.com',970084,0),(3,'61-26031','owellvennekimseguban58@gmail.com',918796,1),(4,'13-43237','eddeimar@gmail.com',913371,0),(5,'23-81528','shinmonb67@gmail.com',920473,1),(6,'32-10540','riveraarcangel044@gmail.com',418422,0),(7,'14-75790','oseguban.ccit@unp.edu.ph',815280,0),(8,'68-57871','trhishanec@gmail.com',245500,1),(9,'68-57871','trhishanec@gmail.com',301083,1),(10,'68-57871','trhishanec@gmail.com',125208,1),(11,'68-57871','trhishanec@gmail.com',374914,0),(12,'46-68970','trhishanec@gmail.com',252128,0),(13,'46-68970','trhishanec@gmail.com',738822,0),(14,'46-68970','trhishanec@gmail.com',160649,0),(15,'46-68970','trhishanec@gmail.com',709297,0),(16,'46-68970','trhishanec@gmail.com',579261,1),(17,'46-68970','trhishanec@gmail.com',672251,1),(18,'46-68970','trhishanec@gmail.com',150792,1),(19,'46-68970','trhishanec@gmail.com',381607,1),(20,'46-68970','trhishanec@gmail.com',729344,1),(21,'46-68970','trhishanec@gmail.com',780714,1),(22,'46-68970','trhishanec@gmail.com',442782,0),(23,'46-68970','trhishanec@gmail.com',633688,1),(24,'46-68970','trhishanec@gmail.com',931364,1),(25,'46-68970','trhishanec@gmail.com',261270,1),(26,'46-68970','trhishanec@gmail.com',601754,1),(27,'46-68970','trhishanec@gmail.com',264856,1),(28,'46-68970','trhishanec@gmail.com',804701,1),(29,'46-71408','trhishanec@gmail.com',858014,0),(30,'46-71408','trhishanec@gmail.com',239892,1),(31,'46-71408','trhishanec@gmail.com',207374,1),(32,'65-70348','trhishanec@gmail.com',690970,1),(33,'63-75442','trhishanec@gmail.com',143627,1),(34,'37-77144','trhishanec@gmail.com',577554,1),(35,'71-54150','trhishanec@gmail.com',907127,0),(36,'71-54150','trhishanec@gmail.com',598431,1),(37,'71-54150','trhishanec@gmail.com',109780,1),(38,'71-54150','trhishanec@gmail.com',324082,1),(39,'71-54150','trhishanec@gmail.com',948111,0),(40,'71-54150','trhishanec@gmail.com',778618,1),(41,'60-23906','trhishanec@gmail.com',786174,1),(42,'37-70841','trhishanec@gmail.com',268988,0),(43,'37-70841','trhishanec@gmail.com',856868,0),(44,'37-70841','trhishanec@gmail.com',766145,0),(45,'37-70841','trhishanec@gmail.com',977635,0),(46,'37-70841','trhishanec@gmail.com',134961,0),(47,'37-70841','trhishanec@gmail.com',606887,0),(48,'87-56740','trhishanec@gmail.com',341026,1),(49,'62-90355','trhishanec@gmail.com',915327,1),(50,'01-08768','trhishanec@gmail.com',974797,1),(51,'03-20134','trhishanec@gmail.com',218618,0),(52,'03-20134','trhishanec@gmail.com',567980,0),(53,'03-20134','trhishanec@gmail.com',528449,1),(54,'03-20134','trhishanec@gmail.com',131965,1),(55,'03-20134','trhishanec@gmail.com',506087,0),(56,'03-20134','trhishanec@gmail.com',188745,0),(57,'03-20134','trhishanec@gmail.com',574540,0),(58,'03-20134','trhishanec@gmail.com',101163,0),(59,'03-20134','trhishanec@gmail.com',907650,0),(60,'03-20134','trhishanec@gmail.com',977417,0),(61,'18-72006','trhishanec@gmail.com',787040,0),(62,'18-72006','trhishanec@gmail.com',469534,0),(63,'91-23428','trhishanec@gmail.com',220404,0),(64,'91-23428','tncariaga.ccit@unp.edu.ph',492076,0),(65,'91-23428','trhishanec@gmail.com',402653,1),(66,'08-88691','trhishanec@gmail.com',736646,1),(67,'36-56970','trhishanec@gmail.com',825153,0),(68,'36-56970','trhishanec@gmail.com',144715,1),(69,'36-56970','trhishanec@gmail.com',953961,0),(70,'36-56970','trhishanec@gmail.com',255854,0),(71,'36-56970','trhishanec@gmail.com',729203,0),(72,'36-56970','trhishanec@gmail.com',332128,0),(73,'93-86245','trhishanec@gmail.com',714977,1),(74,'68-24798','trhishanec@gmail.com',438203,1),(75,'29-61282','trhishanec@gmail.com',374629,1),(76,'20-53249','trhishanec@gmail.com',892589,1),(77,'19-69318','trhishanec@gmail.com',585516,1),(78,'15-17094','trhishanec@gmail.com',281638,1),(79,'78-28145','trhishanec@gmail.com',674924,1),(80,'31-86444','trhishanec@gmail.com',306271,1),(81,'90-30465','trhishanec@gmail.com',794338,1),(82,'29-28996','trhishanec@gmail.com',891961,1),(83,'11-60273','trhishanec@gmail.com',472339,1),(84,'93-06852','trhishanec@gmail.com',629799,1),(85,'50-14169','trhishanec@gmail.com',946742,1),(86,'19-57782','trhishanec@gmail.com',427612,1),(87,'86-34518','trhishanec@gmail.com',506305,1),(88,'23-49685','trhishanec@gmail.com',674954,1),(89,'67-66551','trhishanec@gmail.com',433423,1),(90,'96-13004','trhishanec@gmail.com',453920,1),(91,'18-43457','trhishanec@gmail.com',486287,1),(92,'42-20482','trhishanec@gmail.com',138233,0),(93,'42-20482','trhishanec@gmail.com',324805,0),(94,'42-20482','trhishanec@gmail.com',701122,0),(95,'42-20482','trhishanec@gmail.com',983713,0),(96,'42-20482','trhishanec@gmail.com',791933,0),(97,'42-20482','trhishanec@gmail.com',558573,0),(98,'42-20482','trhishanec@gmail.com',672127,1),(99,'60-23906','trhishanec@gmail.com',722681,0),(100,'37-77330','trhishanec@gmail.com',878536,1),(101,'07-38589','trhishanec@gmail.com',998089,1),(102,'71-54150','trhishanec@gmail.com',275370,1);
/*!40000 ALTER TABLE `tblverificationcodes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-21 22:41:25
