alter table assessments add column a_step1_finished tinyint(1) default 0 after a_status;
alter table assessments add column a_step2_finished tinyint(1) default 0 after a_step1_finished;
alter table assessments add column a_step3_finished tinyint(1) default 0 after a_step2_finished;
alter table assessments add column a_step4_finished tinyint(1) default 0 after a_step3_finished;
alter table assessments add column a_step5_finished tinyint(1) default 0 after a_step4_finished;

alter table assessments add column a_all_steps_finished tinyint(1) default 0 after a_step5_finished;

alter table assessments add column a_version_index int(11) default 0 after a_id;
alter table assessments add column a_version_index_item int(1) default 0 after a_version_index;
alter table assessments add column a_writable tinyint(1) default 1 after a_version_index_item;
alter table assessments add column a_is_version tinyint(1) default 0 after a_writable;
alter table assessments add column a_parent_a_id int(11) after a_is_version;
