<?php

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
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';//todo: delete
        $mail_table = $this->getServiceLocator()->get('Mail\Model\MailtemplateTable');

        foreach ($this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompaniesByRenewalDate(time()) as $client) {
            $mail_table->sendMail($this->getServiceLocator(), array('templateKey' => 'renewaluser', 'uId' => $client->u_id));
            
            $proj_manager_role = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getCompanyRoleByCompanyAndRole($client->c_id, 8);

            if ($proj_manager_role) {
                $mail_table->sendMail($this->getServiceLocator(), array('templateKey' => 'renewaluser', 'uId' => $proj_manager_role->cr_u_id));
            }

            $admin = $this->getServiceLocator()->get('Admin\Model\UserTable')->getAdminUserId();

            if ($admin) {
                $mail_table->sendMail($this->getServiceLocator(), array('templateKey' => 'renewaluser', 'uId' => $admin));
            }            
        }

        return new ViewModel;
    }
}