
CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

INSERT INTO `user_group` (`id`, `name`, `level`) VALUES
(1, 'Пользователь', 0),
(2, 'Модератор чата', 1),
(3, 'Модератор форума', 1),
(4, 'Модератор Зоны обмена', 1),
(5, 'Модератор библиотеки', 1),
(6, 'Модератор фотогалереи', 1),
(7, 'Модератор', 2),
(8, 'Администратор', 3),
(9, 'Главный администратор', 9),
(15, 'Создатель', 10),
(11, 'Модератор дневников', 1),
(12, 'Модератор гостевой', 1);