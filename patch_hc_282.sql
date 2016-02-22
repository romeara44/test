CREATE TABLE IF NOT EXISTS `business_associates_reports` (
  `bar_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `bar_ba_id` int(11) DEFAULT NULL,
  `bar_f_id` int(11) DEFAULT NULL,
  `bar_create_u_id` int(11) DEFAULT NULL,
  `bar_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
