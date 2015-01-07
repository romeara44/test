alter table addresses add column adr_name varchar(255) after adr_zip;
alter table addresses add column adr_phone varchar(255) after adr_name;
alter table addresses add column adr_phone_inner varchar(255) after adr_phone;
alter table addresses add column adr_other_phone varchar(255) after adr_phone_inner;
alter table addresses add column adr_other_phone_inner varchar(255) after adr_other_phone;
alter table addresses add column adr_fax varchar(255) after adr_other_phone_inner;
alter table addresses add column adr_email varchar(255) after adr_fax;


DROP TABLE IF EXISTS `assessments_roles`;

CREATE TABLE `assessments_roles` (
  `ar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ar_name` varchar(255) DEFAULT NULL,
  `ar_order` int(11) DEFAULT NULL,
  `ar_active` tinyint(1) DEFAULT '1',
  `ar_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ar_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `assessments_roles` */

insert  into `assessments_roles`(`ar_id`,`ar_name`,`ar_order`,`ar_active`,`ar_create_date`) values (1,'HR Director',1,1,'2014-05-30 11:15:52'),(2,'EMR Administrator',2,1,'2014-05-30 11:16:00'),(3,'IT Network Manager',3,1,'2014-05-30 11:16:07'),(4,'IT Server Manager',4,1,'2014-05-30 11:16:30'),(5,'HIPAA Security Officer',5,1,'2014-05-30 11:16:41'),(6,'Facilities Manager',6,1,'2014-05-30 11:16:43');

/*Table structure for table `assessments_roles_locations_contacts` */

DROP TABLE IF EXISTS `assessments_roles_locations_contacts`;

CREATE TABLE `assessments_roles_locations_contacts` (
  `arlc_id` int(11) NOT NULL AUTO_INCREMENT,
  `arlc_a_id` int(11) DEFAULT NULL,
  `arlc_adr_id` int(11) DEFAULT NULL,
  `arlc_ar_id` int(11) DEFAULT NULL,
  `arlc_u_id` int(11) DEFAULT NULL,
  `arlc_create_u_id` int(11) DEFAULT NULL,
  `arlc_update_u_id` int(11) DEFAULT NULL,
  `arlc_active` tinyint(1) DEFAULT '1',
  `arlc_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `arlc_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`arlc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

/*Data for the table `assessments_roles_locations_contacts` */

insert  into `assessments_roles_locations_contacts`(`arlc_id`,`arlc_a_id`,`arlc_adr_id`,`arlc_ar_id`,`arlc_u_id`,`arlc_create_u_id`,`arlc_update_u_id`,`arlc_active`,`arlc_create_date`,`arlc_update_date`) values (8,8,27,1,8,13,13,1,'2014-05-30 13:39:40','2014-05-30 13:51:09'),(9,8,27,2,9,13,13,1,'2014-05-30 13:39:40','2014-05-30 13:51:09'),(10,8,27,3,9,13,13,1,'2014-05-30 13:39:40','2014-05-30 13:51:09'),(11,8,27,4,8,13,13,1,'2014-05-30 13:39:40','2014-05-30 13:51:10'),(12,8,27,5,7,13,13,1,'2014-05-30 13:39:40','2014-05-30 13:51:10'),(13,8,27,6,9,13,13,1,'2014-05-30 13:39:40','2014-05-30 13:51:10'),(33,8,28,1,7,13,13,1,'2014-05-30 13:51:56','2014-05-30 13:52:16'),(34,8,28,2,7,13,13,1,'2014-05-30 13:51:56','2014-05-30 13:52:16'),(35,8,28,3,7,13,13,1,'2014-05-30 13:51:57','2014-05-30 13:52:16'),(36,8,28,4,7,13,13,1,'2014-05-30 13:51:57','2014-05-30 13:52:16'),(37,8,28,5,7,13,13,1,'2014-05-30 13:51:57','2014-05-30 13:52:16'),(38,8,28,6,7,13,13,1,'2014-05-30 13:51:57','2014-05-30 13:52:16');
