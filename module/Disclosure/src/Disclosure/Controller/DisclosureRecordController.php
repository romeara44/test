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

use Disclosure\Model\DisclosureRecord;
use Disclosure\Form\DisclosureRecordForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class DisclosureRecordController extends AbstractActionController
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

    public function getDisclosureRecordTable()
    {
        if (!isset($this->disclosureRecordTable)) {
            $sm = $this->getServiceLocator();
            $this->disclosureRecordTable = $sm->get('Disclosure\Model\DisclosureRecordTable');
        }
        return $this->disclosureRecordTable;
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
            'id'     => 'dr_id',
            'patient_name'         => 'dr_patient_name',
            'date_requested'       => 'dr_date_received',
            'disclosed_by' => 'dr_disclosed_by',
            'date_disclosed'         => 'dr_date_disclosed',
            'location'   => 'dr_location',
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'dr_id';
        $paginator = $this->getDisclosureRecordTable()->getDisclosureRecords(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open disclosure record list page');

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

        $drObj = null;
        $comments          = null;

        if ((int) $id) {
            $drObj = $this->getDisclosureRecordTable()->getDisclosureRecord($id);
            $comments          = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_DR);
        }
        
        $request = $this->getRequest();

        $form = new DisclosureRecordForm($this->getServiceLocator(), $drObj);

        if ($request->isPost()) {
            $dr = new DisclosureRecord();
            $post = $request->getPost();

            $ymds['dr_date_received']       = \DateTime::createFromFormat('m/d/Y', $post['dr_date_received']);
            $ymds['dr_date_disclosure'] = \DateTime::createFromFormat('m/d/Y', $post['dr_date_disclosure']);
            $ymds['dr_date_of_birth']        = \DateTime::createFromFormat('m/d/Y', $post['dr_date_of_birth']);
            $ymds['dr_date_entered']       = \DateTime::createFromFormat('m/d/Y', $post['dr_date_entered']);
            $ymds['dr_date_reviewed'] = \DateTime::createFromFormat('m/d/Y', $post['dr_date_reviewed']);
            $ymds['dr_date_disclosed']        = \DateTime::createFromFormat('m/d/Y', $post['dr_date_disclosed']);
            
            foreach($ymds as $ymdKey => $ymd) {
                if (is_object($ymd)) {
                    $post[$ymdKey] = $ymd->format('Y-m-d');
                } else {
                    $post[$ymdKey] = '';
                }
            }

            $dr->dr_c_id = $post['dr_c_id'];
            $form = new DisclosureRecordForm($this->getServiceLocator(), $dr);
            
            $form->setInputFilter($dr->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if ($form->isValid()) {
                $dr->exchangeArray($post);
                $this->getDisclosureRecordTable()->setServiceLocator($this->getServiceLocator());

                $drId = $this->getDisclosureRecordTable()->saveDisclosureRecord($dr);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update disclosure record "' . $drId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new disclosure record "' . $drId . '"');
                }

                $note = new Note();
                $noteData['note_text'] = $post['note_text'];
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_DR;
                $noteData['note_item_id'] = $drId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());
                
                return $this->redirect()->toRoute('disclosurerecord', array('controller' => 'disclosurerecord', 'action' => 'list'));
            } else {

                if ((int) $id) {
                    $form->bind($drObj);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit disclosure record "' . $id . '" page');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new disclosure record page');
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);

            if ((int) $id) {
                $drObj->dr_date_received       = ($drObj->dr_date_received != '0000-00-00')       ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_received)->format('m/d/Y')       : '';
                $drObj->dr_date_disclosure = ($drObj->dr_date_disclosure != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_disclosure)->format('m/d/Y') : '';
                $drObj->dr_date_of_birth        = ($drObj->dr_date_of_birth != '0000-00-00')        ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_of_birth)->format('m/d/Y')        : '';
                $drObj->dr_date_entered       = ($drObj->dr_date_entered != '0000-00-00')       ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_entered)->format('m/d/Y')       : '';
                $drObj->dr_date_reviewed = ($drObj->dr_date_reviewed != '0000-00-00') ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_reviewed)->format('m/d/Y') : '';
                $drObj->dr_date_disclosed        = ($drObj->dr_date_disclosed != '0000-00-00')        ? \DateTime::createFromFormat('Y-m-d', $drObj->dr_date_disclosed)->format('m/d/Y')        : '';
                $form->bind($drObj);
            }
        }

        return array(
            'form' => $form,
            'drId' => $id,
            'drObj' => $drObj,
            'comments' => $comments,
            'formNote' => $formNote,
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getDisclosureRecordTable()->deleteDisclosureRecord($id);
        $this->flashMessenger()->addSuccessMessage('Disclosure record has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
        
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete disclosure record "' . $id . '"');
        
        return $this->redirect()->toRoute('disclosurerecord', array('controller' => 'disclosurerecord', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getDisclosureRecordTable()->unarchiveDisclosureRecord($id);
        $this->flashMessenger()->addSuccessMessage('Disclosure record has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive disclosure record "' . $id . '"');

        return $this->redirect()->toRoute('disclosurerecord', array('controller' => 'disclosurerecord', 'action' => 'list'));

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
