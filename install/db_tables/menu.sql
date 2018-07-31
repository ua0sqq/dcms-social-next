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
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('link','razd') NOT NULL DEFAULT 'link',
  `name` varchar(32) NOT NULL,
  `url` varchar(32) NOT NULL,
  `counter` varchar(32) NOT NULL,
  `pos` int(11) NOT NULL,
  `icon` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos` (`pos`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='Главное меню';

--
-- Дамп данных таблицы `menu`
--

INSERT INTO `menu` (`id`, `type`, `name`, `url`, `counter`, `pos`, `icon`) VALUES
(1, 'link', 'Новости', '/news/', 'news/count.php', 1, 'news.png'),
(2, 'link', 'Чат', '/chat/', 'chat/count.php', 7, 'chat.png'),
(4, 'link', 'Гостевая', '/guest/', 'guest/count.php', 9, 'guest.png'),
(5, 'link', 'Зона обмена', '/obmen/', 'obmen/count.php', 5, 'obmen.png'),
(6, 'link', 'Форум', '/forum/', 'forum/count.php', 6, 'forum.png'),
(7, 'link', 'Фотогалерея', '/foto/', 'foto/count.php', 10, 'foto.png'),
(11, 'link', 'Лидеры', '/user/liders/', '/user/liders/count.php', 4, 'lider.gif'),
(10, 'link', 'Дневники', '/plugins/notes/', 'plugins/notes/count.php', 8, 'zametki.gif'),
(12, 'link', 'Знакомства', '/user/love/', '/user/love/count.php', 3, 'meets.gif'),
(13, 'link', 'Информация', '/plugins/rules/', '', 12, 'info.gif'),
(14, 'link', 'Обитатели', '/user/users.php', '/user/count.php', 11, 'druzya.png');
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-31 18:29:29
