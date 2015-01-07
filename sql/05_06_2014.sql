CREATE TABLE `assessments_inventory_locations_reports` (              
                                         `ailr_id` int(11) NOT NULL AUTO_INCREMENT,                        
                                         `ailr_a_id` int(11) DEFAULT NULL,                                 
                                         `ailr_adr_id` int(11) DEFAULT NULL,                               
                                         `ailr_ai_id` int(11) DEFAULT NULL,                                
                                         `ailr_f_id` text,                                                 
                                         `ailr_create_u_id` int(11) DEFAULT NULL,                          
                                         `ailr_update_u_id` int(11) DEFAULT NULL,                          
                                         `ailr_active` tinyint(1) DEFAULT '1',                             
                                         `ailr_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  
                                         `ailr_update_date` datetime DEFAULT NULL,                         
                                         PRIMARY KEY (`ailr_id`)                                           
                                       ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

                       CREATE TABLE `assessments_business_associates_locations` (
                         `abal_id` int(11) NOT NULL AUTO_INCREMENT,
                         `abal_a_id` int(11) DEFAULT NULL,
                         `abal_adr_id` int(11) DEFAULT NULL,
                         `abal_ba_id` int(11) DEFAULT NULL,
                         `abal_create_u_id` int(11) DEFAULT NULL,
                         `abal_update_u_id` int(11) DEFAULT NULL,
                         `abal_active` tinyint(1) DEFAULT '1',
                         `abal_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `abal_update_date` datetime DEFAULT NULL,
                         PRIMARY KEY (`abal_id`)
                       ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
