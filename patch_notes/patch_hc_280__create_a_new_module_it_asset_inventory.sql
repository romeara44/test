CREATE TABLE `it_asset_inventories` (
  `iai_id` int(11) NOT NULL AUTO_INCREMENT,
  `iai_c_id` int(11) DEFAULT NULL,
  `iai_type_id` int(11) DEFAULT NULL,
  `iai_owner_u_id` int(11) DEFAULT NULL,
  `iai_consultant_u_id` int(11) DEFAULT NULL,
  `iai_location_id` int(11) DEFAULT NULL,
  `iai_active` tinyint(1) DEFAULT '1',
  `iai_update_u_id` int(11) DEFAULT NULL,
  `iai_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `iai_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`iai_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `it_asset_inventory_types` (
  `iait_id` int(11) NOT NULL AUTO_INCREMENT,
  `iait_name` varchar(255) NOT NULL,
  `iait_order` int(11) DEFAULT NULL,
  `iait_active` int(11) DEFAULT '1',
  `iait_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`iait_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `it_asset_inventory_types` (`iait_name`, `iait_order`, `iait_create_date`)
     VALUES ('Full IT Asset Inventory Report Upload', 1, NOW())
          , ('ePHI Servers Report Upload', 2, NOW())
          , ('Network Devices Report Upload', 3, NOW())
          , ('ePHI Repository Summary Report Upload', 4, NOW())
          , ('ISP, WAN and Data Circuit Summary Report Upload', 5, NOW())
          , ('IT Asset Manual Entry Form', 6, NOW());

CREATE TABLE `it_asset_inventory_item_types` (
  `iaiit_id` int(11) NOT NULL AUTO_INCREMENT,
  `iaiit_name` varchar(255) DEFAULT NULL,
  `iaiit_order` int(11) DEFAULT NULL,
  `iaiit_active` tinyint(1) DEFAULT '1',
  `iaiit_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`iaiit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `it_asset_inventory_item_types`(`iaiit_id`,`iaiit_name`,`iaiit_order`,`iaiit_active`,`iaiit_create_date`)
     VALUES (1,'ePHI Servers',1,1,'2014-06-03 23:33:24')
          , (2,'Network Devices',2,1,'2014-06-03 23:33:31')
          , (3,'ePHI Repository Summary',3,1,'2014-06-03 23:33:40')
          , (4,'ISP, WAN and Data Circuit Summary',4,1,'2014-06-03 23:33:41');

CREATE TABLE `it_asset_inventory_items` (
  `iaii_id` int(11) NOT NULL AUTO_INCREMENT,
  `iaii_iai_id` int(11) DEFAULT NULL,
  `iaii_iaiit_id` int(11) DEFAULT NULL,
  `iaii_name` text,
  `iaii_model` text,
  `iaii_description` text,
  `iaii_create_u_id` int(11) DEFAULT NULL,
  `iaii_update_u_id` int(11) DEFAULT NULL,
  `iaii_active` tinyint(1) DEFAULT '1',
  `iaii_create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `iaii_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`iaii_id`)
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8;

CREATE TABLE `it_asset_inventory_reports` (
  `iair_id` INT(11) NOT NULL AUTO_INCREMENT,
  `iair_iai_id` INT(11) DEFAULT NULL,
  `iair_iaiit_id` INT(11) DEFAULT NULL,
  `iair_f_id` INT(11) DEFAULT NULL,
  `iair_create_u_id` INT(11) DEFAULT NULL,
  `iair_update_u_id` INT(11) DEFAULT NULL,
  `iair_active` TINYINT(1) DEFAULT '1',
  `iair_create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `iair_update_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`iair_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;