<?php

namespace SanAuth\Controller;

use SanAuth\Form\AuthForm;
use SanAuth\Form\ForgetForm;
use SanAuth\Form\NewPasswordForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

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
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()->get('SanAuth\Model\MyAuthStorage');
        }
        
        return $this->storage;
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

                $ret = $userTable->sendPasswordReminder($email);

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send forgot password email');

                if ($ret) {
                    $this->flashMessenger()->addSuccessMessage('Instruction on your email');
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
        $redirect = 'login';
        
        $request = $this->getRequest();
        if ($request->isPost()) {

            $user = new \Admin\Model\User();
            $form->setInputFilter($user->getLoginInputFilter($this->getServiceLocator()));
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $usersTable = $this->getServiceLocator()->get('UsersTableGateway');
                $usersDb = new \Admin\Model\UserTable($usersTable);
                $user = $usersDb->getUserByEmail($request->getPost('u_email'));

                if($user['u_confirmed'] == 0) {
                    $this->flashmessenger()->addErrorMessage('Wrong email or password. Please try again.');
                    return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
                }

                $this->getAuthService()->getAdapter()
                                       ->setIdentity($request->getPost('u_email'))
                                       ->setCredential($request->getPost('u_password'));

                $result = $this->getAuthService()->authenticate();

                foreach($result->getMessages() as $message)
                {
                    //save message temporary into flashmessenger
                    //$this->flashmessenger()->addSuccessMessage($message);
                }
                
                if ($result->isValid()) {
                    $this->flashmessenger()->addSuccessMessage('Log in');
                    $redirect = 'index';
                    //check if it has rememberMe :
                    if ($request->getPost('u_remember_me') == 1) {
                        $this->getSessionStorage()
                             ->setRememberMe(1);
                        //set storage again
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }
                    $this->getAuthService()->setStorage($this->getSessionStorage());

                    $user = $usersDb->getUserByEmail($request->getPost('u_email'));

                    $dataStorage['u_email'] = $request->getPost('u_email');
                    $dataStorage['u_firstname'] = $user->u_firstname;
                    $dataStorage['u_title'] = $user->u_title;
                    $dataStorage['u_lastname'] = $user->u_lastname;
                    $dataStorage['u_role_id'] = $user->u_role_id;
                    $dataStorage['u_id'] = $user->u_id;
                    $dataStorage['u_senior_consultant_u_id'] = $user->u_senior_consultant_u_id;
                    $dataStorage['u_company_id'] = $user->u_company_id;
                    $dataStorage['u_office_phone'] = $user->u_office_phone;
                    $dataStorage['u_register'] = $user->u_register;
                    $dataStorage['u_first_login'] = $user->u_first_login;
                    $dataStorage['u_company_id_admin'] = $user->u_company_id_admin;

                    $this->getAuthService()->getStorage()->write($dataStorage);

                    if (!$user->u_active) {
                        $this->getSessionStorage()->forgetMe();
                        $this->getAuthService()->clearIdentity();

                        $this->flashmessenger()->addErrorMessage('Wrong email or password. Please try again.');
                    }

                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Authenticate');


                    $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
                } else {
                    $this->flashmessenger()->addErrorMessage('Wrong email or password. Please try again.');
                    $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
                }
            } else {
                $this->flashmessenger()->addErrorMessage('Wrong');
                $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
            }
        }
        
        return $this->redirect()->toRoute($redirect);
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
}