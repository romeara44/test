ALTER TABLE `remediation_plans` ADD `rp_adr_id` INT NULL ;
update remediation_plans
left join remediation_plans_actions on (rp_id = rpa_rp_id)
set rp_adr_id = rpa_adr_id;