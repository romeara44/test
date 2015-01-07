ALTER TABLE business_associates add column ba_sign_off_date datetime after ba_status;
alter table assessments_questions_categories add column aqc_action_plan text after aqc_specification;
alter table remediation_plans_actions add column rpa_adr_id int(11) after rpa_rp_id;

alter table remediation_plans_actions add column rpa_aqc_id int(11) after rpa_rp_id;