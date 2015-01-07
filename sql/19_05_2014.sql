/*
SQLyog Community Edition- MySQL GUI v8.01 
MySQL - 5.5.24-log : Database - hipaa
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`hipaa` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `hipaa`;

/*Table structure for table `breach_logs` */

DROP TABLE IF EXISTS `breach_logs`;

CREATE TABLE `breach_logs` (
  `bl_id` int(11) NOT NULL AUTO_INCREMENT,
  `bl_consultant_u_id` int(11) DEFAULT NULL,
  `bl_c_id` int(11) DEFAULT NULL,
  `bl_name` varchar(255) DEFAULT NULL,
  `bl_date_of_occurrence` datetime DEFAULT NULL,
  `bl_size` varchar(255) DEFAULT NULL,
  `bl_description` text,
  `bl_active` tinyint(1) DEFAULT '1',
  `bl_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bl_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`bl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `breach_logs` */

insert  into `breach_logs`(`bl_id`,`bl_consultant_u_id`,`bl_c_id`,`bl_name`,`bl_date_of_occurrence`,`bl_size`,`bl_description`,`bl_active`,`bl_create_date`,`bl_update_date`) values (1,13,29,'CXD','2014-05-19 00:00:00','sizeX','desc breach',1,'2014-05-19 22:41:12','2014-05-19 22:54:35');

/*Table structure for table `breach_logs_answers` */

DROP TABLE IF EXISTS `breach_logs_answers`;

CREATE TABLE `breach_logs_answers` (
  `bla_id` int(11) NOT NULL AUTO_INCREMENT,
  `bla_blq_id` int(11) DEFAULT NULL,
  `bla_bl_id` int(11) DEFAULT NULL,
  `bla_u_id` int(11) DEFAULT NULL,
  `bla_value` int(11) DEFAULT NULL,
  `bla_active` tinyint(1) DEFAULT '1',
  `bla_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bla_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `breach_logs_answers` */

/*Table structure for table `breach_logs_questions` */

DROP TABLE IF EXISTS `breach_logs_questions`;

CREATE TABLE `breach_logs_questions` (
  `blq_id` int(11) NOT NULL AUTO_INCREMENT,
  `blq_title` text,
  `blq_text` text,
  `blq_order` int(11) DEFAULT NULL,
  `blq_active` tinyint(1) DEFAULT '1',
  `blq_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blq_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`blq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `breach_logs_questions` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
