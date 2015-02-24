<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\User;
use Admin\Form\UserForm;
use User\Form\RegistrationForm;
use Zend\Session\Container;
use Client\Model\CompanyTable;
use Client\Model\Company;

class UserController extends AbstractActionController
{
    protected $userTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity() && !in_array($this->params('action'), array('registration', 'confirm'))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        return parent::onDispatch($e);
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getCompanyTable()
    {
        if (!$this->companyTable) {
            $sm = $this->getServiceLocator();
            $this->companyTable = $sm->get('Client\Model\CompanyTable');
        }
        return $this->companyTable;
    }

    public function getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return $identity;
    }

    public function hasIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->hasIdentity();

        return $identity;
    }

    public function accountAction()
    {
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new UserForm($this->getServiceLocator());

        $identity = $this->getIdentity();

        $id = $identity['u_id'];
        $userObj = null;
        if ((int) $id) {
            $userObj = $this->getUserTable()->getUser($id);
        } else {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $passwordWrong = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $user = new User();
            $uId = is_object($userObj) ? $userObj->u_id : 0;
            $form->setInputFilter($user->getSimpleInputFilter($this->getServiceLocator(), $id, $uId, true));
            $form->setData($request->getPost());

            if (($post['u_password'] != '') && ($post['u_password'] != $post['u_confirm_password'])) {
                $passwordWrong = true;
            }

            if ($form->isValid() && !$passwordWrong) {

                if ($post['u_password'] != '') {
                    $this->getUserTable()->setNewPassword($id, $post['u_password']);
                }

                $post['u_senior_consultant_u_id'] = $userObj->u_senior_consultant_u_id;
                $post['u_company_id'] = $userObj->u_company_id;
                $post['u_role_id'] = $userObj->u_role_id;
                $post['u_email'] = $userObj->u_email;
                $user->exchangeArray($post);
                $this->getUserTable()->saveUser($user);

                $this->flashMessenger()->addSuccessMessage('Profile has been modified');

                $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'account'));

            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                    //echo "Validation failure '$messageId': $message\n";
                    //die;
                }
            }
        } else {
            if ((int) $id) {
                $form->bind($userObj);
            }
        }

        return array(
            'form' => $form,
            'passwordWrong' => $passwordWrong,
            'uId' => $id,
            'email' => $identity['u_email']
        );
    }

    public function registrationAction()
    {
        $form = new RegistrationForm($this->getServiceLocator(), $this->getRequest()->getBaseUrl().'/data/captcha/');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $user = new User();
            $form->setInputFilter($user->getRegistrationInputFilter($this->getServiceLocator()));
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $post['u_senior_consultant_u_id'] = null;
                $post['u_company_id']             = null;
                $post['u_register']               = 1;
                $post['u_first_login']            = 1;
                $post['u_confirmed']              = 0;
                $post['u_role_id']                = \Admin\Model\User::ROLE_PARTIAL;

                $user->exchangeArray($post);

                $uId = $this->getUserTable()->registerUser($user);

                if($uId) {
                    $company = new Company();

                    $companyData['c_name']                 = $post['u_company'];
                    $companyData['c_email']                = $post['u_email'];
                    $companyData['c_phone']                = $post['u_office_phone'] . $post['u_office_phone_inner'];
                    $companyData['c_other_phone']          = $post['u_other_phone'];
                    $companyData['c_other_phone_inner']    = $post['u_other_phone_inner'];
                    $companyData['c_owner_u_id']           = $uId;
                    $companyData['c_primary_contact_u_id'] = $uId;
                    $companyData['c_active']               = 1;

                    $company->exchangeArray($companyData);

                    $cId = $this->getCompanyTable()->addClientCompany($company);

                    if($cId) {
                        $user->u_id         = $uId;
                        $user->u_company_id = $cId;

                        $this->getUserTable()->saveUser($user);
                    }
                }

                $this->flashMessenger()->addSuccessMessage('Email with confirmation link was sent');

                $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'registration'));

            }
        }

        return array(
            'form' => $form
        );
    }

    public function confirmAction()
    {
        $id   = $this->params()->fromRoute('id') ? $this->params()->fromRoute('id') : null;
        $hash = $this->params()->fromRoute('hash') ? $this->params()->fromRoute('hash') : null;

        if ($id && $hash) {
            if($this->getUserTable()->setConfirmed($id, $hash)) {
                $this->flashMessenger()->addSuccessMessage('Account succesfuly confirmed. Please, login.');
            }
        }

        return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
    }
}
