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

use Disclosure\Model\AccountingRequest;
use Disclosure\Form\AccountingRequestForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class AccountingRequestController extends AbstractActionController
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
        if (!$this->getUserTable()->checkModulesAccess('disclosures')) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
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

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return $identity;
    }

    public function getAccountingRequestTable()
    {
        if (!isset($this->accountingRequestTable)) {
            $sm = $this->getServiceLocator();
            $this->accountingRequestTable = $sm->get('Disclosure\Model\AccountingRequestTable');
        }
        return $this->accountingRequestTable;
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
            'id'     => 'ar_id',
            'patient_name'         => 'ar_patient_name',
            'date_received'       => 'ar_date_requested',
            'request_finalized' => 'ar_is_finalized',
            'date_accounting_sent'         => 'ar_date_sent',
            'location'   => '_ar_location',
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'ar_id';
        $paginator = $this->getAccountingRequestTable()->getAccountingRequests(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(0);

        $view = new ViewModel(array(
            'order_by'    => $orderBy,
            'order'       => $order,
            'page'        => $page,
            'paginator'   => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter'  => $roleFilter,
            'search'      => $search
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open accounting request list page');

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

        $formNote = new NoteForm($this->getServiceLocator());

        $identity = $this->getIdentity();        

        $arObj = null;
        $comments          = null;
        
        if ((int) $id) {
            $arObj = $this->getAccountingRequestTable()->getAccountingRequest($id);
            $comments          = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_AR);
        }
        
        $request = $this->getRequest();

        $form = new AccountingRequestForm($this->getServiceLocator(), $arObj);

        if ($request->isPost()) {
            $ar = new AccountingRequest();
            $post = $request->getPost();

            $ymds['ar_date_requested']       = \DateTime::createFromFormat('m/d/Y', $post['ar_date_requested']);
            $ymds['ar_date_of_birth'] = \DateTime::createFromFormat('m/d/Y', $post['ar_date_of_birth']);
            $ymds['ar_date_requested_from']        = \DateTime::createFromFormat('m/d/Y', $post['ar_date_requested_from']);
            $ymds['ar_date_requested_to']       = \DateTime::createFromFormat('m/d/Y', $post['ar_date_requested_to']);
            $ymds['ar_date_sent'] = \DateTime::createFromFormat('m/d/Y', $post['ar_date_sent']);
            $ymds['ar_date_patient_notified']        = \DateTime::createFromFormat('m/d/Y', $post['ar_date_patient_notified']);
            
            foreach($ymds as $ymdKey => $ymd) {
                if (is_object($ymd)) {
                    $post[$ymdKey] = $ymd->format('Y-m-d');
                } else {
                    $post[$ymdKey] = '';
                }
            }

            $ar->ar_c_id = $post['ar_c_id'];
            $form = new AccountingRequestForm($this->getServiceLocator(), $ar);
            
            $form->setInputFilter($ar->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if ($form->isValid()) {
                $ar->exchangeArray($post);
                $this->getAccountingRequestTable()->setServiceLocator($this->getServiceLocator());

                $arId = $this->getAccountingRequestTable()->saveAccountingRequest($ar);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update accounting request "' . $arId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new accounting request "' . $arId . '"');
                }

                $note = new Note();
                $noteData['note_text'] = $post['note_text'];
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_AR;
                $noteData['note_item_id'] = $arId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());
                
                return $this->redirect()->toRoute('accountingrequest', array('controller' => 'accountingrequest', 'action' => 'list'));
            } else {

                if ((int) $id) {
                    $form->bind($arObj);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit accounting request "' . $id . '" page');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new accounting request page');
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);

            if ((int) $id) {
                $arObj->ar_date_requested       = ($arObj->ar_date_requested != '0000-00-00')       ? \DateTime::createFromFormat('Y-m-d', $arObj->ar_date_requested)->format('m/d/Y')       : '';
                $arObj->ar_date_of_birth = ($arObj->ar_date_of_birth != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $arObj->ar_date_of_birth)->format('m/d/Y') : '';
                $arObj->ar_date_requested_from        = ($arObj->ar_date_requested_from != '0000-00-00')        ? \DateTime::createFromFormat('Y-m-d', $arObj->ar_date_requested_from)->format('m/d/Y')        : '';
                $arObj->ar_date_requested_to       = ($arObj->ar_date_requested_to != '0000-00-00')       ? \DateTime::createFromFormat('Y-m-d', $arObj->ar_date_requested_to)->format('m/d/Y')       : '';
                $arObj->ar_date_sent = ($arObj->ar_date_sent != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $arObj->ar_date_sent)->format('m/d/Y') : '';
                $arObj->ar_date_patient_notified        = ($arObj->ar_date_patient_notified != '0000-00-00')        ? \DateTime::createFromFormat('Y-m-d', $arObj->ar_date_patient_notified)->format('m/d/Y')        : '';
                $form->bind($arObj);
            }
        }

        return array(
            'form' => $form,
            'arId' => $id,
            'arObj' => $arObj,
            'comments' => $comments,
            'formNote' => $formNote,
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getAccountingRequestTable()->deleteAccountingRequest($id);
        $this->flashMessenger()->addSuccessMessage('Accounting request has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);
        
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete accounting request "' . $id . '"');
        
        return $this->redirect()->toRoute('accountingrequest', array('controller' => 'accountingrequest', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getAccountingRequestTable()->unarchiveAccountingRequest($id);
        $this->flashMessenger()->addSuccessMessage('Accounting request has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive accounting request "' . $id . '"');

        return $this->redirect()->toRoute('accountingrequest', array('controller' => 'accountingrequest', 'action' => 'list'));

    }

    public function getlocationsAction()
    {
        $cId = $this->params('id');

        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($cId, \Client\Model\AddressItem::COMPANY_TYPE);

        $res = array('0' => 'Select location');
        foreach ($addresses as $value) {
            $res[$value->adr_id] = $value->adr_name;
        }

        return new JsonModel($res);
    }
}
