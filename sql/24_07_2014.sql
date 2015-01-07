alter table remediation_plans add column rp_performed_u_id int(11) after rp_approver_u_id;
alter table breach_remediation_plans add column brp_performed_u_id int(11) after brp_approver_u_id;