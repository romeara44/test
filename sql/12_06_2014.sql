alter table breach_remediation_plans_actions add column brpa_approver_u_id int(11) after brpa_contact_u_id;
alter table breach_remediation_plans add column brp_approver_u_id int(11) after brp_consultant_u_id;
alter table breach_remediation_plans add column brp_initials_approver varchar(255) after brp_approver_u_id;
insert into mail_templates values (null, 'Request Review', 'requestreview', 'Request Review', 'Request Review <id>', 1, current_timestamp);
insert into mail_templates values (null, 'Pending approval', 'pendingapproval', 'Pending approval', 'Pending approval <id>', 1, current_timestamp);
