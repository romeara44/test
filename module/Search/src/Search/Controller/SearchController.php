<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Search\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class SearchController extends AbstractActionController
{
    protected $searchTable;

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
        if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getSearchTable()
    {
        if (!$this->searchTable) {
            $sm = $this->getServiceLocator();
            $this->searchTable = $sm->get('Search\Model\SearchTable');
        }
        return $this->searchTable;
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

    public function searchAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $query = $post['search'];
            $query = str_replace(' ', '-', $query);
            if (empty($post['roleFilter'])) {
                return $this->redirect()->toRoute('search', array('controller' => 'search', 'action' => 'search', 'searchValue' => $query));    
            } else {
                return $this->redirect()->toRoute('search', array('controller' => 'search', 'action' => 'search', 'searchValue' => $query, 'roleFilter' => $post['roleFilter']));
            }
            
        }

        $query = $this->params('searchValue');
        $query = str_replace('-', ' ', $query);

        $this->layout()->query = $query;

        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'type';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? $this->params()->fromRoute('roleFilter') : 'a';

        $mappingSortCol = array(
            'type' => '_type',
            'name' => '_name',
            'date' => '_date',
            'status' => '_status',
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : '_type';

        $this->getSearchTable()->setServiceLocator($this->getServiceLocator());

        //$query = 'SEnio company';

        $paginator = $this->getSearchTable()->getResults(true, $sortCol, $order, $this->getIdentity(), $query, $roleFilter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $query = str_replace(' ', '-', $query);
        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter,
            'searchValue' => $query
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Search "' . $query . '"');

        return $view;

    }
}
