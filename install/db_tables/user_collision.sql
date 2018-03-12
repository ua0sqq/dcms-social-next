CREATE TABLE `user_collision` (
  `id_user` int(11) NOT NULL,
  `id_user2` int(11) NOT NULL,
  `type` set('sess','ip_ua_time') NOT NULL default 'sess',
  KEY `id_user` (`id_user`,`id_user2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;