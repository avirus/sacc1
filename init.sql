-- $Author: slavik $  $LastChangedDate: 2012-11-20 17:39:26 +0600 (Вт, 20 ноя 2012) $ $Rev: 112 $
-- $Id: init.sql 112 2012-11-20 11:39:26Z slavik $ 
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`sacc` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `sacc`;

/*Table structure for table `acl` */

DROP TABLE IF EXISTS `acl`;

CREATE TABLE `acl` (
  `id` int(11) NOT NULL auto_increment,
  `sysname` varchar(15) default NULL,
  `vname` text,
  `data` text,
  `blacklist` text,
  `whitelist` text,
  `stoplist` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=koi8r;

/*Table structure for table `admins` */

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL auto_increment,
  `login` varchar(15) default NULL,
  `passwd` varchar(50) default NULL,
  `descr` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=koi8r;

/*Table structure for table `detail` */

DROP TABLE IF EXISTS `detail`;

CREATE TABLE `detail` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `utime` bigint(20) default NULL,
  `qtime` bigint(20) unsigned default NULL,
  `ip_addr` int(10) unsigned default '0',
  `code` varchar(20) default NULL,
  `size` bigint(20) unsigned default NULL,
  `method` varchar(10) default NULL,
  `url` text,
  `server` text,
  `u_id` mediumint(9) default NULL,
  `t_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `szut` (`size`,`utime`),
  KEY `code` (`code`),
  KEY `url` (`url`(20))
) ENGINE=MyISAM DEFAULT CHARSET=koi8r;

/*Table structure for table `mail` */

DROP TABLE IF EXISTS `mail`;

CREATE TABLE `mail` (
  `uid` int(11) NOT NULL auto_increment,
  `frm` text,
  `size` bigint(20) default NULL,
  `id` text,
  `rcpt` text,
  PRIMARY KEY  (`uid`),
  KEY `email1` (`frm`(15)),
  KEY `email2` (`id`(15)),
  KEY `email4` (`rcpt`(15))
) ENGINE=MyISAM DEFAULT CHARSET=koi8r;

/*Table structure for table `options` */

DROP TABLE IF EXISTS `options`;

CREATE TABLE `options` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(127) NOT NULL default '',
  `value` text,
  `descr` text,
  `help` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=koi8r;

/*Table structure for table `queue` */

DROP TABLE IF EXISTS `queue`;

CREATE TABLE `queue` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `complete` int(10) unsigned default '0',
  `command` int(10) unsigned NOT NULL default '0',
  `itime` int(10) unsigned NOT NULL default '0',
  `rtime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `complete` (`complete`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=koi8r;

/*Table structure for table `shistory` */

DROP TABLE IF EXISTS `shistory`;

CREATE TABLE `shistory` (
  `id` int(11) NOT NULL auto_increment,
  `uh_id` int(11) NOT NULL default '0',
  `site` text COMMENT 'site url',
  `size` bigint(20) default '0',
  `tname` text COMMENT 'tarif name',
  `tval` int(11) NOT NULL default '1' COMMENT 'tarif value',
  PRIMARY KEY  (`id`),
  KEY `uh_id` (`uh_id`)
) ENGINE=MyISAM DEFAULT CHARSET=koi8r;

/*Table structure for table `site` */

DROP TABLE IF EXISTS `site`;

CREATE TABLE `site` (
  `id` int(11) NOT NULL auto_increment,
  `u_id` int(11) default NULL,
  `site` text,
  `size` bigint(20) default '0',
  `lutime` bigint(20) unsigned NOT NULL default '0',
  `futime` bigint(20) unsigned NOT NULL default '0',
  `t_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `site1` (`site`(40))
) ENGINE=MyISAM DEFAULT CHARSET=koi8r;

/*Table structure for table `sys_trf` */

DROP TABLE IF EXISTS `sys_trf`;

CREATE TABLE `sys_trf` (
  `offset` bigint(20) default NULL,
  `moffset` bigint(20) default NULL,
  `trf` bigint(20) default NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=koi8r;

/*Table structure for table `syslog` */

DROP TABLE IF EXISTS `syslog`;
CREATE TABLE `syslog` (
  `id` int(11) NOT NULL auto_increment,
  `a_id` int(11) default NULL,
  `record` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=koi8r;

/*Table structure for table `tsites` */

DROP TABLE IF EXISTS `tsites`;

CREATE TABLE `tsites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` text,
  `ad` int(1) unsigned default '1',
  `comment` text,
  `tarif` int(11) unsigned default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=koi8r;

/*Table structure for table `uhistory` */

DROP TABLE IF EXISTS `uhistory`;

CREATE TABLE `uhistory` (
  `login` varchar(25) default NULL,
  `descr` text,
  `quota` bigint(20) default '1',
  `used` bigint(20) default NULL,
  `id` bigint(20) NOT NULL auto_increment,
  `utime` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `utime` (`utime`),
  FULLTEXT KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=koi8r;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `aid` int(11) NOT NULL default '0',
  `login` varchar(25) default NULL,
  `quota` bigint(20) unsigned default '1',
  `used` bigint(20) unsigned default '0',
  `timeacl` varchar(20) default '',
  `status` int(11) NOT NULL default '0',
  `descr` text,
  `email` varchar(50) default NULL,
  `dquota` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM AUTO_INCREMENT=244 DEFAULT CHARSET=koi8r;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
