INSERT INTO  `hipaacar_app`.`assessments_roles` (
`ar_id` ,
`ar_name` ,
`ar_order` ,
`ar_active` ,
`ar_create_date`
)
VALUES (
NULL ,  'Project Manager',  '7',  '1', 
CURRENT_TIMESTAMP
), (
NULL ,  'Approval Authority',  '8',  '1', 
CURRENT_TIMESTAMP
), (
NULL ,  'Signoff Authority',  '9',  '1', 
CURRENT_TIMESTAMP
);