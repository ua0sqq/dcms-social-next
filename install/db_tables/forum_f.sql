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
-- Table structure for table `forum_f`
--

DROP TABLE IF EXISTS `forum_f`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_f` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `pos` int(11) NOT NULL,
  `opis` varchar(512) NOT NULL,
  `adm` set('0','1') NOT NULL DEFAULT '0',
  `icon` varchar(30) DEFAULT 'default',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `forum_f`
--

INSERT INTO `forum_f` (`id`, `name`, `pos`, `opis`, `adm`, `icon`) VALUES
(1, 'Новости форума', 1, 'Конкурсы, акции, мероприятия, новости.', '0', 'default.png'),
(2, 'Общение и знакомство', 2, 'Общение между пользователями нашего сайта', '0', 'f_obshenie.gif'),
(3, 'Тематические форумы', 3, 'Форумы разбитые по темам', '0', 'f_tematijka.gif'),
(4, 'Секс и отношения', 4, 'Полезные статьи, Любовь, Секс, Вопросы о сексе', '0', 'F_seks.gif'),
(5, 'Досуг и увлечения', 5, 'Отдых, Туризм, Кино, Авто/Мото и др.', '0', 'f_dosug.gif'),
(6, 'Музыка', 6, 'Все что связано с музыкой', '0', 'f_music.gif'),
(7, 'Все о спорте', 7, 'Футбол хоккей и прочее', '0', 'f_sport.gif'),
(8, 'Мобильные телефоны', 8, 'Обсуждение моделей, Покупка, Продажа', '0', 'f_mobil.gif'),
(9, 'Все для телефона', 9, 'Java Symbian Мелодии Картинки', '0', 'f_vse_mobil.gif'),
(10, 'Мобильная связь', 10, 'Все о операторах, WAP; GPRS; EDGE; 3G; Wi-Fi; SMS; MMS', '0', 'svyaz_mob.gif'),
(11, 'Компьютеры', 11, 'Все о компьютерах', '0', 'f_jkomp.gif'),
(12, 'Беспредел', 12, 'No comments...', '0', 'bespredel.gif');
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-31 18:29:29
