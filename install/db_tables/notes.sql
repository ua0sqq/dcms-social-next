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
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg` text,
  `time` int(11) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `private` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(64) NOT NULL,
  `id_user` int(11) DEFAULT '0',
  `private_komm` int(11) DEFAULT '0',
  `count` int(11) DEFAULT '0',
  `id_dir` int(11) DEFAULT '0',
  `type` int(1) DEFAULT '0',
  `share` enum('0','1') DEFAULT '0',
  `share_id` int(11) DEFAULT NULL,
  `share_text` text,
  `share_name` varchar(60) DEFAULT NULL,
  `share_id_user` int(11) DEFAULT NULL,
  `share_type` varchar(10) DEFAULT 'notes',
  `share_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `id_user` (`id_user`),
  KEY `share_id` (`share_id`,`share_type`) USING BTREE,
  KEY `share_type` (`share_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-31 18:29:29
