<?php
//C:\www\hipaa>php public/index.php clients renewal
namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use RuntimeException;

class IndexController extends AbstractActionController
{
    public function renewalAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/public';
        //$_SERVER['SERVER_ADDR'] = '127.0.0.1';//todo: delete
        $mail_table = $this->getServiceLocator()->get('Mail\Model\MailtemplateTable');

        foreach ($this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompaniesForRenewalDateNotification(strtotime('+45 days')) as $client) {

            $consultant = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getConsultantIdForCompany($client->c_id);

            if ($consultant) {
                $mail_table->sendMail($this->getServiceLocator(), array('templateKey' => 'renewalnotification', 'uId' => $consultant->cc_consultant_id, 'cId' => $client->c_id));
            } 
        }

        foreach ($this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompaniesForRenewalDateEmailing(strtotime('+30 days')) as $client) {

            $recepients = array();
            if ($client->c_renewal_email_recipients) {
                $recepients = explode(',', $client->c_renewal_email_recipients);
            }

            $admin_id = $this->getServiceLocator()->get('Admin\Model\UserTable')->getAdminUserId();

            if ($admin_id) {
                $recepients[] = $admin_id;
            }

            foreach ($recepients as $id) {
                $mail_table->sendMail($this->getServiceLocator(), array('templateKey' => 'renewaluser', 'uId' => $id));
            }
        }

        $this->getServiceLocator()->get('Client\Model\CompanyTable')->incrementRenewalDate();

        return new ViewModel;
    }
}