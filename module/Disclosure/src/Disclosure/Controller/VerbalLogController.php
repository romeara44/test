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

use Disclosure\Model\VerbalLog;
use Disclosure\Form\VerbalLogForm;
use Admin\Model\User;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class VerbalLogController extends AbstractActionController
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

    public function getVerbalLogTable()
    {
        if (!isset($this->VerbalLogTable)) {
            $sm = $this->getServiceLocator();
            $this->VerbalLogTable = $sm->get('Disclosure\Model\VerbalLogTable');
        }
        return $this->VerbalLogTable;
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
            'medical_record_number' => 'vl_medical_record_number',
            'name'                  => 'vl_name',
            'date_of_birth'         => 'vl_date_of_birth',
            'address'               => 'vl_address',
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'vl_id';
        $paginator = $this->getVerbalLogTable()->getVerbalLogs(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open verbal log list page');

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

        $form = new VerbalLogForm($this->getServiceLocator());

        $vlObj = null;
        
        $companyRolesMsg = null;

        if ((int) $id) {
            $vlObj = $this->getVerbalLogTable()->getVerbalLog($id);
        }
        
        $request = $this->getRequest();

        if ($request->isPost()) {
            $dr = new VerbalLog();
            $post = $request->getPost();

            $ymds['vl_date_of_request']  = \DateTime::createFromFormat('m/d/Y', $post['vl_date_of_request']);
            $ymds['vl_date_of_birth'] = \DateTime::createFromFormat('m/d/Y', $post['vl_date_of_birth']);
            $ymds['vl_date_requested_from'] = \DateTime::createFromFormat('m/d/Y', $post['vl_date_requested_from']);
            $ymds['vl_date_requested_to'] = \DateTime::createFromFormat('m/d/Y', $post['vl_date_requested_to']);
            $ymds['vl_date_request_received'] = \DateTime::createFromFormat('m/d/Y', $post['vl_date_request_received']);
            $ymds['vl_date_account_sent'] = \DateTime::createFromFormat('m/d/Y', $post['vl_date_account_sent']);
            $ymds['vl_date_patient_notified'] = \DateTime::createFromFormat('m/d/Y', $post['vl_date_patient_notified']);
            
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
                $this->getVerbalLogTable()->setServiceLocator($this->getServiceLocator());

                $drId = $this->getVerbalLogTable()->saveVerbalLog($dr);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update verbal log "' . $drId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new verbal log "' . $drId . '"');
                }
                
                return $this->redirect()->toRoute('verballog', array('controller' => 'verballog', 'action' => 'list'));
            } else {

                if ((int) $id) {
                    $form->bind($vlObj);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit verbal log "' . $id . '" page');
                } else {
                    $ymds['vl_date_of_request']  = \DateTime::createFromFormat('Y-m-d', $post['vl_date_of_request']);
                    $ymds['vl_date_of_birth'] = \DateTime::createFromFormat('Y-m-d', $post['vl_date_of_birth']);
                    $ymds['vl_date_requested_from'] = \DateTime::createFromFormat('Y-m-d', $post['vl_date_requested_from']);
                    $ymds['vl_date_requested_to'] = \DateTime::createFromFormat('Y-m-d', $post['vl_date_requested_to']);
                    $ymds['vl_date_request_received'] = \DateTime::createFromFormat('Y-m-d', $post['vl_date_request_received']);
                    $ymds['vl_date_account_sent'] = \DateTime::createFromFormat('Y-m-d', $post['vl_date_account_sent']);
                    $ymds['vl_date_patient_notified'] = \DateTime::createFromFormat('Y-m-d', $post['vl_date_patient_notified']);
                    
                    foreach($ymds as $ymdKey => $ymd) {
                        if (is_object($ymd)) {
                            $post[$ymdKey] = $ymd->format('m/d/Y');
                        } else {
                            $post[$ymdKey] = '';
                        }
                    }
                    $form->setData($post);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new verbal log page');
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);

            if ((int) $id) {
                $vlObj->vl_date_of_request       = ($vlObj->vl_date_of_request != '0000-00-00')       ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_of_request)->format('m/d/Y')       : '';
                $vlObj->vl_date_of_birth         = ($vlObj->vl_date_of_birth != '0000-00-00')         ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_of_birth)->format('m/d/Y')         : '';
                $vlObj->vl_date_requested_from   = ($vlObj->vl_date_requested_from != '0000-00-00')   ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_requested_from)->format('m/d/Y')   : '';
                $vlObj->vl_date_requested_to     = ($vlObj->vl_date_requested_to != '0000-00-00')     ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_requested_to)->format('m/d/Y')     : '';
                $vlObj->vl_date_request_received = ($vlObj->vl_date_request_received != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_request_received)->format('m/d/Y') : '';
                $vlObj->vl_date_account_sent     = ($vlObj->vl_date_account_sent != '0000-00-00')     ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_account_sent)->format('m/d/Y')     : '';
                $vlObj->vl_date_patient_notified = ($vlObj->vl_date_patient_notified != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $vlObj->vl_date_patient_notified)->format('m/d/Y') : '';
                $form->bind($vlObj);
            }
        }

        return array(
            'form' => $form,
            'vlId' => $id,
            'vlObj' => $vlObj,
            'companyRolesMsg' => $companyRolesMsg
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getVerbalLogTable()->deleteVerbalLog($id);
        $this->flashMessenger()->addSuccessMessage('Verbal log has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);
        
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete verbal log "' . $id . '"');
        
        return $this->redirect()->toRoute('verballog', array('controller' => 'verballog', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getVerbalLogTable()->unarchiveVerbalLog($id);
        $this->flashMessenger()->addSuccessMessage('Verbal log has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive verbal log "' . $id . '"');

        return $this->redirect()->toRoute('verballog', array('controller' => 'verballog', 'action' => 'list'));

    }

}
