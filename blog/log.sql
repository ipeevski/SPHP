# phpMyAdmin MySQL-Dump
# version 2.3.0-rc1
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Mar 12, 2005 at 10:19 AM
# Server version: 3.23.58
# PHP Version: 4.3.10
# Database : `cyberhorse`
# --------------------------------------------------------

#
# Table structure for table `posts`
#

CREATE TABLE posts (
  id int(11) NOT NULL auto_increment,
  timestamp timestamp(14) NOT NULL,
  flags tinyint(4) NOT NULL default '0',
  user varchar(20) NOT NULL default '',
  title varchar(200) NOT NULL default '',
  message text NOT NULL,
  attachment varchar(255) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `replies`
#

CREATE TABLE replies (
  id int(11) NOT NULL auto_increment,
  parent int(11) NOT NULL default '0',
  timestamp timestamp(14) NOT NULL,
  user varchar(20) NOT NULL default '',
  message text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;