-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: u376937047_gamuchirai_db
-- ------------------------------------------------------
-- Server version	8.0.40

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
-- Table structure for table `student_tasks`
--

DROP TABLE IF EXISTS `student_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date NOT NULL,
  `task_type` enum('lesson','assignment','test') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_student_tasks_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`sid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_tasks`
--

LOCK TABLES `student_tasks` WRITE;
/*!40000 ALTER TABLE `student_tasks` DISABLE KEYS */;
INSERT INTO `student_tasks` VALUES (1,1,'Practical theory','2025-10-29','assignment',1,'2025-10-14 14:54:55'),(2,1,'Practical theory I','2025-10-29','test',0,'2025-10-14 14:54:55'),(3,1,'Web Design Project','2025-10-25','assignment',1,'2025-10-14 14:54:55'),(4,1,'HTML Quiz','2025-10-20','test',1,'2025-10-14 14:54:55'),(5,1,'Python Data Analysis','2025-11-05','assignment',0,'2025-10-14 14:54:55'),(6,1,'Final Assessment','2025-11-15','test',0,'2025-10-14 14:54:55'),(7,1,'Introduction to Web Design','2025-10-16','lesson',1,'2025-10-14 15:02:13'),(8,1,'CSS Fundamentals','2025-10-18','lesson',1,'2025-10-14 15:02:13'),(9,1,'JavaScript Basics','2025-10-22','lesson',0,'2025-10-14 15:02:13'),(10,1,'Responsive Design','2025-10-28','lesson',1,'2025-10-14 15:02:13'),(11,1,'Python Variables and Data Types','2025-11-01','lesson',0,'2025-10-14 15:02:13'),(12,1,'Python Functions','2025-11-08','lesson',0,'2025-10-14 15:02:13');
/*!40000 ALTER TABLE `student_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `sid` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  PRIMARY KEY (`sid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'Gamuchirai','Kundhlande','gkundhlande@gmail.com','$2y$12$n2f2xhUlkHHrSu6sYZkw3.9YVyDrBE2aU3nJGmP6BS1T8/vgvRH4m',0,NULL,1,'Male','1993-09-10'),(2,'fsfdfad','adfdf','gkundhlande@gmadfail.com','$2y$12$kE/76XhwYf4YeGRaWeV/puBH9LzG6b8yOfl1Q0oGU.7NKzJYm/1t6',0,NULL,1,'Male','2241-02-10');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-20 15:12:26
