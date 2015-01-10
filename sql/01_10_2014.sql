UPDATE  `hipaacar_app`.`mail_templates` SET  `mt_text` =  'You have been assigned the following task as part of our remediation project. When you have completed the task, please follow the link to upload the final version of your work, change the status to "Pending Approval" and forward your work on to the "Approver"" or for the Approver " the following task is ready for your approval. When ready, please click on the link approve the task, and set the status of the task to completed.

<Client Name>

Target Date: <Target Date>

Task: <Task>

Action Plan: <Action plan>
Policy number: <Policy>' WHERE  `mail_templates`.`mt_key` = 'remediationplan';

UPDATE  `hipaacar_app`.`mail_templates` SET  `mt_text` =  'You have been assigned the following task as part of our remediation project. When you have completed the task, please follow the link to upload the final version of your work, change the status to "Pending Approval" and forward your work on to the "Approver"" or for the Approver " the following task is ready for your approval. When ready, please click on the link approve the task, and set the status of the task to completed.

<Client Name>

Target Date: <Target Date>

Task: <Task>

Action Plan: <Action plan>' WHERE  `mail_templates`.`mt_key` = 'emailapprover';