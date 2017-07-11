<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Dashboard\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;


use Businessassociate\Form\BusinessassociateForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Businessassociate\Model\Businessassociate;
use Businessassociate\Model\Businessassociateuser;
use Note\Model\Note;
use Mail\Model\Mailtemplate;
use Zend\Session\Container;

class DashboardController extends AbstractActionController
{
    protected $businessassociateTable;
    protected $businessassociateanswerTable;
    protected $userTable;
    protected $noteTable;
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
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

    public function getBusinessassociateTable()
    {
        if (!$this->businessassociateTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateTable = $sm->get('Businessassociate\Model\BusinessassociateTable');
        }
        return $this->businessassociateTable;
    }

    public function getBusinessassociateanswerTable()
    {
        if (!$this->businessassociateanswerTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateanswerTable = $sm->get('Businessassociate\Model\BusinessassociateanswerTable');
        }
        return $this->businessassociateanswerTable;
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getMailtemplateTable()
    {
        if (!$this->mailtemplateTable) {
            $sm = $this->getServiceLocator();
            $this->mailtemplateTable = $sm->get('Mail\Model\MailtemplateTable');
        }
        return $this->mailtemplateTable;
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

    public function clientAction()
    {
        $container = new Container('activity');
        $container->activity = time();
        $container = new Container('files');
        $identity  = $this->getIdentity();
        
        if ($container->item != '') {
            $container->item = '';
            $this->redirect()->toUrl($container->item);
        }

        $container = new Container('files');

        $viewModel = new ViewModel(array(
            'usRoleId' => $identity['u_role_id']
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open client dashboard page');

        return $viewModel;
    }

    public function trailAction()
    {
        $container = new Container('activity');
        $container->activity = time();
        $container = new Container('files');
        $identity  = $this->getIdentity();
        
        if ($container->item != '') {
            $container->item = '';
            $this->redirect()->toUrl($container->item);
        }

        $container = new Container('files');

        $viewModel = new ViewModel(array(
            'usRoleId' => $identity['u_role_id']
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open trail user dashboard page');

        return $viewModel;
    }

    public function consultantAction()
    {
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1, 2, 3))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $container = new Container('activity');
        $container->activity = time();
        $container = new Container('files');

        if ($container->item != '') {
            $container->item = '';
            $this->redirect()->toUrl($container->item);
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->setServiceLocator($this->getServiceLocator());
        $logsOpen = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(true);
        $logsActivities = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(false);

        $logsUpcoming = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(false, 0, true);

        $viewModel = new ViewModel(array(
            'logsOpen' => $logsOpen,
            'logsActivities' => $logsActivities,
            'logsUpcoming' => $logsUpcoming,
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open consultant dashboard page');

        return $viewModel;
    }

    public function salesrepAction()
    {
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 4))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $container = new Container('activity');
        $container->activity = time();
        $container = new Container('files');

        if ($container->item != '') {
            $container->item = '';
            $this->redirect()->toUrl($container->item);
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->setServiceLocator($this->getServiceLocator());
        $logsActivities = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(false);

        $viewModel = new ViewModel(array(
            'logsActivities' => $logsActivities,
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open salesrep dashboard page');

        return $viewModel;
    }

    public function adminAction()
    {
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $container = new Container('activity');
        $container->activity = time();
        $container = new Container('files');

        if ($container->item != '') {
            $container->item = '';
            $this->redirect()->toUrl($container->item);
        }


        $c1 = (int) $this->params('c1');
        $c2 = (int) $this->params('c2');
        $c3 = (int) $this->params('c3');
        $c4 = (int) $this->params('c4');

        $blReportUnReportable = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->getForReport($c1, 0);
        $blReportReportable = $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->getForReport($c1, 1);

        $aReportInProgress = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForReport($c2, 10);
        $aReportCompleted = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForReport($c2, 100);

        $brpReportInProgress = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable')->getForReport($c3, 1);
        $brpReportCompleted = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable')->getForReport($c3, 2);

        $rpReportInProgress = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getForReport($c4, 1);
        $rpReportCompleted = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getForReport($c4, 2);

        $viewModel = new ViewModel(array(
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
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open admin dashboard page');

        return $viewModel;
    }

    public function getmoreAction()
    {
        $container = new Container('activity');
        $container->activity = time();

        $type = $this->params('type');
        $lastId = $this->params('id');

        $docRoot = $_SERVER['SERVER_NAME'] == 'hipaa' ? $_SERVER['DOCUMENT_ROOT'] . '/..' : $_SERVER['DOCUMENT_ROOT'];
        $renderer = new PhpRenderer();

        $this->getServiceLocator()->get('Application\Model\LogsTable')->setServiceLocator($this->getServiceLocator());
        if ($type == 'activities') {
            $logs = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(false, $lastId);

            $map = new Resolver\TemplateMapResolver(array(
                'dashboard/dashboard' => $docRoot . '/module/Dashboard/view/dashboard/dashboard/_logs_activities.phtml',
            ));

        } elseif ($type == 'open') {
            $logs = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(true, $lastId);
            $map = new Resolver\TemplateMapResolver(array(
                'dashboard/dashboard' => $docRoot . '/module/Dashboard/view/dashboard/dashboard/_logs_open.phtml',
            ));
        } elseif ($type == 'upcoming') {
            $logs = $this->getServiceLocator()->get('Application\Model\LogsTable')->getLogs(false, $lastId, true);
            $map = new Resolver\TemplateMapResolver(array(
                'dashboard/dashboard' => $docRoot . '/module/Dashboard/view/dashboard/dashboard/_logs_upcoming.phtml',
            ));
        }

        $renderer->setResolver($map);

        $formatDateA = $this->getServiceLocator()->get('viewhelpermanager');
        $renderer->setHelperPluginManager($formatDateA);

        $model = new ViewModel(array(
            'logsActivities' => $logs,
            'logsOpen' => $logs,
            'logsUpcoming' => $logs,
        ));
        $model->setTemplate('dashboard/dashboard');

        $html = $renderer->render($model);

        return $this->getResponse()->setContent(json_encode($html));
    }

    public function checkactivityAction()
    {
        $container = new Container('activity');
        $time = $container->activity;

        $sub = time() - $time; // sec

        $show = 0;
        if ($sub > 1800) { // 30 min.
        //if ($sub > 10) { // 30 min.
            $show = 1;
        }

        return $this->getResponse()->setContent($show);
    }

    public function resetactivityAction()
    {
        $container = new Container('activity');
        $container->activity = time();

        return $this->getResponse()->setContent(1);
    }


    public function zendeskAction()
    {
        ini_set('display_errors', 1);
        error_reporting(255);
        $data = curlWrap("/users.json", null, "GET");

        echo '<pre>';
        print_r($data);
        die;
    }


}

define("ZDAPIKEY", "e2asX5c1tjmJo8CPb4mwD4oAbe3KFaIwai9s9YC8");
define("ZDUSER", "tb82@interia.pl");
define("ZDURL", "https://tb82.zendesk.com/api/v2");

/* Note: do not put a trailing slash at the end of v2 */

function curlWrap($url, $json, $action)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
    curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
    curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);

    switch($action){
        case "POST":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            break;
        case "GET":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            break;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($output);
    return $decoded;
}
