alter table breach_logs add column bl_reportable tinyint(1) default 0 after bl_description;


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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `breach_logs_questions` */

insert  into `breach_logs_questions`(`blq_id`,`blq_title`,`blq_text`,`blq_order`,`blq_active`,`blq_create_date`,`blq_update_date`) values (1,'Does Breach include PHI?',NULL,1,1,'2014-05-20 10:05:34','0000-00-00 00:00:00'),(2,'Does Breach Include Identifiable Data?',NULL,2,1,'2014-05-20 10:06:56','0000-00-00 00:00:00'),(3,'Can De-idnetified Data be Re-identified?',NULL,3,1,'2014-05-20 10:06:57','0000-00-00 00:00:00'),(4,'Is Recipient Able to Retain the PHI?',NULL,6,1,'2014-05-20 10:07:00','0000-00-00 00:00:00'),(5,'Is Recipient Authorized to See Data?',NULL,5,1,'2014-05-20 10:06:58','0000-00-00 00:00:00'),(6,'Is Recipient Under the Authority of the CE or BA?',NULL,4,1,'2014-05-20 10:06:58','0000-00-00 00:00:00'),(7,'Was the PHI Actually Viewed?',NULL,7,1,'2014-05-20 10:07:01','0000-00-00 00:00:00'),(8,'Can the Risk Be Mitigated Through Distruction of Return?',NULL,8,1,'2014-05-20 10:07:01','0000-00-00 00:00:00'),(9,'Has a Reportable Breach Occurred?',NULL,9,1,'2014-05-20 10:07:03','0000-00-00 00:00:00'),(10,'When is Reporting Required?',NULL,10,1,'2014-05-20 10:07:05','0000-00-00 00:00:00');
