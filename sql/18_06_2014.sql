CREATE TABLE `remediation_plans` (                          
                            `rp_id` int(11) NOT NULL AUTO_INCREMENT,                        
                            `rp_version_index` int(11) DEFAULT NULL,                        
                            `rp_version_index_item` int(11) DEFAULT NULL,                   
                            `rp_writable` tinyint(1) DEFAULT '1',                           
			    `rp_type` tinyint(1) DEFAULT '1',
                            `rp_a_id` int(11) DEFAULT NULL,
                            `rp_c_id` int(11) DEFAULT NULL,
                            `rp_consultant_u_id` int(11) DEFAULT NULL,
                            `rp_approver_u_id` int(11) DEFAULT NULL,
                            `rp_initials_approver` varchar(255) DEFAULT NULL,
                            `rp_create_u_id` int(11) DEFAULT NULL,
                            `rp_update_u_id` int(11) DEFAULT NULL,
                            `rp_is_version` tinyint(1) DEFAULT '0',
                            `rp_parent_rp_id` int(11) DEFAULT NULL,
                            `rp_status` int(11) DEFAULT '10',                               
                            `rp_incident_date` date DEFAULT NULL,                           
                            `rp_remediation_date` date DEFAULT NULL,                        
                            `rp_initials` varchar(100) DEFAULT NULL,                        
                            `rp_active` tinyint(1) DEFAULT '1',                             
                            `rp_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  
                            PRIMARY KEY (`rp_id`)                                           
                          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;   



CREATE TABLE `remediation_plans_actions` (                   
                                    `rpa_id` int(11) NOT NULL AUTO_INCREMENT,                        
                                    `rpa_rp_id` int(11) DEFAULT NULL,
                                    `rpa_threat` text,
                                    `rpa_action_plan` text,
				                            `rpa_risk_score` int(11),
                                    `rpa_status` tinyint(1) DEFAULT '0',                             
                                    `rpa_contact_u_id` int(11) DEFAULT NULL,                         
                                    `rpa_approver_u_id` int(11) DEFAULT NULL,                        
                                    `rpa_target_date` date DEFAULT NULL,                             
                                    `rpa_active` tinyint(1) DEFAULT '1',                             
                                    `rpa_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  
                                    PRIMARY KEY (`rpa_id`)                                           
                                  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

