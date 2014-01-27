-- $Author: slavik $  $LastChangedDate: 2012-11-20 17:39:26 +0600 (Вт, 20 ноя 2012) $ $Rev: 112 $
-- $Id: data.sql 112 2012-11-20 11:39:26Z slavik $

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`sacc` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `sacc`;

/*Data for the table `acl` */

LOCK TABLES `acl` WRITE;

insert  into `acl`(`id`,`sysname`,`vname`,`data`,`blacklist`,`whitelist`,`stoplist`) values (1,'time1700','с 17:00 до 9:00 c обедом','acl time1700_time time MTWHF 17:00-23:59\r\nacl time1700_time time MTWHF 12:59-14:00\r\nacl time1700_time time SA 0:00-23:59\r\nacl time1700_time time MTWHF 0:00-8:59\r\nacl time1700_blacktime time MTWHF 9:30-12:59\r\nacl time1700_blacktime time MTWHF 14:00-18:30\r\nacl time1700_type method CONNECT\r\nacl time1700_typetime time MTWHFSA 0:00-23:59\r\n',NULL,NULL,NULL);
insert  into `acl`(`id`,`sysname`,`vname`,`data`,`blacklist`,`whitelist`,`stoplist`) values (2,'time1900','с 19:00 до 9:00 без обеда','acl time1900_time time MTWHF 19:00-23:59\r\nacl time1900_time time MTWHF 0:00-8:59\r\nacl time1900_time time SA 0:00-23:59\r\nacl time1900_blacktime time M 0:00-0:01\r\nacl time1900_type method CONNECT\r\nacl time1900_typetime time MTWHFSA 0:00-23:59\r\n',NULL,NULL,NULL);
insert  into `acl`(`id`,`sysname`,`vname`,`data`,`blacklist`,`whitelist`,`stoplist`) values (3,'ssl','только SSL/ICQ','acl ssl_time time MTWHFSA 0:00-23:59\r\nacl ssl_blacktime time M 0:00-0:01\r\nacl ssl_type method CONNECT\r\nacl ssl_typetime time MTWHFSA 0:00-23:59\r\n',NULL,NULL,NULL);
insert  into `acl`(`id`,`sysname`,`vname`,`data`,`blacklist`,`whitelist`,`stoplist`) values (4,'time0900','фултайм с обедом','acl time0900_time time MTWHFSA 0:00-23:59\r\nacl time0900_blacktime time MTWHF 9:30-12:59\r\nacl time0900_blacktime time MTWHF 14:00-18:30\r\nacl time0900_type method CONNECT\r\nacl time0900_typetime time MTWHFSA 0:00-23:59\r\n',NULL,NULL,NULL);
insert  into `acl`(`id`,`sysname`,`vname`,`data`,`blacklist`,`whitelist`,`stoplist`) values (5,'time0921','с 09:00 до 21:00 с обедом','acl time0921_time time MTWHF 08:59-21:00\r\nacl time0921_blacktime time MTWHF 9:30-12:59\r\nacl time0921_blacktime time MTWHF 14:00-18:30\r\nacl time0921_type method CONNECT\r\nacl time0921_typetime time MTWHFSA 0:00-23:59\r\n','odnoklaskini.ru\r\nvkontakte.ru\r\nloveplanet.ru\r\n','www.gmail.com','lenta.ru\r\n');
insert  into `acl`(`id`,`sysname`,`vname`,`data`,`blacklist`,`whitelist`,`stoplist`) values (6,'fulltime','фултайм без обеда','acl fulltime_time time MTWHFSA 0:00-23:59\r\nacl fulltime_blacktime time M 0:00-0:01\r\nacl fulltime_type method CONNECT\r\nacl fulltime_typetime time MTWHFSA 0:00-23:59\r\n',NULL,NULL,NULL);

UNLOCK TABLES;

/*Data for the table `admins` */

LOCK TABLES `admins` WRITE;

insert into admins (login, passwd, descr) values ('admin', md5('password'), 'sysadmin');
insert  into `admins`(`id`,`login`,`passwd`,`descr`) values (2,'slavik','81198d0d6c036105f859d6301897c52b','developer');

UNLOCK TABLES;

/*Data for the table `detail` */

LOCK TABLES `detail` WRITE;

UNLOCK TABLES;

/*Data for the table `mail` */

LOCK TABLES `mail` WRITE;

UNLOCK TABLES;

