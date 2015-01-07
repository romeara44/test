alter table assessments_questions_answers change column aqa_a_id aqa_a_id int(11);
alter table assessments_questions_answers change column aqa_aq_id aqa_aq_id int(11);
alter table assessments_questions_answers change column aqa_aqo_id aqa_aqo_id int(11);
alter table assessments_questions_answers change column aqa_u_id aqa_u_id int(11);
alter table assessments_inventory_locations_reports change column ailr_f_id ailr_f_id int(11);

alter table assessments_questions_answers add column aqa_adr_id int(11) after aqa_a_id;
alter table assessments_questions_answers drop column aqa_order;

alter table assessments_questions_answers add column aqa_create_u_id int(11) after aqa_active;
alter table assessments_questions_answers add column aqa_update_u_id int(11) after aqa_create_u_id;
alter table assessments_questions_answers add column aqa_ar_id int(11) after aqa_adr_id;
alter table assessments_questions_answers drop column aqa_u_id;
