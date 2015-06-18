<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Reporting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Session\Container;
use Reporting\Form\PlanprogressForm;
use Reporting\Form\ReportpageForm;
use Reporting\Model\Planprogress;
use Assessment\Model\Remediationplan;
use Assessment\Model\Remediationplanaction;
use Breachlog\Model\Breachremediationplan;
use Breachlog\Model\Breachremediationplanaction;

class ReportingController extends AbstractActionController
{
    public $countPerPage = 10;

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
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 4, 5))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
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

    public function getTraininglogTable()
    {
        if (!isset($this->traininglogTable)) {
            $sm = $this->getServiceLocator();
            $this->traininglogTable = $sm->get('Traininglog\Model\TraininglogTable');
        }
        return $this->traininglogTable;
    }

    public function getBreachremediationplanTable()
    {
        if (!isset($this->breachremediationplanTable)) {
            $sm = $this->getServiceLocator();
            $this->breachremediationplanTable = $sm->get('Breachlog\Model\BreachremediationplanTable');
        }
        return $this->breachremediationplanTable;
    }

    public function getRemediationplanactionTable()
    {
        if (!isset($this->remediationplanactionTable)) {
            $sm = $this->getServiceLocator();
            $this->remediationplanactionTable = $sm->get('Assessment\Model\RemediationplanactionTable');
        }
        return $this->remediationplanactionTable;
    }

    public function getRemediationplanTable()
    {
        if (!isset($this->remediationplanTable)) {
            $sm = $this->getServiceLocator();
            $this->remediationplanTable = $sm->get('Assessment\Model\RemediationplanTable');
        }
        return $this->remediationplanTable;
    }

    public function getBreachremediationplanactionTable()
    {
        if (!isset($this->breachremediationplanactionTable)) {
            $sm = $this->getServiceLocator();
            $this->breachremediationplanactionTable = $sm->get('Breachlog\Model\BreachremediationplanactionTable');
        }
        return $this->breachremediationplanactionTable;
    }

    public function hasIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->hasIdentity();

        return $identity;
    }

    public function auditBreachAction()
    {
        $page   = 1;
        $search = $this->params()->fromRoute('search') ? $this->params()->fromRoute('search') : null;

        if($search) {
            $tLPaginator = $this->getTraininglogTable()->getTraininglogsForReporting($search);

            $tLPaginator->setCurrentPageNumber($page);
            $tLPaginator->setItemCountPerPage($this->countPerPage);

            $bRPPaginator = $this->getBreachremediationplanTable()->getBreachRemediationPlansForReporting($search);

            $bRPPaginator->setCurrentPageNumber($page);
            $bRPPaginator->setItemCountPerPage($this->countPerPage);

            $rPAPaginator = $this->getRemediationplanactionTable()->getRemediationPlanActionsForReporting($search);

            $rPAPaginator->setCurrentPageNumber($page);
            $rPAPaginator->setItemCountPerPage($this->countPerPage);
        } else {
            $tLPaginator  = null;
            $bRPPaginator = null;
            $rPAPaginator = null;
        }

        $view = new ViewModel(array(
            'order_by'     => 'id',
            'order'        => 'DESC',
            'page'         => $page,
            'tLPaginator'  => $tLPaginator,
            'bRPPaginator' => $bRPPaginator,
            'rPAPaginator' => $rPAPaginator,
            'search'       => $search
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open reporting audit breach list page');

        return $view;
    }

    public function auditBreachRPAAction()
    {
        $request = $this->getRequest();
        
        if($request->isXmlHttpRequest()){
            $page   = $this->params()->fromRoute('page')   ? $this->params()->fromRoute('page')   : null;
            $search = $this->params()->fromRoute('search') ? $this->params()->fromRoute('search') : null;

            $rPAPaginator = $this->getRemediationplanactionTable()->getRemediationPlanActionsForReporting($search);

            $rPAPaginator->setCurrentPageNumber($page);
            $rPAPaginator->setItemCountPerPage($this->countPerPage);

            $view = new ViewModel;

            $view->setVariables(array(
                'order_by'     => 'id',
                'order'        => 'DESC',
                'page'         => $page,
                'rPAPaginator' => $rPAPaginator,
                'search'       => $search
            ));

            $view->setTemplate( 'reporting/reporting/auditbreach_rpa.phtml' );

            $view->setTerminal(true);
        }

        return $view;
    }

    public function auditBreachBRPAction()
    {
        $request = $this->getRequest();
        
        if($request->isXmlHttpRequest()){
            $page   = $this->params()->fromRoute('page')   ? $this->params()->fromRoute('page')   : null;
            $search = $this->params()->fromRoute('search') ? $this->params()->fromRoute('search') : null;

            $bRPPaginator = $this->getBreachremediationplanTable()->getBreachRemediationPlansForReporting($search);

            $bRPPaginator->setCurrentPageNumber($page);
            $bRPPaginator->setItemCountPerPage($this->countPerPage);

            $view = new ViewModel;

            $view->setVariables(array(
                'order_by'     => 'id',
                'order'        => 'DESC',
                'page'         => $page,
                'bRPPaginator' => $bRPPaginator,
                'search'       => $search
            ));

            $view->setTemplate( 'reporting/reporting/auditbreach_brp.phtml' );

            $view->setTerminal(true);
        }

        return $view;
    }

    public function auditBreachTLAction()
    {
        $request = $this->getRequest();
        
        if($request->isXmlHttpRequest()){
            $page   = $this->params()->fromRoute('page')   ? $this->params()->fromRoute('page')   : null;
            $search = $this->params()->fromRoute('search') ? $this->params()->fromRoute('search') : null;

            $tLPaginator = $this->getTraininglogTable()->getTraininglogsForReporting($search);

            $tLPaginator->setCurrentPageNumber($page);
            $tLPaginator->setItemCountPerPage($this->countPerPage);

            $view = new ViewModel;

            $view->setVariables(array(
                'order_by'     => 'id',
                'order'        => 'DESC',
                'page'         => $page,
                'tLPaginator'  => $tLPaginator,
                'search'       => $search
            ));

            $view->setTemplate( 'reporting/reporting/auditbreach_tl.phtml' );

            $view->setTerminal(true);
        }

        return $view;
    }

    public function planProgressAction()
    {
        $form = new PlanprogressForm($this->getServiceLocator());
        
        $pp = new Planprogress();

        $request = $this->getRequest();

        if ($request->isGet()) {
            $get = $request->getQuery();

            $form->setInputFilter($pp->getInputFilter($this->getServiceLocator()));
            $form->setData($get);
        }
        
        $identity = $this->getIdentity();

        $page = $this->params()->fromRoute('page') ? $this->params()->fromRoute('page') : null;

        $company  = isset($get['company'])  ? $get['company']  : null;
        $type     = isset($get['type'])     ? $get['type']     : null;
        $status   = isset($get['status'])   ? $get['status']   : null;

        $statuses['breach']      = Breachremediationplan::$statusesNames;
        $statuses['remediation'] = Remediationplan::$statusesNames;

        $typeMap = array( 'security' => 1, 'privacy' => 2, 'imported' => 'imported', 'breach' => 'breach');

        $paginator = $this->getRemediationplanTable()->getRemediationPlansForPlanProgress($company, isset($typeMap[$type]) ? $typeMap[$type] : null, $status);

        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->countPerPage);

        $view = new ViewModel(array(
            'order_by'  => 'id',
            'order'     => 'DESC',
            'page'      => $page,
            'paginator' => $paginator,
            'company'   => $company,
            'type'      => $type,
            'status'    => $status,
            'statuses'  => $statuses,
            'identity'  => $identity,
            'form'      => $form
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open reporting audit breach list page');

        return $view;
    }

    public function reportPageAction()
    {
        $form = new ReportpageForm($this->getServiceLocator());
        
        $pp = new Planprogress();

        $request = $this->getRequest();

        if ($request->isGet()) {
            $get = $request->getQuery();

            $form->setInputFilter($pp->getInputFilter($this->getServiceLocator()));
            $form->setData($get);
        }
        
        $identity = $this->getIdentity();

        $page = $this->params()->fromRoute('page') ? $this->params()->fromRoute('page') : null;
        $type = $this->params()->fromRoute('type') ? $this->params()->fromRoute('type') : null;
        $id   = $this->params()->fromRoute('id')   ? $this->params()->fromRoute('id')   : null;

        $status = isset($get['status']) ? $get['status'] : null;

        $statuses['breach']      = Breachremediationplanaction::$statusesNames;
        $statuses['remediation'] = Remediationplanaction::$statusesNames;

        if($type == 'breach') {
            $paginator = $this->getBreachremediationplanactionTable()->getBreachRemediationPlanAnactionsForReportPage($id, $status);
        } else {
            $paginator = $this->getRemediationplanactionTable()->getRemediationPlanActionsForReportPage($id, $status);
        }

        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->countPerPage);

        $view = new ViewModel(array(
            'order_by'  => 'id',
            'order'     => 'DESC',
            'id'        => $id,
            'page'      => $page,
            'paginator' => $paginator,
            'type'      => $type,
            'status'    => $status,
            'statuses'  => $statuses,
            'identity'  => $identity,
            'form'      => $form
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open reporting audit breach list page');

        return $view;
    }
}
