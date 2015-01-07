/*
SQLyog Community Edition- MySQL GUI v8.01 
MySQL - 5.5.8-log : Database - hipaa
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`hipaa` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `hipaa`;

/*Table structure for table `addresses` */

DROP TABLE IF EXISTS `addresses`;

CREATE TABLE `addresses` (
  `adr_id` int(11) NOT NULL AUTO_INCREMENT,
  `adr_address1` text,
  `adr_address2` text,
  `adr_city` varchar(255) DEFAULT NULL,
  `adr_state_id` int(11) DEFAULT NULL,
  `adr_zip` varchar(20) DEFAULT NULL,
  `adr_active` tinyint(1) DEFAULT '1',
  `adr_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adr_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`adr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

/*Data for the table `addresses` */

insert  into `addresses`(`adr_id`,`adr_address1`,`adr_address2`,`adr_city`,`adr_state_id`,`adr_zip`,`adr_active`,`adr_create_date`,`adr_update_date`) values (14,'PRIM','Address 2 HAHAHA','City HAHA',10,'ZIP Code 1 PRIM',1,'2014-05-08 14:36:05','2014-05-08 16:03:51'),(15,'Address 1 1 ion #2','dress 2  1','City 2 2',15,'ZIP Cod 2',1,'2014-05-08 14:36:05','2014-05-11 15:59:28'),(16,'ddress 1 3','ddress 2 3','City 3',34,'ZIP Cod 3 ation #3 ',1,'2014-05-08 14:36:05','2014-05-08 16:03:51'),(17,'loc 4 HAHAHA','Address 2 HAHAHA','City HAHA',10,'ZIP Code 1 PRIM',1,'2014-05-08 16:03:29','2014-05-11 15:59:28'),(18,'PRIM','Address 2 HAHAHA','City HAHA',10,'ZIP Code 1 PRIM',1,'2014-05-08 16:03:51','2014-05-11 15:59:28'),(19,'add111','ddress 2 3','',0,'',1,'2014-05-09 14:10:59','2014-05-09 14:12:45'),(20,'Address TEST1','ddress 2 3','City 2 2',15,'ZIP Cod 2',1,'2014-05-09 14:13:19','2014-05-09 14:13:43'),(21,'Pri adr1','Pri adr2','CIT',17,'ZIP',1,'2014-05-09 14:54:14','2014-05-11 15:37:11'),(22,'Pri adr1','Pri adr2','CIT',17,'ZIP',1,'2014-05-09 16:44:25','2014-05-11 15:37:11'),(23,'Pri adr1','Pri adr2','CIT',17,'ZIP',1,'2014-05-11 12:24:36','2014-05-11 15:37:11'),(24,'dress 4','','',0,'',1,'2014-05-11 12:51:53','2014-05-11 15:37:11'),(25,'ation #5','','',0,'',1,'2014-05-11 12:51:54','2014-05-11 15:37:11'),(26,'ation #6','','',0,'',1,'2014-05-11 12:51:54','2014-05-11 15:37:11');

/*Table structure for table `addresses_items` */

DROP TABLE IF EXISTS `addresses_items`;

CREATE TABLE `addresses_items` (
  `cadr_id` int(11) NOT NULL AUTO_INCREMENT,
  `cadr_type` int(11) DEFAULT NULL,
  `cadr_c_id` int(11) DEFAULT NULL,
  `cadr_adr_id` int(11) DEFAULT NULL,
  `cadr_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`cadr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

/*Data for the table `addresses_items` */

insert  into `addresses_items`(`cadr_id`,`cadr_type`,`cadr_c_id`,`cadr_adr_id`,`cadr_active`) values (9,1,26,14,1),(10,1,28,15,1),(11,1,26,16,1),(12,1,28,17,1),(13,1,28,18,1),(14,1,29,19,1),(15,1,30,20,1),(16,1,1,21,1),(17,1,1,22,1),(18,1,1,23,1),(19,1,1,24,1),(20,1,1,25,1),(21,1,1,26,1);

/*Table structure for table `business_associates` */

DROP TABLE IF EXISTS `business_associates`;

CREATE TABLE `business_associates` (
  `ba_id` int(11) NOT NULL AUTO_INCREMENT,
  `ba_contact_u_id` int(11) DEFAULT NULL,
  `ba_consultant_u_id` int(11) DEFAULT NULL,
  `ba_name` varchar(100) DEFAULT NULL,
  `ba_office_phone` varchar(50) DEFAULT NULL,
  `ba_office_phone_inner` varchar(50) DEFAULT NULL,
  `ba_direct_phone` varchar(50) DEFAULT NULL,
  `ba_direct_phone_inner` varchar(50) DEFAULT NULL,
  `ba_other_phone` varchar(50) DEFAULT NULL,
  `ba_other_phone_inner` varchar(50) DEFAULT NULL,
  `ba_fax` varchar(50) DEFAULT NULL,
  `ba_email` varchar(100) DEFAULT NULL,
  `ba_website` varchar(100) DEFAULT NULL,
  `ba_address1` varchar(255) DEFAULT NULL,
  `ba_address2` varchar(255) DEFAULT NULL,
  `ba_state_id` int(11) DEFAULT NULL,
  `ba_city` varchar(255) DEFAULT NULL,
  `ba_zip` varchar(255) DEFAULT NULL,
  `ba_phone_call_status` tinyint(1) DEFAULT '0',
  `ba_agreement_status` tinyint(1) DEFAULT '0',
  `ba_assessment_invited_status` tinyint(1) DEFAULT '0',
  `ba_assessment_completed_status` tinyint(1) DEFAULT '0',
  `ba_hipaa_compliant_status` tinyint(1) DEFAULT '0',
  `ba_active` tinyint(1) DEFAULT '1',
  `ba_status` tinyint(1) DEFAULT '0',
  `ba_create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_update_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ba_id`),
  KEY `fk_ba_states` (`ba_state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `business_associates` */

insert  into `business_associates`(`ba_id`,`ba_contact_u_id`,`ba_consultant_u_id`,`ba_name`,`ba_office_phone`,`ba_office_phone_inner`,`ba_direct_phone`,`ba_direct_phone_inner`,`ba_other_phone`,`ba_other_phone_inner`,`ba_fax`,`ba_email`,`ba_website`,`ba_address1`,`ba_address2`,`ba_state_id`,`ba_city`,`ba_zip`,`ba_phone_call_status`,`ba_agreement_status`,`ba_assessment_invited_status`,`ba_assessment_completed_status`,`ba_hipaa_compliant_status`,`ba_active`,`ba_status`,`ba_create_date`,`ba_update_date`) values (1,20,13,'Ba name','','','','','','','','','','','',0,'','',1,1,1,0,1,1,NULL,'2014-05-14 16:09:57','2014-05-15 11:52:47');

/*Table structure for table `companies` */

DROP TABLE IF EXISTS `companies`;

CREATE TABLE `companies` (
  `c_id` int(11) NOT NULL AUTO_INCREMENT,
  `c_consultant_u_id` int(11) DEFAULT NULL,
  `c_primary_contact_u_id` int(11) DEFAULT NULL,
  `c_name` varchar(255) DEFAULT NULL,
  `c_email` varchar(100) DEFAULT NULL,
  `c_phone` varchar(50) DEFAULT NULL,
  `c_other_phone` varchar(50) DEFAULT NULL,
  `c_other_phone_inner` varchar(50) DEFAULT NULL,
  `c_fax` varchar(50) DEFAULT NULL,
  `c_website` varchar(100) DEFAULT NULL,
  `c_primary_adr_id` int(11) DEFAULT NULL,
  `c_active` tinyint(1) DEFAULT '1',
  `c_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `c_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`c_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

/*Data for the table `companies` */

insert  into `companies`(`c_id`,`c_consultant_u_id`,`c_primary_contact_u_id`,`c_name`,`c_email`,`c_phone`,`c_other_phone`,`c_other_phone_inner`,`c_fax`,`c_website`,`c_primary_adr_id`,`c_active`,`c_create_date`,`c_update_date`) values (1,13,18,'Company 1','Argentina@email.com','(436) 123-6346','123424','999','fddsfsdf','dfsfsdf',21,1,'2014-05-07 14:24:31','2014-05-11 15:37:10'),(2,NULL,NULL,'fgdgd',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2014-05-07 14:38:34','0000-00-00 00:00:00'),(3,NULL,NULL,'3dfgdg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2014-05-07 14:38:38','0000-00-00 00:00:00'),(4,NULL,NULL,'Company 2',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2014-05-07 14:38:40','0000-00-00 00:00:00'),(5,NULL,NULL,'5jhjhgj',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2014-05-07 14:38:42','0000-00-00 00:00:00'),(6,13,NULL,'Xompany 3','','','','','','',NULL,1,'2014-05-07 14:38:44','2014-05-12 14:49:18'),(7,NULL,NULL,'7ukujkh',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2014-05-07 14:38:55','0000-00-00 00:00:00'),(8,NULL,NULL,'pany nam','','','',NULL,'','',NULL,0,'2014-05-08 13:54:48','0000-00-00 00:00:00'),(9,NULL,NULL,'AAA','','','',NULL,'','',NULL,0,'2014-05-08 13:56:53','0000-00-00 00:00:00'),(28,13,NULL,'Company 4','','(436) 321-6346','','','','',15,1,'2014-05-08 14:36:05','2014-05-11 15:59:28'),(29,13,NULL,'COMpany NOVA 1','france11@email.com','123321123','',NULL,'','',NULL,1,'2014-05-09 14:10:59','2014-05-09 14:12:45'),(30,13,10,'COMpany NOVA 2','france@email.com','213','123424',NULL,'fddsfsdf','google.com',NULL,1,'2014-05-09 14:13:19','2014-05-09 14:13:43'),(31,13,NULL,'hahaha','','','',NULL,'','',NULL,0,'2014-05-09 14:37:35','0000-00-00 00:00:00');

/*Table structure for table `files` */

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `f_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `f_name` varchar(255) DEFAULT NULL,
  `f_type` varchar(255) DEFAULT NULL,
  `f_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `files` */

insert  into `files`(`f_id`,`f_name`,`f_type`,`f_create_date`) values (1,'credit card example.txt','text/plain','2014-05-13 14:16:02'),(2,'db_flexishore.txt','text/plain','2014-05-13 14:16:02'),(3,'PIT-36(19)_v1-0E_2013.pdf','application/x-download','2014-05-13 14:22:28'),(4,'2014-05-05-CYF-MODIFICATIONREQUEST-1TR2014-VALIDATED-EN.pptx','application/vnd.openxmlformats-officedocument.presentationml.presentation','2014-05-13 14:22:28'),(5,'BOCH Tomasz CV.pdf','application/x-download','2014-05-13 14:22:28'),(6,'BOCH Tomasz CV.pdf','application/x-download','2014-05-14 15:33:34'),(7,'BOCH Tomasz CV - en.doc','application/unknown','2014-05-14 15:33:34'),(8,'BOCH Tomasz CV - C++.doc','application/unknown','2014-05-15 15:57:48'),(9,'BOCH Tomasz CV - C++.pdf','application/x-download','2014-05-15 15:57:49'),(10,'BOCH Tomasz CV.pdf','application/x-download','2014-05-15 16:16:54');

/*Table structure for table `mail_templates` */

DROP TABLE IF EXISTS `mail_templates`;

CREATE TABLE `mail_templates` (
  `mt_id` int(11) NOT NULL AUTO_INCREMENT,
  `mt_name` varchar(255) DEFAULT NULL,
  `mt_key` varchar(255) DEFAULT NULL,
  `mt_text` text,
  `mt_active` tinyint(1) DEFAULT '0',
  `mt_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `mail_templates` */

insert  into `mail_templates`(`mt_id`,`mt_name`,`mt_key`,`mt_text`,`mt_active`,`mt_create_date`) values (1,'Schedule Call with Business Associate','schedulecall','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\nDear &lt;Business Associate&gt;,\r\n\r\nCarosh Compliance Solutions has been retained by «Company» to conduct an audit of their compliance with HIPAA regulations, as amended by the Final Omnibus Rules.  One of the areas covered in the Final Omnibus Rules relates to Business Associates.  As part of our work for «Company»  we are reviewing «Company»’s   Business Associate relationships.  To this end, we would like to schedule a call with you at your earliest possible convenience within the next 2 weeks.  The purpose of this call will be to determine if you are in fact classified correctly as a Business Associate.\r\n\r\nPlease be assured we make every attempt to make this process as painless as possible.  If you have any questions, please do not hesitate to contact me.\r\n\r\nThanks you in advance for your assistance.\r\n\r\nSincerely,\r\n«Consultant\'s Name»\r\n«Consultant\'s Title»\r\nThe Carosh Group',0,'2014-05-15 16:28:15'),(2,'Invite Business Associate to Assessment','invitetoassessment','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\nDear &lt;Business Associate&gt;,\r\n\r\nCarosh Compliance Solutions has been retained by «Company» to conduct an audit of their compliance with HIPAA regulations, as amended by the Final Omnibus Rules. One of the areas covered in the Final Omnibus Rules relates to Business Associates. As part of our work for «Company» we are reviewing «Company»’s Business Associate relationships.\r\n\r\nLogin page: https://hipaa.carosh.com/\r\nUsername: &lt;username&gt;\r\nPassword: &lt;password&gt;\r\n\r\nWe determined that you are classified as a Business Associate, and on behalf of «Company» we will need to conduct additional due diligence on your compliance with the HIPAA Regulations, again as mandated by the Final Omnibus Rules. To help us with this process we would like to have you fill out the attached questionair, and provide us with the following:\r\n\r\n	•	The signature pages of your most recent Security Risk Assessment and Remediation Plan,\r\n	•	The signature page of your HIPAA Master Policy and Procedure Manual, and \r\n	•	A sample of your most recent training logs.\r\n\r\nFinally, subsequent to the due diligence effort, we will need to discuss the changes in the Business Associates Agreement, and execute a new Business Associates Agreement, as mandated by the Final Omnibus Rules, effective as of September 23, 2013.\r\n\r\nOne of our project managers will contact you to set up a time we can meet with you to review the materials and subsequently discuss and execute the revised Business Associate Agreement.\r\n\r\nPlease be assured we make every attempt to make this process as painless as possible. If you have any questions, please do not hesitate to contact me.\r\n\r\nThanks you in advance for your assistance.\r\n\r\nSincerely,\r\n«Consultant\'s Name»\r\n«Consultant\'s Title»\r\nThe Carosh Group',0,'2014-05-15 16:33:04');

/*Table structure for table `notes` */

DROP TABLE IF EXISTS `notes`;

CREATE TABLE `notes` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `note_text` text,
  `note_u_id` int(11) DEFAULT NULL,
  `note_item_type` int(11) DEFAULT NULL,
  `note_item_id` int(11) DEFAULT NULL,
  `note_active` tinyint(1) DEFAULT '1',
  `note_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

/*Data for the table `notes` */

insert  into `notes`(`note_id`,`note_text`,`note_u_id`,`note_item_type`,`note_item_id`,`note_active`,`note_create_date`) values (1,'Lorem Ipsum jest tekstem stosowanym jako przykładowy wypełniacz w przemyśle poligraficznym. Został po raz pierwszy użyty w XV w. przez nieznanego drukarza do wypełnienia tekstem próbnej książki. Pięć wieków później zaczął być używany przemyśle elektronicznym, pozostając praktycznie niezmienionym. Spopularyzował się w latach 60. XX w. wraz z publikacją arkuszy Letrasetu, zawierających fragmenty Lorem Ipsum, a ostatnio z zawierającym różne wersje Lorem Ipsum oprogramowaniem przeznaczonym do realizacji druków na komputerach osobistych, jak Aldus PageMaker\r\n\r\nOgólnie znana teza głosi, iż użytkownika może rozpraszać zrozumiała zawartość strony, kiedy ten chce zobaczyć sam jej wygląd. Jedną z mocnych stron używania Lorem Ipsum jest to, że ma wiele różnych „kombinacji” zdań, słów i akapitów, w przeciwieństwie do zwykłego: „tekst, tekst, tekst”, sprawiającego, że wygląda to „zbyt czytelnie” po polsku. Wielu webmasterów i designerów używa Lorem Ipsum jako domyślnego modelu tekstu i wpisanie w internetowej wyszukiwarce ‘lorem ipsum’ spowoduje znalezienie bardzo wielu stron, które wciąż są w budowie. Wiele wersji tekstu ewoluowało i zmieniało się przez lata, czasem przez przypadek, czasem specjalnie (humorystyczne wstawki itd).',13,1,28,1,'2014-05-11 21:51:05'),(2,'padkowym tekstem. Ma ono korzenie w klasycznej łacińskiej literaturze z 45 roku przed Chrystusem, czyli ponad 2000 lat temu! Richard McClintock, wykładowca łaciny na uniwersytecie Hampden-Sydney w Virginii, przyjrzał się uważniej jednemu z najbardziej niejasnych słów w Lorem Ipsum – consectetur – i po wielu poszukiwaniach odnalazł niezaprzeczalne źródło: Lorem Ipsum pochodzi z fragmentów (1.10.32 i 1.10.33) „de Finibus Bonorum et Malorum”, czyli „O granicy dobra i zła”, napisanej właśnie w 45 p.n.e. przez Cycerona. Jest to bardzo p',13,1,1,1,'2014-05-11 21:52:49'),(3,'ość, że nie ma niczego „dziwnego” w środku tekstu. Wszystkie Internetowe generatory Lorem Ipsum mają tendencje do kopiowania już istniejących bloków, co czyni nasz pierwszym prawdziwym generatorem w Internecie. U',13,1,1,1,'2014-05-11 22:05:47'),(4,'asdhsaihdasd',13,1,1,1,'2014-05-12 13:42:19'),(15,'dd a note about this Compan',13,1,6,1,'2014-05-13 14:16:02'),(16,'asd',13,1,6,1,'2014-05-13 14:22:28'),(17,'haha',13,1,6,1,'2014-05-13 15:23:08'),(18,'haha',13,1,6,1,'2014-05-13 15:23:42'),(19,'XXX',13,1,6,1,'2014-05-14 15:33:34'),(20,'PARDOn',13,2,1,1,'2014-05-15 15:57:48'),(21,'fdgfdg',13,1,1,1,'2014-05-15 16:16:53');

/*Table structure for table `notes_files` */

DROP TABLE IF EXISTS `notes_files`;

CREATE TABLE `notes_files` (
  `nf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nf_f_id` int(11) DEFAULT NULL,
  `nf_note_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`nf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `notes_files` */

insert  into `notes_files`(`nf_id`,`nf_f_id`,`nf_note_id`) values (1,1,15),(2,2,15),(3,3,16),(4,4,16),(5,5,16),(6,6,19),(7,7,19),(8,8,20),(9,9,20),(10,10,21);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) DEFAULT NULL,
  `role_create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `roles` */

insert  into `roles`(`role_id`,`role_name`,`role_create_date`,`role_active`) values (1,'Administrator','2013-11-05 13:28:35',1),(2,'Senior Consultant','2013-10-24 09:33:08',1),(3,'Consultant','2013-11-05 13:28:46',1),(4,'Sales Rep','2014-04-28 10:59:45',1),(5,'Client','2014-04-28 10:59:50',1),(6,'Business Associate','2014-04-28 11:01:39',1);

/*Table structure for table `states` */

DROP TABLE IF EXISTS `states`;

CREATE TABLE `states` (
  `state_id` int(11) NOT NULL AUTO_INCREMENT,
  `state_name` char(40) NOT NULL,
  `state_code` char(2) NOT NULL,
  PRIMARY KEY (`state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

/*Data for the table `states` */

insert  into `states`(`state_id`,`state_name`,`state_code`) values (1,'Alaska','AK'),(2,'Alabama','AL'),(3,'American Samoa','AS'),(4,'Arizona','AZ'),(5,'Arkansas','AR'),(6,'California','CA'),(7,'Colorado','CO'),(8,'Connecticut','CT'),(9,'Delaware','DE'),(10,'District of Columbia','DC'),(11,'Federated States of Micronesia','FM'),(12,'Florida','FL'),(13,'Georgia','GA'),(14,'Guam','GU'),(15,'Hawaii','HI'),(16,'Idaho','ID'),(17,'Illinois','IL'),(18,'Indiana','IN'),(19,'Iowa','IA'),(20,'Kansas','KS'),(21,'Kentucky','KY'),(22,'Louisiana','LA'),(23,'Maine','ME'),(24,'Marshall Islands','MH'),(25,'Maryland','MD'),(26,'Massachusetts','MA'),(27,'Michigan','MI'),(28,'Minnesota','MN'),(29,'Mississippi','MS'),(30,'Missouri','MO'),(31,'Montana','MT'),(32,'Nebraska','NE'),(33,'Nevada','NV'),(34,'New Hampshire','NH'),(35,'New Jersey','NJ'),(36,'New Mexico','NM'),(37,'New York','NY'),(38,'North Carolina','NC'),(39,'North Dakota','ND'),(40,'Northern Mariana Islands','MP'),(41,'Ohio','OH'),(42,'Oklahoma','OK'),(43,'Oregon','OR'),(44,'Palau','PW'),(45,'Pennsylvania','PA'),(46,'Puerto Rico','PR'),(47,'Rhode Island','RI'),(48,'South Carolina','SC'),(49,'South Dakota','SD'),(50,'Tennessee','TN'),(51,'Texas','TX'),(52,'Utah','UT'),(53,'Vermont','VT'),(54,'Virgin Islands','VI'),(55,'Virginia','VA'),(56,'Washington','WA'),(57,'West Virginia','WV'),(58,'Wisconsin','WI'),(59,'Wyoming','WY'),(60,'Armed Forces Africa','AE'),(61,'Armed Forces Americas (except Canada)','AA'),(62,'Armed Forces Canada','AE'),(63,'Armed Forces Europe','AE'),(64,'Armed Forces Middle East','AE'),(65,'Armed Forces Pacific','AP');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_role_id` int(11) NOT NULL,
  `u_senior_consultant_u_id` int(11) DEFAULT NULL,
  `u_company_id` int(11) DEFAULT NULL,
  `u_password` varchar(40) DEFAULT NULL,
  `u_firstname` varchar(100) DEFAULT NULL,
  `u_lastname` varchar(100) DEFAULT NULL,
  `u_title` varchar(100) DEFAULT NULL,
  `u_company` varchar(100) DEFAULT NULL,
  `u_email` varchar(100) DEFAULT NULL,
  `u_office_phone` varchar(50) DEFAULT NULL,
  `u_office_phone_inner` varchar(50) DEFAULT NULL,
  `u_direct_phone` varchar(50) DEFAULT NULL,
  `u_direct_phone_inner` varchar(50) DEFAULT NULL,
  `u_cell_phone` varchar(50) DEFAULT NULL,
  `u_other_phone` varchar(50) DEFAULT NULL,
  `u_other_phone_inner` varchar(50) DEFAULT NULL,
  `u_fax` varchar(50) DEFAULT NULL,
  `u_address1` varchar(255) DEFAULT NULL,
  `u_address2` varchar(255) DEFAULT NULL,
  `u_state_id` int(11) DEFAULT NULL,
  `u_city` varchar(255) DEFAULT NULL,
  `u_zip` varchar(255) DEFAULT NULL,
  `u_active` tinyint(1) DEFAULT '1',
  `u_hash` varchar(100) DEFAULT NULL,
  `u_status` tinyint(1) DEFAULT '0',
  `u_create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `u_update_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`u_id`),
  KEY `fk_users_roles1` (`u_role_id`),
  KEY `fk_users_cons_users` (`u_senior_consultant_u_id`),
  KEY `fk_users_states` (`u_state_id`),
  CONSTRAINT `fk_users_cons_users` FOREIGN KEY (`u_senior_consultant_u_id`) REFERENCES `users` (`u_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_roles1` FOREIGN KEY (`u_role_id`) REFERENCES `roles` (`role_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_states` FOREIGN KEY (`u_state_id`) REFERENCES `states` (`state_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`u_id`,`u_role_id`,`u_senior_consultant_u_id`,`u_company_id`,`u_password`,`u_firstname`,`u_lastname`,`u_title`,`u_company`,`u_email`,`u_office_phone`,`u_office_phone_inner`,`u_direct_phone`,`u_direct_phone_inner`,`u_cell_phone`,`u_other_phone`,`u_other_phone_inner`,`u_fax`,`u_address1`,`u_address2`,`u_state_id`,`u_city`,`u_zip`,`u_active`,`u_hash`,`u_status`,`u_create_date`,`u_update_date`) values (5,1,NULL,NULL,'f865b53623b121fd34ee5426c792e5c33af8c227','te','be',NULL,NULL,'admin@hipaa.com','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,1,NULL,0,'2014-05-05 14:03:35',NULL),(6,2,NULL,NULL,NULL,'te1','Last','','','admin@hipaa2.com','2','444','direct','555','CELL','othger ','999','','','',NULL,'','',1,NULL,0,'2014-05-05 14:16:11',NULL),(7,5,11,6,NULL,'te2','sdf','','','admin@hipaa3.com','3',NULL,'',NULL,'','',NULL,'','','',15,'','',0,NULL,0,'2014-05-05 14:16:13',NULL),(8,5,NULL,6,NULL,'te3',NULL,NULL,NULL,'admin@hipaa4.com','4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,'2014-05-05 14:16:15',NULL),(9,5,NULL,6,NULL,'te4',NULL,NULL,NULL,'admin@hipaa5.com','5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,'2014-05-05 14:16:17',NULL),(10,5,NULL,NULL,NULL,'te5','be5','','','admin@hipaa6.com','6','','','','','','','','','',NULL,'','',1,NULL,0,'2014-05-05 14:16:19',NULL),(11,5,7,28,NULL,'te6','asdasd','','','admin@hipaa7.com','6',NULL,'',NULL,'','',NULL,'','','',NULL,'','',0,NULL,0,'2014-05-05 14:16:21',NULL),(12,1,NULL,NULL,NULL,'ter','ber','','','admin2@hipaa.com','7',NULL,'',NULL,'','',NULL,'','','',NULL,'','',1,'d5907034e7b00fdee72106b83bcc297cf6936f6e',0,'2014-05-05 16:19:49',NULL),(13,3,6,NULL,'f865b53623b121fd34ee5426c792e5c33af8c227','Consultanto','Consultanto','Consultanto','Consultanto','consultanto@hipaa.com','123321',NULL,'123321',NULL,'123321','123321',NULL,'123321','','',NULL,'','',1,'cadee445fe06f303b01cf5451736afda9e3c0732',0,'2014-05-07 12:11:34',NULL),(17,5,NULL,NULL,NULL,'Robert','Brown','Title','','client2@hipaa.com','(436) 436-6346',NULL,'123321',NULL,'123321','123321',NULL,'','','',NULL,'','',1,'56a7497fbd1fdc873fe2da13f35c9c441e9b26fd',0,'2014-05-09 11:09:54',NULL),(18,5,NULL,1,NULL,'Jessica','Kindell','Consultanto',NULL,'tb@tb.pl','(436) 436-6346','123','123321','234','123321','123321','345','123321','Pri adr1','Pri adr2',17,'CIT','ZIP',1,'c2a22340ceaee23a64932eaf59e5ce1e5b00ddf3',0,'2014-05-09 11:14:15',NULL),(19,5,NULL,30,NULL,'Consultanto COmpany NOVA 2','Consultanto COmpany NOVA 2','Title COmpany NOVA 2',NULL,'nova2@hipaa.com','','','','','','','','','','',NULL,'','',1,'fdc52781d18d7e0e0c3eac46e6d9044932afe700',0,'2014-05-12 14:43:00',NULL),(20,6,NULL,NULL,NULL,'te','be','titi',NULL,'ba@hipaa.com','off','','','','','','','',NULL,NULL,NULL,NULL,NULL,1,'03ad87609584ec521e6ae855ce2e4aec64c27676',0,'2014-05-15 11:52:01',NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
