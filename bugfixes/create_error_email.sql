SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for error_mail_recipients
-- ----------------------------
DROP TABLE IF EXISTS `error_mail_recipients`;
CREATE TABLE `error_mail_recipients`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_connection`(`u_id`) USING BTREE,
  CONSTRAINT `user_connection` FOREIGN KEY (`u_id`) REFERENCES `rockyhil_hipaa`.`users` (`u_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
