
CREATE TABLE IF NOT EXISTS `forum_f` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `pos` int(11) NOT NULL,
  `opis` varchar(512) NOT NULL,
  `adm` set('0','1') NOT NULL DEFAULT '0',
  `icon` varchar(30) DEFAULT 'default',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

INSERT INTO `forum_f` (`id`, `name`, `pos`, `opis`, `adm`, `icon`) VALUES
(1, 'Новости форума', 1, 'Конкурсы, акции, мероприятия, новости', '0', 'f_news.gif'),
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
