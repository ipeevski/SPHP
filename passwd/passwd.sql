CREATE TABLE IF NOT EXISTS `passwords` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account` varchar(255) character set ascii NOT NULL,
  `site` varchar(255) character set ascii NOT NULL,
  `username` varchar(100) character set ascii NOT NULL,
  `password` varchar(100) character set ascii NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `account` (`account`,`site`,`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
