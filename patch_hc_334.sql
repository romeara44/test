INSERT INTO `hipaacar_app`.`roles` (`role_id`, `role_name`, `role_create_date`, `role_active`) VALUES ('8', 'Trail User', CURRENT_TIMESTAMP, '1');
ALTER TABLE `companies` ADD `c_is_fee` TINYINT NULL DEFAULT 0;
