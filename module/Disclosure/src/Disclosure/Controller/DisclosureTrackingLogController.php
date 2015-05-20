<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Disclosure\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Disclosure\Model\DisclosureTrackingLog;
use Disclosure\Form\DisclosureTrackingLogForm;
use Admin\Model\User;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class DisclosureTrackingLogController extends AbstractActionController
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
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 4, 5, 7))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
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

    public function getDisclosureTrackingLogTable()
    {
        if (!isset($this->disclosureTrackingLogTable)) {
            $sm = $this->getServiceLocator();
            $this->disclosureTrackingLogTable = $sm->get('Disclosure\Model\DisclosureTrackingLogTable');
        }
        return $this->disclosureTrackingLogTable;
    }

    public function getCompanyRolesTable()
    {
        if (!isset($this->companyRolesTable)) {
            $sm = $this->getServiceLocator();
            $this->companyRolesTable = $sm->get('Client\Model\CompanyRolesTable');
        }
        return $this->companyRolesTable;
    }

    public function hasIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->hasIdentity();

        return $identity;
    }

    public function listAction()
    {
        $orderBy    = $this->params()->fromRoute('order_by')   ? $this->params()->fromRoute('order_by')         : 'id';
        $order      = $this->params()->fromRoute('order')      ? $this->params()->fromRoute('order')            : 'DESC';
        $page       = $this->params()->fromRoute('page')       ? (int) $this->params()->fromRoute('page')       : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;
        $search     = $this->params()->fromRoute('search')     ? $this->params()->fromRoute('search')           : null;

        $mappingSortCol = array(
            'reference_number'     => 'dtl_reference_number',
            'patient_name'         => 'dtl_patient_name',
            'medical_record_number' => 'dtl_medical_record_number',
            'date_received' => 'dtl_date_received',
            'name_of_requestor'         => 'dtl_name_of_requestor',
            'auth_type'   => 'dtl_auth_type',
            'date_disclosed'        => 'dtl_date_disclosed',
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'dtl_id';
        $paginator = $this->getDisclosureTrackingLogTable()->getDisclosureTrackingLogs(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by'    => $orderBy,
            'order'       => $order,
            'page'        => $page,
            'paginator'   => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter'  => $roleFilter,
            'search'      => $search
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open disclosure tracking log list page');

        return $view;
    }

    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $identity = $this->getIdentity();

        // if(!$this->getCompanyRolesTable()->checkFillCompanyRoles($identity['u_company_id'])){
        //     $this->flashMessenger()->addErrorMessage('Please, fill all roles for you company');
        //     return $this->redirect()->toRoute('disclosuretrackinglog', array('controller' => 'disclosuretrackinglog', 'action' => 'list'));
        // }

        $form = new DisclosureTrackingLogForm($this->getServiceLocator());

        $dtlObj = null;
        
        $companyRolesMsg = null;

        if ((int) $id) {
            $dtlObj = $this->getDisclosureTrackingLogTable()->getDisclosureTrackingLog($id);
        }
        
        $request = $this->getRequest();

        if ($request->isPost()) {
            $dr = new DisclosureTrackingLog();
            $post = $request->getPost();

            $ymds['dtl_date_received']  = \DateTime::createFromFormat('m/d/Y', $post['dtl_date_received']);
            $ymds['dtl_date_disclosed'] = \DateTime::createFromFormat('m/d/Y', $post['dtl_date_disclosed']);
            
            foreach($ymds as $ymdKey => $ymd) {
                if (is_object($ymd)) {
                    $post[$ymdKey] = $ymd->format('Y-m-d');
                } else {
                    $post[$ymdKey] = '';
                }
            }
            
            $form->setInputFilter($dr->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if ($form->isValid()) {
                $dr->exchangeArray($post);
                $this->getDisclosureTrackingLogTable()->setServiceLocator($this->getServiceLocator());

                $drId = $this->getDisclosureTrackingLogTable()->saveDisclosureTrackingLog($dr);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update disclosure tracking log "' . $drId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new disclosure tracking log "' . $drId . '"');
                }
                
                return $this->redirect()->toRoute('disclosuretrackinglog', array('controller' => 'disclosuretrackinglog', 'action' => 'list'));
            } else {

                if ((int) $id) {
                    $form->bind($dtlObj);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit disclosure tracking log "' . $id . '" page');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new disclosure tracking log page');
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_DTL, $id);

            if ((int) $id) {
                $dtlObj->dtl_date_received  = ($dtlObj->dtl_date_received != '0000-00-00')  ? $dtlObj->dtl_date_received  : '';
                $dtlObj->dtl_date_disclosed = ($dtlObj->dtl_date_disclosed != '0000-00-00') ? $dtlObj->dtl_date_disclosed : '';
                $form->bind($dtlObj);
            }
        }

        return array(
            'form' => $form,
            'dtlId' => $id,
            'dtlObj' => $dtlObj,
            'companyRolesMsg' => $companyRolesMsg
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getDisclosureTrackingLogTable()->deleteDisclosureTrackingLog($id);
        $this->flashMessenger()->addSuccessMessage('Disclosure tracking log has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_DTL, $id);
        
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete disclosure tracking log "' . $id . '"');
        
        return $this->redirect()->toRoute('disclosuretrackinglog', array('controller' => 'disclosuretrackinglog', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getDisclosureTrackingLogTable()->unarchiveDisclosureTrackingLog($id);
        $this->flashMessenger()->addSuccessMessage('Disclosure tracking log has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive disclosure tracking log "' . $id . '"');

        return $this->redirect()->toRoute('disclosuretrackinglog', array('controller' => 'disclosuretrackinglog', 'action' => 'list'));

    }

}
