<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Sitesetting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Sitesetting\Form\SitesettingForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Sitesetting\Model\Sitesetting;
use Zend\Session\Container;

class SitesettingController extends AbstractActionController
{
    protected $userTable;
    protected $noteTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

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

    public function getSitesettingTable()
    {
        if (!$this->SitesettingTable) {
            $sm = $this->getServiceLocator();
            $this->SitesettingTable = $sm->get('Sitesetting\Model\SitesettingTable');
        }
        return $this->SitesettingTable;
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        
        $ssObjs = $this->getSitesettingTable()->getSitesettings();

        if ($request->isPost()) {
            $st   = new Sitesetting();
            $post = $request->getPost();

            foreach ($post as $item) {
                $st->exchangeArray($item);
                $this->getSitesettingTable()->saveSitesetting($st);
            }

            return $this->redirect()->toRoute('sitesetting', array('controller' => 'sitesetting', 'action' => 'index'));

        }

        return array(
            'ssObjs' => $ssObjs
        );
    }

}
