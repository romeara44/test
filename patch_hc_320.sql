CREATE TABLE IF NOT EXISTS `employee_master_list` (
  `eml_company_id` int(11) NOT NULL,
  `eml_list` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `training_logs` ADD `_tl_eml_items` TEXT NULL ;