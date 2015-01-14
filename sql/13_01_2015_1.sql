INSERT INTO  `hipaacar_app`.`breach_logs_questions` (
`blq_id` ,
`blq_title` ,
`blq_text` ,
`blq_order` ,
`blq_active` ,
`blq_create_date` ,
`blq_update_date`
)
VALUES (
NULL ,  'Was the PHI encrypted?', NULL ,  '3',  '1', 
CURRENT_TIMESTAMP ,  '0000-00-00 00:00:00'
);
UPDATE  `hipaacar_app`.`breach_logs_questions` SET  `blq_order` =  '4' WHERE  `breach_logs_questions`.`blq_id` =3;

ALTER TABLE  `breach_logs` ADD  `bl_initials` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `bl_description` ,
ADD  `bl_initials_approver` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `bl_initials` ;
ALTER TABLE  `breach_logs` ADD  `bl_date_invest_start` DATETIME NULL DEFAULT NULL AFTER  `bl_date_of_occurrence` ,
ADD  `bl_date_invest_complete` DATETIME NULL DEFAULT NULL AFTER  `bl_date_invest_start` ;
ALTER TABLE  `breach_logs` ADD  `bl_invest_led_by` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `bl_name` ;
