CREATE TABLE `media_info` (
  `id` int(11) NOT NULL auto_increment,
  `file` varchar(64) NOT NULL,
  `size` int(11) NOT NULL,
  `lenght` varchar(32) NOT NULL,
  `bit` varchar(32) NOT NULL,
  `codec` varchar(32) NOT NULL,
  `wh` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `file` (`file`,`size`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;