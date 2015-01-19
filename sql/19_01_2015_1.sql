ALTER TABLE  `remediation_plans` ADD  `rp_approved_date` DATE NULL AFTER  `rp_initials` ,
ADD  `rp_accepted_date` DATE NULL AFTER  `rp_approved_date` ;
ALTER TABLE  `remediation_plans` ADD  `rp_accepter_u_id` INT( 11 ) NULL AFTER  `rp_initials` ;