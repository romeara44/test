<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();

        return parent::onDispatch($e);
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
        $this->layout()->bodyId = 'pFirst';

        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if ($this->hasIdentity()) {
            $identity = $this->getIdentity();
            
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open index page');

            if ($identity['u_role_id'] == 1) {
                return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'admin'));
            } elseif ($identity['u_first_login'] == 1) {
                return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
            }  elseif (in_array($identity['u_role_id'], array(2, 3))) {
                return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'consultant'));
            } elseif (in_array($identity['u_role_id'], array(6))) {
                return $this->redirect()->toRoute('businessassociateuser', array('controller' => 'businessassociateuser', 'action' => 'questionsform'));
            } elseif (in_array($identity['u_role_id'], array(5))) {
                return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
            } elseif (in_array($identity['u_role_id'], array(4))) {
                return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'salesrep'));
            } elseif (in_array($identity['u_role_id'], array(7))) {
                return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
            }

        } else {
            $this->layout('layout/layout_login');
        }

        /*
        $sm = $this->getServiceLocator();
        $practiceTable = $sm->get('Practice\Model\PracticeTable');
        $last5practices = $practiceTable->fetchAll(true, null, null, null, array('last5' => true));
        $last5practices->setItemCountPerPage(5);
        $view = new ViewModel(array(
            'last5practices' => $last5practices,
        ));

        */
    }

}
