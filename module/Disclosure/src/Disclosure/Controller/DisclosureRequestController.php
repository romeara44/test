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

use Disclosure\Model\DisclosureRequest;
use Disclosure\Form\DisclosureRequestForm;
use Admin\Model\User;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class DisclosureRequestController extends AbstractActionController
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

    public function getDisclosureRequestTable()
    {
        if (!isset($this->disclosureRequestTable)) {
            $sm = $this->getServiceLocator();
            $this->disclosureRequestTable = $sm->get('Disclosure\Model\DisclosureRequestTable');
        }
        return $this->disclosureRequestTable;
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
            'reference_number'     => 'dr_reference_number',
            'requested_by'         => 'dr_requested_by',
            'date_requested'       => 'dr_date_requested',
            'date_range_requested' => 'dr_date_range_requested',
            'staff_member'         => 'dr_staff_member',
            'completing_request'   => 'dr_completing_request',
            'date_provided'        => 'dr_date_provided',
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'dr_id';
        $paginator = $this->getDisclosureRequestTable()->getDisclosureRequests(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open disclosure request list page');

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
        //     return $this->redirect()->toRoute('disclosurerequest', array('controller' => 'disclosurerequest', 'action' => 'list'));
        // }

        $form = new DisclosureRequestForm($this->getServiceLocator());

        $drObj = null;
        
        $companyRolesMsg = null;

        if ((int) $id) {
            $drObj = $this->getDisclosureRequestTable()->getDisclosureRequest($id);
        }
        
        $request = $this->getRequest();

        if ($request->isPost()) {
            $dr = new DisclosureRequest();
            $post = $request->getPost();

            $ymds['dr_date_requested']       = \DateTime::createFromFormat('m/d/Y', $post['dr_date_requested']);
            $ymds['dr_date_range_requested'] = \DateTime::createFromFormat('m/d/Y', $post['dr_date_range_requested']);
            $ymds['dr_date_provided']        = \DateTime::createFromFormat('m/d/Y', $post['dr_date_provided']);
            
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
                $this->getDisclosureRequestTable()->setServiceLocator($this->getServiceLocator());

                $drId = $this->getDisclosureRequestTable()->saveDisclosureRequest($dr);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update disclosure request "' . $drId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new disclosure request "' . $drId . '"');
                }
                
                return $this->redirect()->toRoute('disclosurerequest', array('controller' => 'disclosurerequest', 'action' => 'list'));
            } else {

                if ((int) $id) {
                    $form->bind($drObj);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit disclosure request "' . $id . '" page');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new disclosure request page');
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);

            if ((int) $id) {
                $drObj->dr_date_requested       = ($drObj->dr_date_requested != '0000-00-00')       ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_requested)->format('m/d/Y')       : '';
                $drObj->dr_date_range_requested = ($drObj->dr_date_range_requested != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_range_requested)->format('m/d/Y') : '';
                $drObj->dr_date_provided        = ($drObj->dr_date_provided != '0000-00-00')        ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_provided)->format('m/d/Y')        : '';
                $form->bind($drObj);
            }
        }

        return array(
            'form' => $form,
            'drId' => $id,
            'drObj' => $drObj,
            'companyRolesMsg' => $companyRolesMsg
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getDisclosureRequestTable()->deleteDisclosureRequest($id);
        $this->flashMessenger()->addSuccessMessage('Disclosure request has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
        
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete disclosure request "' . $id . '"');
        
        return $this->redirect()->toRoute('disclosurerequest', array('controller' => 'disclosurerequest', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getDisclosureRequestTable()->unarchiveDisclosureRequest($id);
        $this->flashMessenger()->addSuccessMessage('Disclosure request has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive disclosure request "' . $id . '"');

        return $this->redirect()->toRoute('disclosurerequest', array('controller' => 'disclosurerequest', 'action' => 'list'));

    }

}
