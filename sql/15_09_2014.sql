alter table business_associates add column ba_phone_call_invited_status int(11) after ba_phone_call_status;
insert into assessments_questions_options values (3, 1, 'N/A', 3, 0, 1, '2014-06-16 19:48:44', '0000-00-00 00:00:00');
alter table business_associates_answers add column baa_ba_id int(11) after baa_id;