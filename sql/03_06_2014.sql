
CREATE TABLE `assessments_inventory` (
  `ai_id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_name` varchar(255) DEFAULT NULL,
  `ai_order` int(11) DEFAULT NULL,
  `ai_active` tinyint(1) DEFAULT '1',
  `ai_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ai_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `assessments_inventory` */

insert  into `assessments_inventory`(`ai_id`,`ai_name`,`ai_order`,`ai_active`,`ai_create_date`) values (1,'ePHI Servers',1,1,'2014-06-03 15:33:24'),(2,'Network Devices',2,1,'2014-06-03 15:33:31'),(3,'ePHI Repository Summary',3,1,'2014-06-03 15:33:40'),(4,'ISP, WAN and Data Circuit Summary',4,1,'2014-06-03 15:33:41');


CREATE TABLE `assessments_inventory_locations_items` (
                                        `aili_id` int(11) NOT NULL AUTO_INCREMENT,
                                        `aili_a_id` int(11) DEFAULT NULL,
                                        `aili_adr_id` int(11) DEFAULT NULL,
                                        `aili_ai_id` int(11) DEFAULT NULL,
                                        `aili_name` text,
                                        `aili_model` text,
                                        `aili_description` text,
                                        `aili_create_u_id` int(11) DEFAULT NULL,
                                        `aili_update_u_id` int(11) DEFAULT NULL,
                                        `aili_active` tinyint(1) DEFAULT '1',
                                        `aili_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                        `aili_update_date` datetime DEFAULT NULL,
                                        PRIMARY KEY (`aili_id`)
                                      ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;