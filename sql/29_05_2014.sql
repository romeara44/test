
DROP TABLE IF EXISTS `assessments`;

CREATE TABLE `assessments` (
  `a_id` int(11) NOT NULL AUTO_INCREMENT,
  `a_owner_u_id` int(11) DEFAULT NULL,
  `a_consultant_u_id` int(11) DEFAULT NULL,
  `a_c_id` int(11) DEFAULT NULL,
  `a_type` tinyint(1) DEFAULT '0',
  `a_status` int(11) DEFAULT NULL,
  `a_update_u_id` int(11) DEFAULT NULL,
  `a_active` tinyint(1) DEFAULT '1',
  `a_create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `a_update_date` date DEFAULT NULL,
  PRIMARY KEY (`a_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `assessments` */

insert  into `assessments`(`a_id`,`a_owner_u_id`,`a_consultant_u_id`,`a_c_id`,`a_type`,`a_status`,`a_update_u_id`,`a_active`,`a_create_date`,`a_update_date`) values (1,13,NULL,1,2,10,NULL,1,'2014-05-29 13:43:58',NULL),(3,13,NULL,6,1,20,NULL,1,'2014-05-30 13:52:19',NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `mail_templates` */

insert  into `mail_templates`(`mt_id`,`mt_name`,`mt_key`,`mt_text`,`mt_active`,`mt_create_date`) values (1,'Schedule Call with Business Associate','schedulecall','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\nDear &lt;Business Associate&gt;,\r\n\r\nCarosh Compliance Solutions has been retained by «Company» to conduct an audit of their compliance with HIPAA regulations, as amended by the Final Omnibus Rules.  One of the areas covered in the Final Omnibus Rules relates to Business Associates.  As part of our work for «Company»  we are reviewing «Company»’s   Business Associate relationships.  To this end, we would like to schedule a call with you at your earliest possible convenience within the next 2 weeks.  The purpose of this call will be to determine if you are in fact classified correctly as a Business Associate.\r\n\r\nPlease be assured we make every attempt to make this process as painless as possible.  If you have any questions, please do not hesitate to contact me.\r\n\r\nThanks you in advance for your assistance.\r\n\r\nSincerely,\r\n«Consultant\'s Name»\r\n«Consultant\'s Title»\r\nThe Carosh Group',0,'2014-05-15 16:28:15'),(2,'Invite Business Associate to Assessment','invitetoassessment','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\nDear &lt;Business Associate&gt;,\r\n\r\nCarosh Compliance Solutions has been retained by «Company» to conduct an audit of their compliance with HIPAA regulations, as amended by the Final Omnibus Rules. One of the areas covered in the Final Omnibus Rules relates to Business Associates. As part of our work for «Company» we are reviewing «Company»’s Business Associate relationships.\r\n\r\nLogin page: https://hipaa.carosh.com/\r\nUsername: &lt;username&gt;\r\nPassword: &lt;password&gt;\r\n\r\nWe determined that you are classified as a Business Associate, and on behalf of «Company» we will need to conduct additional due diligence on your compliance with the HIPAA Regulations, again as mandated by the Final Omnibus Rules. To help us with this process we would like to have you fill out the attached questionair, and provide us with the following:\r\n\r\n	•	The signature pages of your most recent Security Risk Assessment and Remediation Plan,\r\n	•	The signature page of your HIPAA Master Policy and Procedure Manual, and \r\n	•	A sample of your most recent training logs.\r\n\r\nFinally, subsequent to the due diligence effort, we will need to discuss the changes in the Business Associates Agreement, and execute a new Business Associates Agreement, as mandated by the Final Omnibus Rules, effective as of September 23, 2013.\r\n\r\nOne of our project managers will contact you to set up a time we can meet with you to review the materials and subsequently discuss and execute the revised Business Associate Agreement.\r\n\r\nPlease be assured we make every attempt to make this process as painless as possible. If you have any questions, please do not hesitate to contact me.\r\n\r\nThanks you in advance for your assistance.\r\n\r\nSincerely,\r\n«Consultant\'s Name»\r\n«Consultant\'s Title»\r\nThe Carosh Group',0,'2014-05-15 16:33:04'),(3,'Email Assignee','breachplan','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\n&lt;Client Name&gt;\r\n\r\nTarget Date: 3/4/2014\r\n\r\nThreat: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean gravida mi id nunc placerat suscipit. Vivamus pellentesque imperdiet quam a accumsan. Maecenas ut orci ultrices, aliquet elit vitae, aliquet odio.\r\n\r\nAction Plan: Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla eu turpis in turpis elementum luctus. Vestibulum ut eleifend elit. Fusce at faucibus diam. Donec dictum, risus ac ullamcorper sagittis, risus libero semper enim, quis porttitor nunc nibh in tellus. Vivamus imperdiet sed orci vitae sodales. Pellentesque id nibh faucibus, condimentum diam at, facilisis velit.\r\n\r\nSincerely,\r\n«Consultant\'s Name»\r\n«Consultant\'s Title»\r\nThe Carosh Group',0,'2014-05-22 13:57:42'),(4,'Email Assignee','remediationplan','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\n&lt;Client Name&gt;\r\n\r\nTarget Date: 3/4/2014\r\n\r\nThreat: §164.308(a)(1)(ii)(D) - Information System Activity Review (required) - Implement procedures to regularly review records of information system activity and security incident tracking reports.\r\n\r\nAction Plan: The organization needs to develop and implement system activity review policies and procedures to track security incidents to become compliant.\r\n\r\nSincerely,\r\n«Consultant\'s Name»\r\n«Consultant\'s Title»\r\nThe Carosh Group',0,'2014-05-22 13:59:08'),(5,'Invite <Client Name> to Assessment','invitetoassessment2','Templated email copy will be populated, but user will be able to make changes if necessary.\r\n\r\nDear <Client Name>,\r\n\r\nWe would like to invite you to....\r\n\r\nLogin page: https://hipaa.carosh.com/\r\nUsername: <username>\r\nPassword: <password>\r\n\r\nPlease be assured we make every attempt to make this process as painless as possible.  If you have any questions, please do not hesitate to contact me.\r\n\r\nThanks you in advance for your assistance.\r\n\r\nSincerely,\r\n<Consultant Name>\r\n<Consultant Title>\r\nThe Carosh Group',0,'2014-05-29 14:44:03');
