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
-- Table structure for table `all_accesses`
--

DROP TABLE IF EXISTS `all_accesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `all_accesses` (
  `type` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Дамп данных таблицы `all_accesses`
--

INSERT INTO `all_accesses` (`type`, `name`) VALUES
('adm_panel_show', 'Админка - доступ к разделам админки'),
('loads_file_upload', 'Загрузки - выгрузка файлов'),
('loads_dir_mesto', 'Загрузки - перемещение папок'),
('loads_dir_delete', 'Загрузки - удаление папок'),
('loads_dir_rename', 'Загрузки - переименование папок'),
('loads_dir_create', 'Загрузки - создание папок'),
('loads_file_edit', 'Загрузки - параметры файлов'),
('loads_file_delete', 'Загрузки - удаление файлов'),
('loads_unzip', 'Загрузки - Распаковка ZIP'),
('lib_stat_zip', 'Библиотека - выгрузка статей в ZIP'),
('lib_stat_txt', 'Библиотека - выгрузка статей в txt'),
('lib_stat_create', 'Библиотека - создание статей'),
('lib_dir_delete', 'Библиотека - удаление папок'),
('lib_dir_mesto', 'Библиотека - перемещение папок'),
('lib_dir_edit', 'Библиотека - редактирование папок'),
('lib_dir_create', 'Библиотека - создание папок'),
('lib_stat_delete', 'Библиотека - удаление статей'),
('votes_settings', 'Голосования - закрытие/удаление'),
('votes_create', 'Голосования - создание'),
('guest_clear', 'Гостевая - очистка'),
('guest_delete', 'Гостевая - удаление постов'),
('obmen_dir_delete', 'Обменник - удаление папок'),
('obmen_dir_edit', 'Обменник - управление папками'),
('obmen_dir_create', 'Обменник - создание папок'),
('obmen_file_delete', 'Обменник - удаление файлов'),
('obmen_file_edit', 'Обменник - редактирование файлов'),
('obmen_komm_del', 'Обменник - удаление комментариев'),
('foto_foto_edit', 'Фотогалерея - редактирование/удаление фото'),
('foto_alb_del', 'Фотогалерея - удаление альбомов'),
('foto_komm_del', 'Фотогалерея - удаление комментариев'),
('forum_razd_create', 'Форум - создание разделов'),
('forum_for_delete', 'Форум - удаление подфорумов'),
('forum_for_edit', 'Форум - редактирование подфорумов'),
('forum_for_create', 'Форум - создание подфорумов'),
('forum_razd_edit', 'Форум - управление разделами'),
('adm_info', 'Админка - общая информация'),
('forum_them_edit', 'Форум - редактирование тем'),
('forum_them_del', 'Форум - удаление тем'),
('forum_post_ed', 'Форум - редактирование сообщений'),
('chat_clear', 'Чат - очистка'),
('chat_room', 'Чат - управление комнатами'),
('adm_statistic', 'Админка - статистика'),
('adm_banlist', 'Админка - список забаненых'),
('adm_menu', 'Админка - главное меню'),
('adm_news', 'Админка - новости'),
('adm_rekl', 'Админка - реклама'),
('adm_set_sys', 'Админка - настройки системы'),
('adm_set_loads', 'Админка - настройки загруз-центра'),
('adm_set_user', 'Админка - пользовательские настройки'),
('adm_set_chat', 'Админка - настройки чата'),
('adm_set_forum', 'Админка - настройки форума'),
('adm_set_foto', 'Админка - настройки фотогалереи'),
('adm_forum_sinc', 'Админка - синхронизация таблиц форума'),
('adm_themes', 'Админка - темы оформления'),
('adm_log_read', 'Админка - лог действий администрации'),
('adm_log_delete', 'Админка - удаление лога'),
('adm_mysql', 'Админка - MySQL запросы !!!'),
('adm_ref', 'Админка - рефералы'),
('adm_show_adm', 'Админка - список администрации'),
('adm_ip_edit', 'Админка - редактирование IP операторов'),
('adm_ban_ip', 'Админка - бан по IP'),
('adm_accesses', 'Привилегии групп пользователей !!!'),
('user_delete', 'Пользователи - удаление'),
('user_mass_delete', 'Пользователи - массовое удаление'),
('user_ban_set', 'Пользователи - бан'),
('user_ban_unset', 'Пользователи - снятие бана'),
('user_prof_edit', 'Пользователи - редактирование профиля'),
('user_collisions', 'Пользователи - совпадения ников'),
('user_show_ip', 'Пользователи - показывать IP'),
('user_show_ua', 'Пользователи - показ USER-AGENT'),
('user_show_add_info', 'Пользователи - показ доп. информации'),
('guest_show_ip', 'Гости - показ IP'),
('user_change_group', 'Пользователи - смена группы привилегий'),
('user_ban_set_h', 'Пользователи - бан (max 1 сутки)'),
('forum_post_close', 'Форум - возможность писать в закрытой теме'),
('user_change_nick', 'Пользователи - смена ника'),
('loads_file_import', 'Загрузки - импорт файлов'),
('adm_lib_repair', 'Восстановление библиотеки'),
('notes_edit', 'Дневники - редактирование'),
('notes_delete', 'Дневники - удаление');
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-31 18:29:29
