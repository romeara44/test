alter table breach_remediation_plans add column brp_version_index_item int(11) after brp_version_index;
alter table companies add column c_owner_u_id int(11) after c_consultant_u_id;
update companies set c_owner_u_id = c_consultant_u_id;
alter table companies add column c_update_u_id int(11) after c_owner_u_id;
