CREATE TABLE IF NOT EXISTS `todo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `task` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `duration` int(10) unsigned NOT NULL COMMENT 'in minutes',
  `date` datetime default NULL,
  `priority` int(11) NOT NULL,
  `bug` tinyint(4) NOT NULL,
  `completed` tinyint(4) NOT NULL,
  `recur` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `priority` (`priority`),
  KEY `completed` (`completed`),
  KEY `duration` (`duration`),
  KEY `bug` (`bug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `todo_history` (
  `id` int(10) unsigned NOT NULL,
  `msg` text NOT NULL,
  `time` INT UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
