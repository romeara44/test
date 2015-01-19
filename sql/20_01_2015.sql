ALTER TABLE  `breach_logs` ADD  `bl_approver_u_id` INT( 11 ) NULL AFTER  `bl_initials_approver` ,
ADD  `bl_accepter_u_id` INT( 11 ) NULL AFTER  `bl_approver_u_id` ;