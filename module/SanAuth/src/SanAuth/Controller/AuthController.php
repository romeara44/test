<?php

namespace SanAuth\Controller;

use SanAuth\Form\AuthForm;
use SanAuth\Form\ForgetForm;
use SanAuth\Form\NewPasswordForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

use SanAuth\Model\User;

class AuthController extends AbstractActionController
{
    protected $form;
    protected $storage;
    protected $authservice;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        return parent::onDispatch($e);
    }

    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }
    
    public function getSessionStorage()
    {
        if (!isset($this->storage)) {
            $this->storage = $this->getServiceLocator()->get('SanAuth\Model\MyAuthStorage');
        }
        
        return $this->storage;
    }
    

    public function getUserTable()
    {
        if (!isset($this->userTable)) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function loginAction()
    {
        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()){
            $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new AuthForm();

        return array(
            'form'      => $form,
            'flashMessagesErrors' => $this->flashMessenger()->getErrorMessages()
        );
    }

    public function forgotpasswordAction()
    {
        $this->layout('layout/layout_login');
        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()){
            $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new ForgetForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new \Admin\Model\User();
            $form->setInputFilter($user->getForgetInputFilter($this->getServiceLocator()));
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $post = $request->getPost();
                $email = $post['u_email'];
                $userTable = $this->getServiceLocator()->get('Admin\Model\UserTable');

                $ret = $userTable->sendPasswordForgotRequest($email);

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send forgot password email');

                if ($ret) {
                    $this->flashMessenger()->addSuccessMessage('Thank You. You will receive new password on email as soon as posible');
                } else {
                    $this->flashMessenger()->addErrorMessage('E-mail doesn\'t exists.');
                    return $this->redirect()->toRoute('auth', array('controller' => 'auth', 'action' => 'forgotpassword'));
                }

                return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));

            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                    //echo "Validation failure '$messageId': $message\n";
                }
            }
        }

        return array(
            'form'      => $form,
        );
    }

    public function newpasswordAction()
    {
        $this->layout('layout/layout_login');
        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()){
            $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $uid = $this->params('id');
        $hash = $this->params('hash');


        $userTable = $this->getServiceLocator()->get('Admin\Model\UserTable');
        $ret = $userTable->checkIfUserExistsByIdAndHash($uid, $hash);

        if (!$ret) {
            $this->flashMessenger()->addErrorMessage('Konto nie istnieje.');
            $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new NewPasswordForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new \Admin\Model\User();
            $post = $request->getPost();
            $form->setData($request->getPost());

            $password = trim($post['u_password']);
            $newPassword = trim($post['u_password_repeat']);
            $passwordMatch = ($password == $newPassword) ? true : false;;

            if ($form->isValid() && $passwordMatch) {
                $post = $request->getPost();
                $userTable = $this->getServiceLocator()->get('Admin\Model\UserTable');

                $ret = $userTable->setNewPassword($uid, $password);

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send new password email');

                if ($ret) {
                    $this->flashMessenger()->addSuccessMessage('Passoword changed.');
                }
                $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
            } else {
                if (!$passwordMatch) {
                    $form->get('u_password_repeat')->setMessages(array('error' => 'Password doesn\'t match'));
                }

                foreach ($form->getMessages() as $messageId => $message) {
                    //echo "Validation failure '$messageId': $message\n";
                }
            }
        }

        return array(
            'form'      => $form,
            'id' => $uid,
            'hash' => $hash
        );
    }

    public function indexAction()
    {
        $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
    }

    public function authenticateAction()
    {
        $form = new AuthForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {

            $user = new \Admin\Model\User();
            $form->setInputFilter($user->getLoginInputFilter($this->getServiceLocator()));
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $usersDb = $this->getServiceLocator()->get('Admin\Model\UserTable');
                $user    = $usersDb->getUserByEmail($request->getPost('u_email'));

                if($user) {
                    if(!$user->u_active || $user->u_locked || !$user->u_confirmed) {
                        $this->getSessionStorage()->forgetMe();
                        $this->getAuthService()->clearIdentity();

                        if($user->u_locked) {
                            $this->flashmessenger()->addErrorMessage('You are locked. Please contact admin.');
                            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_AUTH_LOCKED, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $user->u_id);
                        } else {
                            $this->flashmessenger()->addErrorMessage('Wrong email or password. Please try again.');
                        }

                        return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
                    }

                    $config = $this->getServiceLocator()->get('config');

                    if(file_exists($config['application_vars']['secure_db_key_file']) && file_exists($config['application_vars']['secure_file_key_file'])) {
                        $config['application_vars']['secure_db_key']   = file_get_contents($config['application_vars']['secure_db_key_file']);
                        $config['application_vars']['secure_file_key'] = file_get_contents($config['application_vars']['secure_file_key_file']);
                    } else {
                        $this->getSessionStorage()->forgetMe();
                        $this->getAuthService()->clearIdentity();

                        return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
                    }

                    $container = new Container('application_vars');
                    $container->storage = $config['application_vars'];
                }

                $this->getAuthService()->getAdapter()
                                       ->setIdentity($request->getPost('u_email'))
                                       ->setCredential($request->getPost('u_password'));

                $result = $this->getAuthService()->authenticate();

                if ($result->isValid()) {
                    //check if it has rememberMe :
                    if ($request->getPost('u_remember_me') == 1) {
                        $this->getSessionStorage()->setRememberMe(1);
                        //set storage again
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }

                    $this->getAuthService()->setStorage($this->getSessionStorage());

                    $dataStorage['u_email']                  = $request->getPost('u_email');
                    $dataStorage['u_firstname']              = $user->u_firstname;
                    $dataStorage['u_title']                  = $user->u_title;
                    $dataStorage['u_lastname']               = $user->u_lastname;
                    $dataStorage['u_role_id']                = $user->u_role_id;
                    $dataStorage['u_id']                     = $user->u_id;
                    $dataStorage['u_senior_consultant_u_id'] = $user->u_senior_consultant_u_id;
                    $dataStorage['u_company_id']             = $user->u_company_id;
                    $dataStorage['u_office_phone']           = $user->u_office_phone;
                    $dataStorage['u_register']               = $user->u_register;
                    $dataStorage['u_first_login']            = $user->u_first_login;
                    $dataStorage['u_company_id_admin']       = $user->u_company_id_admin;
                    $dataStorage['u_grant_to_disclosures']   = $user->u_grant_to_disclosures;
                    $dataStorage['u_grant_to_breach']        = $user->u_grant_to_breach;
                    $dataStorage['has_modules_access']       = $user->u_role_id == \Admin\Model\User::ROLE_ADMIN ? 1 : 0;

                    $this->getAuthService()->getStorage()->write($dataStorage);

                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Authenticate');

                    $this->getServiceLocator()->get('Admin\Model\UserTable')->updateFailedLoginCount($user, true);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_AUTH_SUCCESS, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $user->u_id);

                    $this->flashmessenger()->addSuccessMessage('Log in');
                } else {
                    if(isset($user->u_id)) {
                        $this->getServiceLocator()->get('Admin\Model\UserTable')->updateFailedLoginCount($user);
                        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_AUTH_FAILED, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $user->u_id);
                    }
                    $this->flashmessenger()->addErrorMessage('Wrong email or password. Please try again.');
                }
            } else {
                $this->flashmessenger()->addErrorMessage('Wrong');
            }
        }
        
        return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
    }
    
    public function logoutAction()
    {
        if ($this->getAuthService()->hasIdentity()) {

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Logout');

            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();
            $this->flashmessenger()->addSuccessMessage("Log out");
        }

        $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
    }

    public function checkAccessForModulesAction()
    {
        if($this->getAuthService()->hasIdentity()) {

            $identity = $this->getAuthService()->getIdentity();
            $key      = $this->getUserTable()->getModulesAccessCode($identity['u_id']);

            if($identity['has_modules_access'] == 1) {
                return new JsonModel(array('result' => 1));
            } else {
                return new JsonModel(array('result' => 0));
            }
        }

        $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
    }

    public function getAccessForModulesAction()
    {
        $request = $this->getRequest();

        if($this->getAuthService()->hasIdentity() && $request->isPost()) {

            $identity = $this->getAuthService()->getIdentity();

            $htmlEntities = new \Zend\Filter\HtmlEntities();

            $action = $htmlEntities->filter($request->getPost('action'));
            $code   = $htmlEntities->filter($request->getPost('code'));

            if($action == 'send_code') {
                if($this->getUserTable()->sendModulesAccessCode($identity['u_id'])) {
                    return new JsonModel(array('result' => 1));
                }
            } else if($action == 'get_access' && isset($code)) {
                if($this->getUserTable()->getModulesAccess($identity['u_id'], $code)) {
                    $identity['has_modules_access'] = 1;

                    $this->getAuthService()->setStorage($this->getSessionStorage());
                    $this->getAuthService()->getStorage()->write($identity);
                    return new JsonModel(array('result' => 1));
                }
            }
        }

        return new JsonModel(array('result' => 0));
    }
}