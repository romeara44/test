UPDATE  `hipaacar_app`.`mail_templates` SET  `mt_text` =  'Dear <Client Name>,

You have been assigned the following task as part of our remediation project. When you have completed the task, please follow the <a href="<Link>" target="_blank">link</a> to upload the final version of your work, change the status to "Pending Approval" and forward your work on to the <Approver>.

<Company>

Target Date: <Target Date>

Task: <Task>

Action Plan: <Action plan>. See attachment.
Policy number: <Policy>' WHERE  `mail_templates`.`mt_key` ='remediationplan';

UPDATE  `hipaacar_app`.`mail_templates` SET  `mt_text` =  'Dear <Client Name>,

The following task is ready for your approval. When ready, please click on the <a href="<Link>" target="_blank">link</a> approve the task, and set the status of the task to completed.

<Company>

Target Date: <Target Date>

Task: <Task>

Action Plan: <Action plan>. See Attacment.' WHERE  `mail_templates`.`mt_key` ='emailapprover';
