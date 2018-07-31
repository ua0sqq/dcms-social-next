-- MySQL dump 10.13  Distrib 5.7.22, for Linux (i686)
--
-- Host: localhost    Database: social_backup
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `frends`
--

DROP TABLE IF EXISTS `frends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frends` (
  `user` int(11) NOT NULL DEFAULT '0',
  `frend` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `i` int(1) DEFAULT '0',
  `lenta_forum` int(11) NOT NULL DEFAULT '1',
  `lenta_obmen` int(11) NOT NULL DEFAULT '1',
  `lenta_foto` int(11) NOT NULL DEFAULT '1',
  `lenta_notes` int(11) NOT NULL DEFAULT '1',
  `lenta_avatar` int(11) DEFAULT '1',
  `lenta_frends` int(1) DEFAULT '1',
  `lenta_status` int(1) DEFAULT '1',
  `lenta_status_like` int(1) DEFAULT '1',
  `disc_forum` int(11) NOT NULL DEFAULT '1',
  `disc_obmen` int(11) NOT NULL DEFAULT '1',
  `disc_foto` int(11) NOT NULL DEFAULT '1',
  `disc_notes` int(11) NOT NULL DEFAULT '1',
  `disc_frends` int(1) DEFAULT '1',
  `disc_status` int(1) DEFAULT '1',
  UNIQUE KEY `frend_UNIQUE` (`frend`,`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-31 18:29:29
