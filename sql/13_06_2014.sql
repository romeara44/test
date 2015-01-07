CREATE TABLE `assessments_questions` (                                
                         `aq_id` int(11) NOT NULL AUTO_INCREMENT,                            
			                   `aq_parent_aq_id` int(11),
			                   `aq_type` int(11),
                         `aq_title` text,                                                    
                         `aq_text` text,                                                     
                         `aq_order` int(11) DEFAULT NULL,                                    
                         `aq_active` tinyint(1) DEFAULT '1',                                 
                         `aq_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,      
                         `aq_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',  
                         PRIMARY KEY (`aq_id`)                                               
                       ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `assessments_questions_options` (
                                 `aqo_id` int(11) NOT NULL AUTO_INCREMENT,
                                 `aqo_aq_id` int(11),
                                 `aqo_title` text,
                                 `aqo_order` int(11) DEFAULT NULL,
                                 `aqo_active` tinyint(1) DEFAULT '1',
                                 `aqo_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 `aqo_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                                 PRIMARY KEY (`aqo_id`)
                               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `assessments_questions_answers` (
                                 `aqa_id` int(11) NOT NULL AUTO_INCREMENT,
                                 `aqa_a_id` text,
                                 `aqa_aq_id` text,
                                 `aqa_aqo_id` text,
                                 `aqa_u_id` text,
                                 `aqa_order` int(11) DEFAULT NULL,
                                 `aqa_active` tinyint(1) DEFAULT '1',
                                 `aqa_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 `aqa_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                                 PRIMARY KEY (`aqa_id`)
                               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert  into `assessments_questions`(`aq_id`,`aq_parent_aq_id`,`aq_type`,`aq_title`,`aq_text`,`aq_order`,`aq_active`,`aq_create_date`,`aq_update_date`) values (1,NULL,1,'Does the practice know the specifications by which it has to determine how it will comply with the standards?',NULL,1,1,'2014-06-16 21:35:10','0000-00-00 00:00:00'),(2,NULL,1,'Does the practice have a clear understanding of what \"required\" specifications are as defined in the HIPAA Security Regulations?',NULL,2,1,'2014-06-16 21:35:12','0000-00-00 00:00:00'),(3,NULL,1,'Does the practice have a clear understanding of what \"addressable\" standards are as defined in the HIPAA Security Regulations?',NULL,3,1,'2014-06-16 21:35:15','0000-00-00 00:00:00');

/*Data for the table `assessments_questions_options` */

insert  into `assessments_questions_options`(`aqo_id`,`aqo_aq_id`,`aqo_title`,`aqo_order`,`aqo_active`,`aqo_create_date`,`aqo_update_date`) values (1,1,'Yes1',1,1,'2014-06-16 21:48:36','0000-00-00 00:00:00'),(2,1,'NO',2,1,'2014-06-16 21:48:44','0000-00-00 00:00:00'),(3,1,'n/A',3,1,'2014-06-16 21:48:59','0000-00-00 00:00:00'),(4,2,'y2',1,1,'2014-06-16 21:49:14','0000-00-00 00:00:00'),(5,2,'n2',2,1,'2014-06-16 21:49:18','0000-00-00 00:00:00'),(6,3,'y1',3,1,'2014-06-16 21:49:24','0000-00-00 00:00:00'),(7,3,'No3',2,1,'2014-06-16 21:49:32','0000-00-00 00:00:00'),(8,3,'NA3',4,1,'2014-06-16 21:49:38','0000-00-00 00:00:00'),(9,3,'NAA3',1,1,'2014-06-16 21:49:44','0000-00-00 00:00:00');
