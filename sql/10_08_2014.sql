alter table `remediation_plans` add column rp_security_rp_id int(11) after rp_parent_rp_id;
update `assessments_questions_categories` set aqc_ar_id = 4 where aqc_ar_id IS NULL;

insert into assessments_roles (ar_name, ar_order) values ('Chief Privacy Officer', 1);
update `assessments_questions_categories` set aqc_ar_id = 7 where aqc_id > 51