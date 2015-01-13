UPDATE `hipaacar_app`.`mail_templates` SET `mt_text` = 'Dear <Client Name>,

Thank you for choosing Carosh Compliance Solutions to assist you with your HIPAA Compliance Program.

To get started, we ask you to fill out the preliminary company information we discussed in our "Kick-off meeting".  As I mentioned at that time, the following will give you access to our system  to log on and answer some administrative questions.

Login page: https://hipaa.carosh.com/
Username: <username>

We understand the questions may be confusing or raise more questions then they answer.  
Most of our clients request some assistance with this task, or have and number of questions.  If you would like us to go through any or all of the questions, please do not hesitate to contact me to set a time to work together on this task.

Thanks you in advance for your assistance.

Sincerely,
<Consultant Name>
<Consultant Title>
<Consultant phone>
<Consultant email>

The Carosh Group
www.carosh.com
' WHERE `mail_templates`.`mt_key` = 'invitetoassessment2';