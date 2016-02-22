CREATE TABLE IF NOT EXISTS `physical_security_changes` (
  `psc_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `psc_c_id` int(11) DEFAULT NULL,
  `psc_adr_id` int(11) DEFAULT NULL,
  `psc_change_type` tinyint(1) DEFAULT NULL,
  `psc_active` tinyint(1) DEFAULT NULL,
  `psc_create_u_id` int(11) DEFAULT NULL,
  `psc_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `physical_security_changes_items` (
  `psci_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `psci_psc_id` int(11) DEFAULT NULL,
  `psci_date` date DEFAULT NULL,
  `psci_identification` varchar(255) DEFAULT NULL,
  `psci_reason` varchar(255) DEFAULT NULL,
  `psci_person` varchar(255) DEFAULT NULL,
  `psci_individual` varchar(255) DEFAULT NULL,
  `psci_comments` varchar(255) DEFAULT NULL,
  `psci_create_u_id` int(11) DEFAULT NULL,
  `psci_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
