# phpMyAdmin MySQL-Dump
# version 2.3.0-rc1
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Mar 12, 2005 at 10:13 AM
# Server version: 3.23.58
# PHP Version: 4.3.10
# Database : `chat`
# --------------------------------------------------------

#
# Table structure for table `logins`
#

CREATE TABLE logins (
  date timestamp(14) NOT NULL,
  user varchar(100) NOT NULL default '',
  pass varchar(100) NOT NULL default ''
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pms`
#

CREATE TABLE pms (
  id int(11) NOT NULL auto_increment,
  uto varchar(20) NOT NULL default '',
  ufrom varchar(20) NOT NULL default '',
  date timestamp(14) NOT NULL,
  msg text,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `posts`
#

CREATE TABLE posts (
  id int(11) NOT NULL auto_increment,
  user varchar(20) default NULL,
  date timestamp(14) NOT NULL,
  post text,
  PRIMARY KEY  (id),
  KEY user (user),
  KEY id (id),
  KEY date (date)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `readposts`
#

CREATE TABLE readposts (
  user varchar(50) NOT NULL default '',
  lastread int(11) NOT NULL default '0'
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `user_settings`
#

CREATE TABLE user_settings (
  user varchar(20) NOT NULL default '',
  style varchar(100) NOT NULL default '',
  color varchar(10) default NULL,
  icons tinyint(4) NOT NULL default '0',
  userlist tinyint(4) NOT NULL default '0',
  refresh int(11) NOT NULL default '2',
  sound tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (user)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `users`
#

CREATE TABLE users (
  user varchar(20) NOT NULL default '',
  pass varchar(20) NOT NULL default '',
  lastseen bigint(14) default NULL,
  PRIMARY KEY  (user(3))
) TYPE=MyISAM;