<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Form\UserForm;
use Admin\Model\User;

class AdminController extends AbstractActionController
{
    protected $userTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();

        $this->layout()->bodyClass = 'admin';
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1))) {
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

    public function indexAction()
    {
        return new ViewModel();
    }

    public function dashboardAction()
    {

    }

    public function usersAction()
    {
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $mappingSortCol = array(
            'id' => 'u_id',
            'name' => 'u_lastname',
            'role' => 'u_role_id',
            'status' => 'u_status',
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'u_id';
        $paginator = $this->getUserTable()->fetchAll(true, $sortCol, $order, $roleFilter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open users list page');

        return $view;
    }

    public function remediationsplansAction()
    {
        return new ViewModel();
    }

    public function edituserAction()
    {
        $id = (int) $this->params('id');

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $identity = $this->getIdentity();

        $userObj = null;

        if ((int) $id) {
            $userObj = $this->getUserTable()->getUser($id);
        }

        $uRoleId = is_object($userObj) ? $userObj->u_role_id : null;

        $form = new UserForm($this->getServiceLocator(), $uRoleId);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();
            $uId = is_object($userObj) ? $userObj->u_id : 0;
            $form->setInputFilter($user->getSimpleInputFilter($this->getServiceLocator(), $id, $uId));
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $post = $request->getPost();
                $post['u_confirmed'] = $uId ? $userObj->u_confirmed : null;
                $post['u_company_id'] = $post['u_company_id'] ? $post['u_company_id'] : (is_object($userObj) ? $userObj->u_company_id : null);

                if($post['u_role_id'] == \Admin\Model\User::ROLE_CLIENT && (!$userObj || $userObj->u_role_id == \Admin\Model\User::ROLE_PARTIAL)) {
                    $post['u_senior_consultant_u_id'] = $identity['u_id'];
                }

                $user->exchangeArray($post);
                $saveUserId = $this->getUserTable()->saveUser($user);
                if((int) $id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update user "' . $saveUserId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new user "' . $saveUserId . '"');
                }

                $this->flashMessenger()->addSuccessMessage('User has been added');

                $this->redirect()->toRoute('admin', array('controller' => 'admin', 'action' => 'users'));

            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                    //echo "Validation failure '$messageId': $message\n";
                }
            }
        } else {
            if ((int) $id) {
                $form->bind($userObj);
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit user "' . $id . '" page');
            } else {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new user page');
            }
        }

        $c1 = (int) $this->params('c1');
        $c2 = (int) $this->params('c2');
        $c3 = (int) $this->params('c3');
        $c4 = (int) $this->params('c4');
        $st = (int) $this->params('st');

        $blReportUnReportable = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->getForReport($c1, 0, $id);
        $blReportReportable = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->getForReport($c1, 1, $id);

        $aReportInProgress = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForReport($c2, 10, $id);
        $aReportCompleted = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForReport($c2, 100, $id);

        $brpReportInProgress = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable')->getForReport($c3, 1, $id);
        $brpReportCompleted = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable')->getForReport($c3, 2, $id);

        $rpReportInProgress = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getForReport($c4, 1, $id);
        $rpReportCompleted = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getForReport($c4, 2, $id);

        return array(
            'form' => $form,
            'uId' => $id,
            'roleId' => is_object($userObj) ? $userObj->u_role_id : -1,
            'isActive' => is_object($userObj) ? $userObj->u_active : -1,
            'username' => is_object($userObj) ? $userObj->u_firstname . ' ' . $userObj->u_lastname : '',
            'c1Param' => $c1,
            'c2Param' => $c2,
            'c3Param' => $c3,
            'c4Param' => $c4,
            'blReportUnReportable' => $blReportUnReportable,
            'blReportReportable' => $blReportReportable,
            'aReportInProgress' => $aReportInProgress,
            'aReportCompleted' => $aReportCompleted,
            'brpReportInProgress' => $brpReportInProgress,
            'brpReportCompleted' => $brpReportCompleted,
            'rpReportInProgress' => $rpReportInProgress,
            'rpReportCompleted' => $rpReportCompleted,
            'stParam' => $st,
        );
    }

    public function deleteuserAction()
    {
        $id = $this->params('id');

        $this->getUserTable()->deleteUser($id);
        $this->flashMessenger()->addSuccessMessage('User has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete client "' . $id . '"');

        return $this->redirect()->toRoute('admin', array('controller' => 'admin', 'action' => 'users'));
    }

    public function unarchiveuserAction()
    {
        $id = $this->params('id');

        $this->getUserTable()->unarchiveUser($id);
        $this->flashMessenger()->addSuccessMessage('User has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive client "' . $id . '"');

        return $this->redirect()->toRoute('admin', array('controller' => 'admin', 'action' => 'users'));
    }
}

