alter table assessments_questions_categories add column aqc_policy varchar(255) after aqc_citation;
alter table remediation_plans_actions add column rpa_policy varchar(255) after rpa_action_plan;