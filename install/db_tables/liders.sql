
CREATE TABLE IF NOT EXISTS `liders` (
  `time` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `time_p` int(11) NOT NULL,
  `msg` varchar(215) NOT NULL,
  `stav` int(11) NOT NULL DEFAULT '0',
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
