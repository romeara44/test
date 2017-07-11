CREATE TABLE IF NOT EXISTS `company_training_managers` (
  `ctm_company_id` int(11) NOT NULL,
  `ctm_u_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `company_training_managers`
  ADD UNIQUE KEY `ctm_company_training_manager` (`ctm_company_id`,`ctm_u_id`);

insert into `company_training_managers` (`ctm_company_id`,`ctm_u_id`)
SELECT c_id, c_training_manager_u_id FROM `companies` where c_training_manager_u_id is not null;