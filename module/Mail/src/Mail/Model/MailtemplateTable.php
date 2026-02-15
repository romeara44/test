<?php
namespace Mail\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\View\Model\ViewModel;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions;

use SendGrid\Mail\Attachment;


class MailtemplateTable
{
    protected $tableGateway;
    protected $serviceLocator;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getMailtemplate($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('mt_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getMailtemplateByKey($key)
    {
        $rowset = $this->tableGateway->select(array('mt_key' => $key));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

	private function sendMailerror($sl, $params){
        $mail = new Mail\Message();
		$mail->setFrom('postmaster@click5dev7.com', 'HIPAA Suite');
		$subject = 'Carosh global error message server(' . $_SERVER['SERVER_NAME'] . ')';
        $mail->setSubject($subject);

        $message = 'no message attached';	
        if(isset($params['message'])){
            $message = $params['message'];
        }
        $emr = $sl->get('Mail\Model\ErrormailrecipientsTable')->fetchAll();
        
        $addTo = '';
        foreach($emr as $row){
            $tosend = $sl->get('Admin\Model\UserTable')->getUser($row['u_id']);
            if($addTo == '')
                $addTo = $tosend->u_email;
            $toName = $tosend->u_firstname . ' ' . $tosend->u_lastname;
            $mail->addTo($tosend->u_email, $toName);
        }

        $html = new \Zend\Mime\Part($message);
        $html->type = 'text/html';
        $body = new \Zend\Mime\Message;
        $body->addPart($html);
        $mail->setBody($body);
        
        $this->sendMailpackage($mail);
        $sl->get('Mail\Model\MailsentTable')->saveMail(array('ms_subject' => $subject, 'ms_text' => $message, 'ms_addto' => $addTo));			
	}

	private function sendMailissue($sl, $params){
        $mail = new Mail\Message();
		$mail->setFrom('postmaster@click5dev7.com', 'HIPAA Suite');
		$subject = 'Carosh user reporting issue: server(' . $_SERVER['SERVER_NAME'] . ')';
        $mail->setSubject($subject);

		$message = 'no message attached';	
		if(isset($params['message'])){
			$message = $params['message'];
		}
		$emr = $sl->get('Mail\Model\ErrormailrecipientsTable')->fetchAll();
			
		$addTo = '';
        foreach($emr as $row){
            $tosend = $sl->get('Admin\Model\UserTable')->getUser($row['u_id']);
            if($addTo == '')
                $addTo = $tosend->u_email;
            $toName = $tosend->u_firstname . ' ' . $tosend->u_lastname;
            $mail->addTo($tosend->u_email, $toName);
        }

        $html = new \Zend\Mime\Part($message);
        $html->type = 'text/html';
        $body = new \Zend\Mime\Message;
        $body->addPart($html);
        $mail->setBody($body);
        
        $this->sendMailpackage($mail);
        $sl->get('Mail\Model\MailsentTable')->saveMail(array('ms_subject' => $subject, 'ms_text' => $message, 'ms_addto' => $addTo));			
	}

    public function sendMail($sl, $params = array())
    {
        if(isset($params['type'])){
            if($params['type'] === 'error'){
                $this->sendMailerror($sl, $params);
                return;
            }
        }

        if(isset($params['type'])){
            if($params['type'] === 'issue'){
                $this->sendMailissue($sl, $params);
                return;
            }
        }

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        //error_reporting(255);
        //ini_set('display_errors', 1);
        $mt = null;
        $templateKey = '';
        if (isset($params['templateKey'])) {
            $templateKey = $params['templateKey'];
            $mt = $this->getMailtemplateByKey($params['templateKey']);            
        }

        $user = null;
        $addTo = '';
        $addToName = '';
        if (isset($params['uId'])) {
            $user = $sl->get('Admin\Model\UserTable')->getUser($params['uId']);
            $addToName = $user->u_firstname . ' ' . $user->u_lastname;
            $addTo = $user->u_email;
            if (isset($params['login'])) {
                $mt->mt_text = str_replace('<login>', $params['login'], $mt->mt_text);
            } else {
                $mt->mt_text = str_replace('<login>', $user->u_email, $mt->mt_text);
            }
            if (isset($params['password'])) {
                $mt->mt_text = str_replace('<password>', $params['password'], $mt->mt_text);
                $mt->mt_text = str_replace('<Full Name>', $addToName, $mt->mt_text);
                if ($identity) {
                    $mt->mt_text = str_replace('<Admin Name>', $identity['u_firstname'] . ' ' . $identity['u_lastname'], $mt->mt_text);    
                } else {
                    $mt->mt_text = str_replace('<Admin Name>', $addToName, $mt->mt_text);  
                }
                
                if ($user->u_role_id == \Admin\Model\User::ROLE_CLIENT) {
                    $mt->mt_text = str_replace('<Consultant Name>', $identity['u_firstname'] . ' ' . $identity['u_lastname'], $mt->mt_text);
                } else {
                    $mt->mt_text = str_replace(' by <Consultant Name>', '', $mt->mt_text);
                }
            }
            if (isset($params['link'])) {
                $mt->mt_text = str_replace('<link>', $params['link'], $mt->mt_text);
            }
            $mt->mt_text = str_replace('<Full Name>', $addToName, $mt->mt_text);
        }

        if (isset($params['cId'])) {
            $company = $sl->get('Client\Model\CompanyTable')->getClientCompany($params['cId']);
            if ($company) {
                $mt->mt_text = str_replace('<Company name>', $company->c_name, $mt->mt_text);
                $mt->mt_text = str_replace('<Company Renewal Date - 30 days>', date('m/d/Y', strtotime('-30 days', strtotime($company->c_renewal_date))), $mt->mt_text);
                $mt->mt_text = str_replace('<Company Renewal Date>', date('m/d/Y', strtotime($company->c_renewal_date)), $mt->mt_text);
                $mt->mt_text = str_replace('<Company Details page link>', 'https://hipaa.carosh.com/company/edit/' . $company->c_id, $mt->mt_text);
                $mt->mt_text = str_replace('<client_deactivate_link>', 'https://hipaa.carosh.com/company/delete/' . $company->c_id, $mt->mt_text);
            }
        }
        if (isset($params['brpId'])) {
            $mt->mt_text = str_replace('<id>', $params['brpId'], $mt->mt_text);
        }

        if (isset($params['brpaId'])) {
            $mt->mt_text = str_replace('<id>', $params['brpaId'], $mt->mt_text);
        }
        if (isset($params['modules_access_code'])) {
            $mt->mt_text = str_replace('<Full Name>', $params['addToName'], $mt->mt_text);
            $mt->mt_text = str_replace('<code>', $params['modules_access_code'], $mt->mt_text);
        }

        $mail = new Mail\Message();

        $subject = '';
        if (isset($params['subject'])) {
            $subject = $params['subject'];
        } else {
            $subject = $mt->mt_subject;
        }

        if (isset($params['forgot_password_user'])) {
            $forgotPasswordUserName = $params['forgot_password_user']->u_firstname . ' ' . $params['forgot_password_user']->u_lastname;

            $mt->mt_text = str_replace('<User Name>', $forgotPasswordUserName, $mt->mt_text);
            $mt->mt_text = str_replace('<User Email>', $params['forgot_password_user']->u_email, $mt->mt_text);
        }

        if (isset($params['locked_user'])) {
            $lockedUserName = $params['locked_user']->u_firstname . ' ' . $params['locked_user']->u_lastname;

            $mt->mt_text = str_replace('<User Name>', $lockedUserName, $mt->mt_text);
            $mt->mt_text = str_replace('<User Email>', $params['locked_user']->u_email, $mt->mt_text);
        }

        $text = '';
        if (isset($params['text'])) {
            $text = $params['text'];
        } else {
            $text = $mt->mt_text;
        }

        // password to send
        $password = '';
        if (isset($params['post']['passwordToSent']) && $params['post']['passwordToSent'] && isset($params['post']['passwordUId']) && ($params['post']['passwordUId'])) {
            $user = $sl->get('Admin\Model\UserTable')->getUser($params['post']['passwordUId']);
            $password = $sl->get('Admin\Model\UserTable')->generatePassword();
            $sl->get('Admin\Model\UserTable')->setNewPassword($params['post']['passwordUId'], $password);
        }

        if (isset($params['addto']) && ($params['addto'] != '')) {
            $addTo = $params['addto'];
            $user = $sl->get('Admin\Model\UserTable')->getUserByEmail($addTo);
            $addToName = $user->u_firstname . ' ' . $user->u_lastname;
        }

        if (in_array($subject, array('Invite to assessment', 'Schedule Call with Business Associate'))) {
            $mail->setFrom('hipaasuite@carosh.com', $identity['u_firstname'] . ' ' . $identity['u_lastname']);
            $mail->setReplyTo($identity['u_email'], $identity['u_firstname'] . ' ' . $identity['u_lastname']);
        } else {
            $mail->setFrom('hipaasuite@carosh.com', 'HIPAA Suite');
            $mail->addReplyTo('hipaasuite@carosh.com', 'HIPAA Suite');
        }

        $htmlTemplateText = $this->_getHtmlTemplate($sl, $text, $templateKey);

        $message = $htmlTemplateText;

        if ($password) {
            $message = str_replace('********', htmlspecialchars($password), $message);
        }

		$message = utf8_encode($message);
        $message = str_replace('Â', '', $message);
        $message = str_replace('â¢', '&#8226;', $message);

        $html = new \Zend\Mime\Part($message);
        $html->type = 'text/html';
        $body = new \Zend\Mime\Message;

        $body->addPart($html);
        
        if(isset($params['post']) && $params['post']['attachments']) {
            foreach($params['post']['attachments'] as $attachment) {
                
                $attachmentContent = file_get_contents($attachment['file_path']);
                $attach = new \Zend\Mime\Part($attachmentContent);
                $attach->filename    = $attachment['file_name'];
                $attach->type        = \Zend\Mime\Mime::TYPE_OCTETSTREAM;
                //$attach->encoding    = \Zend\Mime\Mime::ENCODING_BASE64;
                $attach->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
                
                $body->addPart($attach);
            }
        }

        if($_SERVER['SERVER_NAME'] != 'hipaa'){
            $mail->addTo($addTo, $addToName);
            $mail->addBcc('compliance@carosh.com');
        }

        $mail->setBody($body);
        $mail->setSubject($subject);
				
		$this->sendMailpackage($mail);

        $sl->get('Mail\Model\MailsentTable')->saveMail(array('ms_subject' => $subject, 'ms_text' => $message, 'ms_addto' => $addTo));			
    }
        
    private function sendMailpackage($mail){

        // Setup SMTP transport using LOGIN authentication

        $options = new SmtpOptions();
        
        //Roger has the 2 factor auth associated to it.
        $options
            ->setHost('smtp.sendgrid.net')
            ->setConnectionClass('login')
            ->setName('smtp.sendgrid.net')
            ->setConnectionConfig(array(
                'auth' => 'login',
                'username' => 'HIPAASuite',
                //'password' => '1948Box13',
                'password' => '!948Box!3',
                'ssl' => 'tls',
                'port' => 587
            ));

        // GMAIL options
        /*$options = new SmtpOptions();
        $options
            ->setHost('smtp.gmail.com')
            ->setConnectionClass('login')
            ->setName('smtp.gmail.com')
            ->setConnectionConfig(array(
                'auth' => 'login',
                'username' => 'hipaacarosh@gmail.com',
                'password' => 'qv#cgAwev',
                'ssl' => 'tls',
                'port' => 587
            ));*/

        $domLibPath =  $_SERVER['DOCUMENT_ROOT'] . '/../vendor';

        $domLibPath = $domLibPath . '/sendgrid-php/sendgrid-php.php';
        require_once $domLibPath;

        $email = new \SendGrid\Mail\Mail();
        
        $email->setSubject($mail->getSubject());      

        $fromArray = $mail->getFrom();
        foreach($fromArray as $fromItem) {
            $email->setFrom($fromItem->getEmail(), $fromItem->getName());
          }

        $toArray = $mail->getTo();
        foreach($toArray as $toItem){
            $email->addTo($toItem->getEmail(), $toItem->getName());            
        }

        $replyToArray = $mail->getReplyTo();
        foreach($replyToArray as $replyToItem){
            $email->setReplyTo($replyToItem->getEmail(), $replyToItem->getName());
        }
        
        $ccArray = $mail->getCc();
        foreach($ccArray as $ccItem){
            $email->addCc($ccItem->getEmail(), $ccItem->getName());
        }

        $bccArray = $mail->getBcc();
        foreach($bccArray as $bccItem){
            $email->addBcc($bccItem->getEmail(), $bccItem->getName());
        }
        
        $body = $mail->getBody();
        
        $filter = array('text/plain', 'text/html');
                  
        foreach ($body->getParts() as $part) {

            if (!in_array($part->type, $filter) || $part->disposition === \Zend\Mime\Mime::DISPOSITION_ATTACHMENT) {
                $attachment = new \SendGrid\Mail\Attachment();
                
                $attachment->setContent($part->getContent());
                $attachment->setType($part->type);
                $attachment->setFilename($part->filename);
                $attachment->setDisposition($part->disposition);
                
                $email->addAttachment( $attachment );

            }
            else {
                $mailType = $part->type;
                $mailContent = $part->getContent();
                $email->addContent($mailType, $mailContent);
            }

        }

        $sendgrid = new \SendGrid('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'); //Restricted API, only send emails.
        //$sendgrid = new \SendGrid('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'); //Restricted API, only send emails.
        
        try {
            
            if($_SERVER['SERVER_ADDR'] == '127.0.0.1'){ 
                // Setup File transport
                $transport = new FileTransport();
                $options   = new FileOptions(array(
                    'path'              => $_SERVER['DOCUMENT_ROOT'] . '/data/mail/',
                    'callback'  => function (FileTransport $transport) {
                        return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
                    },
                ));
                $response = $sendgrid->send($email);
                //print $response->statusCode() . "\n";
                //print_r($response->headers());
                //print $response->body() . "\n";
            }
            else {
                
                $response = $sendgrid->send($email);
                //print $response->statusCode() . "\n";
                //print_r($response->headers());
                //print $response->body() . "\n";
            }

        } catch (Exception $e) {
            
            throw new \Exception('Email Send Error ' . $e->getMessage() ."\n");
        }

	}

    public function _getHtmlTemplate($sl, $content, $templateKey = '')
    {
        $renderer = new PhpRenderer();

        $docRoot = $_SERVER['SERVER_NAME'] == 'hipaa' ? $_SERVER['DOCUMENT_ROOT'] . '/..' : $_SERVER['DOCUMENT_ROOT'] . '/..';

        $map = new Resolver\TemplateMapResolver(array(
            'mail/mailtemplate' => $docRoot . '/module/Mail/view/mail/mail/mailtemplate.phtml',
        ));

        $renderer->setResolver($map);

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $consultant = $sl->get('Admin\Model\UserTable')->getUser($identity['u_id']);

        if($consultant) {
            $consultantName = $consultant->u_firstname . ' ' . $consultant->u_lastname;
            $consultantEmail = $consultant->u_email;
            $consultantPhone = $consultant->u_office_phone;
        } else {
            $consultantName = '';
            $consultantEmail = '';
            $consultantPhone = '';
        }

        $model = new ViewModel(array(
            'content' => $content,
            'consultant_name'  => $consultantName,
            'consultant_email' => $consultantEmail,
            'consultant_phone' => $consultantPhone,
            'consultant_role'  => $sl->get('Admin\Model\RoleTable')->getRoleName($identity['u_role_id']),
            'templateKey' => $templateKey,
        ));
        $model->setTemplate('mail/mailtemplate');

        $html = $renderer->render($model);

        return $html;
    }
}