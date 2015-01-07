update assessments_roles set ar_name = 'HIPAA Compliance Officer' where ar_id = 5;
update assessments_roles set ar_name = 'Chief Security Officer' where ar_id = 4;


alter table assessments_questions_categories add column aqc_ar_id int(11) after aqc_id;
alter table assessments_questions_categories add column aqc_cu_id int(11) after aqc_ar_id;

alter table remediation_plans_actions add column rpa_risk_level int(11) after rpa_risk_score;

