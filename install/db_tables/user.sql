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
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `sess` varchar(32) DEFAULT NULL,
  `activation` varchar(32) DEFAULT NULL,
  `ban` int(11) NOT NULL DEFAULT '0',
  `ban_pr` varchar(64) DEFAULT NULL,
  `ip` bigint(20) NOT NULL DEFAULT '0',
  `ip_cl` bigint(20) NOT NULL DEFAULT '0',
  `ip_xff` bigint(20) NOT NULL DEFAULT '0',
  `ua` varchar(32) DEFAULT NULL,
  `date_reg` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `date_aut` int(11) NOT NULL DEFAULT '0',
  `date_last` int(11) NOT NULL DEFAULT '0',
  `balls` int(11) NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `level` enum('0','1','2','3','9','10') NOT NULL DEFAULT '0',
  `group_access` int(10) unsigned NOT NULL DEFAULT '0',
  `pol` enum('0','1') NOT NULL DEFAULT '1',
  `url` varchar(64) NOT NULL DEFAULT '/',
  `show_url` enum('0','1') NOT NULL DEFAULT '1',
  `ank_g_r` int(4) DEFAULT NULL,
  `ank_m_r` int(2) DEFAULT NULL,
  `ank_d_r` int(2) DEFAULT NULL,
  `ank_city` varchar(32) DEFAULT NULL,
  `ank_o_sebe` varchar(512) DEFAULT NULL,
  `ank_icq` int(9) DEFAULT NULL,
  `ank_skype` varchar(16) DEFAULT NULL,
  `ank_mail` varchar(32) DEFAULT NULL,
  `ank_n_tel` varchar(11) DEFAULT NULL,
  `ank_name` varchar(32) DEFAULT NULL,
  `set_time_chat` int(11) DEFAULT '30',
  `set_p_str` int(11) DEFAULT '7',
  `set_show_icon` enum('0','1','2') DEFAULT '1',
  `set_translit` enum('0','1') NOT NULL DEFAULT '1',
  `set_files` enum('0','1') NOT NULL DEFAULT '0',
  `set_timesdvig` int(11) NOT NULL DEFAULT '0',
  `set_news_to_mail` enum('0','1') NOT NULL DEFAULT '0',
  `set_show_mail` enum('0','1') NOT NULL DEFAULT '0',
  `set_them` varchar(32) DEFAULT 'default',
  `set_them2` varchar(32) DEFAULT 'default',
  `meteo_country` int(11) NOT NULL DEFAULT '0',
  `autorization` enum('0','1') NOT NULL DEFAULT '0',
  `add_konts` enum('0','1','2') NOT NULL DEFAULT '1',
  `wall` int(1) DEFAULT '1',
  `browser` varchar(3) DEFAULT 'wap',
  `ank_rost` int(11) DEFAULT NULL,
  `ank_ves` int(11) DEFAULT NULL,
  `ank_telosl` int(1) NOT NULL,
  `ank_cvet_glas` varchar(11) NOT NULL,
  `ank_volos` varchar(11) NOT NULL,
  `ank_orien` int(1) DEFAULT '0',
  `ank_lov_1` int(11) DEFAULT '0',
  `ank_lov_2` int(11) DEFAULT '0',
  `ank_lov_3` int(11) DEFAULT '0',
  `ank_lov_4` int(11) DEFAULT '0',
  `ank_lov_5` int(11) DEFAULT '0',
  `ank_lov_6` int(11) DEFAULT '0',
  `ank_lov_7` int(11) DEFAULT '0',
  `ank_lov_8` int(11) DEFAULT '0',
  `ank_lov_9` int(11) DEFAULT '0',
  `ank_lov_10` int(11) DEFAULT '0',
  `ank_lov_11` int(11) DEFAULT '0',
  `ank_lov_12` int(11) DEFAULT '0',
  `ank_lov_13` int(11) DEFAULT '0',
  `ank_lov_14` int(11) DEFAULT '0',
  `ank_lov_15` int(11) DEFAULT '0',
  `ank_o_par` varchar(215) NOT NULL,
  `ank_zan` varchar(215) NOT NULL,
  `ank_smok` int(11) NOT NULL,
  `ank_mat_pol` int(11) NOT NULL,
  `ank_proj` int(11) NOT NULL,
  `ank_baby` int(11) NOT NULL,
  `ank_avto` varchar(215) NOT NULL,
  `ank_avto_n` int(11) NOT NULL,
  `ank_alko` varchar(215) DEFAULT NULL,
  `ank_alko_n` int(11) DEFAULT '0',
  `ank_nark` int(11) DEFAULT '0',
  `rating_tmp` int(11) DEFAULT '0',
  `sort` int(1) DEFAULT '0',
  `news_read` int(1) DEFAULT '0',
  `ban_where` varchar(10) DEFAULT NULL,
  `abuld` int(1) DEFAULT '0',
  `vk_id` int(11) DEFAULT NULL,
  `type_reg` varchar(100) DEFAULT NULL,
  `identity` varchar(100) DEFAULT NULL,
  `set_nick` enum('0','1') NOT NULL DEFAULT '1',
  `money` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nick` (`nick`),
  KEY `url` (`url`),
  KEY `date_last` (`date_last`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-31 18:29:30
