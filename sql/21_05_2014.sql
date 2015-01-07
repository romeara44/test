CREATE TABLE `breach_remediation_plans` (                          
                            `brp_id` int(11) NOT NULL AUTO_INCREMENT,                        
                            `brp_bl_id` int(11) DEFAULT NULL,                                
                            `brp_c_id` int(11) DEFAULT NULL,                                 
                            `brp_consultant_u_id` int(11) DEFAULT NULL,                      
                            `brp_is_version` tinyint(1) DEFAULT '0',                         
                            `brp_parent_brp_id` int(11) DEFAULT NULL,                        
                            `brp_status` int(11) DEFAULT '10',                               
                            `brp_incident_date` date DEFAULT NULL,                           
                            `brp_remediation_date` date DEFAULT NULL,                        
                            `brp_initials` varchar(100) DEFAULT NULL,                        
                            `brp_active` tinyint(1) DEFAULT '1',                             
                            `brp_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  
                            PRIMARY KEY (`brp_id`)                                           
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `breach_remediation_plans_actions` (
                                    `brpa_id` int(11) NOT NULL AUTO_INCREMENT,
                                    `brpa_brp_id` int(11) DEFAULT NULL,
                                    `brpa_task` text,
                                    `brpa_action_plan` text,
                                    `brpa_status` tinyint(1) DEFAULT '0',
                                    `brpa_contact_u_id` int(11) DEFAULT NULL,
                                    `brpa_target_date` date DEFAULT NULL,
                                    `brpa_active` tinyint(1) DEFAULT '1',
                                    `brpa_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    PRIMARY KEY (`brpa_id`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;