/*Data for the table `options` */

LOCK TABLES `options` WRITE;

insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (1,'language','1','язык системы','0 - русский, 1 - английский.');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (2,'megabyte_cost','0','стоимость мегабайта траффика','если 0 то нигде про неё не писать.');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (3,'admin_mail','s@econ.psu.ru','адрес администратора',NULL);
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (4,'domain','russia','доменное имя',NULL);
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (5,'detailed','1','детализированная статистика','0 -нет, 1 - да.');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (6,'delimiter',' ','разделитель разрядов.',NULL);
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (7,'def_timeacl','2','время доступа по  умолчанию.',NULL);
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (8,'std_limit','200000000','лимит по умолчанию.',NULL);
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (9,'create_todo','1','действие при создании','0 - создать и в редактирование, 1 - создать и на главную, 3 - создать и снова на создание.');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (10,'order_main','0','main frame sort order','0-6 sort order');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (11,'order_uhist','1','history sort order','0-6 sort order');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (12,'main_ch','1','use color highlight in user manager','on/off');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (13,'uhist_ch','1','use color highlight in user history','on/off');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (14,'origin','SAcc-181-stable1','webinterface header','=)');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (15,'pagelen','10','length of page','numeric');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (16,'timezone','5','delta from UTC','time offset from UTC');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (17,'cisco','0','we need to show cisco ipacc stat','no/yes');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (18,'addn','OU=users,DC=russia,DC=ru','domain and OU name in AD LDAP notation','in form OU=org_unit,DC=level1name,DC=level0name');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (19,'adlogin','squidauth@russia.ru','AD account to connect with','in form login@realm');
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (20,'adpwd','sacciddqd','AD account password',NULL);
insert  into `options`(`id`,`name`,`value`,`descr`,`help`) values (21,'adip','10.0.0.1','ip of domain controller, only one ip allowed',NULL);

UNLOCK TABLES;

/*Data for the table `queue` */

LOCK TABLES `queue` WRITE;

UNLOCK TABLES;

/*Data for the table `shistory` */

LOCK TABLES `shistory` WRITE;

UNLOCK TABLES;

/*Data for the table `site` */

LOCK TABLES `site` WRITE;

UNLOCK TABLES;

/*Data for the table `sys_trf` */

LOCK TABLES `sys_trf` WRITE;

insert  into `sys_trf`(`offset`,`moffset`,`trf`,`id`) values (390195835,0,0,1);

UNLOCK TABLES;

/*Data for the table `syslog` */

LOCK TABLES `syslog` WRITE;

insert  into `syslog`(`id`,`a_id`,`record`) values (1,NULL,'21.12.2008 02:05:58 admin 192.168.2.230 create new user test (6, 20000000, 0, 1, test)');
insert  into `syslog`(`id`,`a_id`,`record`) values (2,NULL,'21.12.2008 02:13:59 admin 192.168.2.230 Удалил пользователя test:25.');
insert  into `syslog`(`id`,`a_id`,`record`) values (3,NULL,'21.12.2008 02:14:21 admin 192.168.2.230 create new user test (6, 200000000, 0, test, test)');
insert  into `syslog`(`id`,`a_id`,`record`) values (4,NULL,'23.03.2010 13:12:41 admin 192.168.1.10 create new user 111 (2, 200000000, 0, , 123)');
insert  into `syslog`(`id`,`a_id`,`record`) values (5,NULL,'23.03.2010 13:12:57 admin 192.168.1.10 Удалил пользователя 111:27.');

UNLOCK TABLES;

/*Data for the table `tsites` */

LOCK TABLES `tsites` WRITE;

insert  into `tsites`(`id`,`url`,`ad`,`comment`,`tarif`) values (1,'google.com',1,'1',0);
insert  into `tsites`(`id`,`url`,`ad`,`comment`,`tarif`) values (2,'http://mail.google.com/',1,'2',0);
insert  into `tsites`(`id`,`url`,`ad`,`comment`,`tarif`) values (3,'perm.ru',1,'1',3);

UNLOCK TABLES;

/*Data for the table `uhistory` */

LOCK TABLES `uhistory` WRITE;

UNLOCK TABLES;

/*Data for the table `users` */

LOCK TABLES `users` WRITE;

insert into users (login, quota, used, email, descr, timeacl, aid) values ('slavik', 0, 0,'spam@mail.ru','regular user', '',1);
UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
