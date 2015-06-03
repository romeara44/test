<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Securityreminder\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Securityreminder\Form\SecurityreminderForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Securityreminder\Model\Distributiontype;
use Securityreminder\Model\Securityreminder;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class SecurityreminderController extends AbstractActionController
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
        if (!isset($this->userTable)) {
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

    public function getNoteTable()
    {
        if (!isset($this->noteTable)) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getSecurityreminderTable()
    {
        if (!isset($this->securityreminderTable)) {
            $sm = $this->getServiceLocator();
            $this->securityreminderTable = $sm->get('Securityreminder\Model\SecurityreminderTable');
        }
        return $this->securityreminderTable;
    }

    public function getDistributiontypeTable()
    {
        if (!isset($this->distributiontypeTable)) {
            $sm = $this->getServiceLocator();
            $this->distributiontypeTable = $sm->get('Securityreminder\Model\DistributiontypeTable');
        }
        return $this->distributiontypeTable;
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
            'title'         => 'sr_title',
            'launched_date' => 'sr_launched_date',
            'developed_by'  => 'sr_developed_by_u_id'
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'sr_id';
        $paginator = $this->getSecurityreminderTable()->getSecurityreminders(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open security reminder list page');

        return $view;
    }

    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
        $noteform = $request->isPost() && (int) $request->getPost('noteform');

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        
        $identity = $this->getIdentity();
        
        if(!$this->getCompanyRolesTable()->checkFillCompanyRoles($identity['u_company_id'])){
            $this->flashMessenger()->addErrorMessage('Please, fill all roles for you company');
            return $this->redirect()->toRoute('securityreminder', array('controller' => 'securityreminder', 'action' => 'list'));
        }
        
        $form     = new SecurityreminderForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());

        $notes = null;
        $srObj = null;

        if ((int) $id) {
            $srObj           = $this->getSecurityreminderTable()->getSecurityreminder($id);
            $copyOfMaterials = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_SRM);
            $comments        = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_SRC);
        } else {
            $copyOfMaterials = array();
            $comments        = array();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $sr = new Securityreminder();
            $post = $request->getPost();
            
            $ymds['sr_launched_date'] = \DateTime::createFromFormat('m/d/Y', $post['sr_launched_date']);
            
            foreach($ymds as $ymdKey => $ymd) {
                if (is_object($ymd)) {
                    $post[$ymdKey] = $ymd->format('Y-m-d');
                } else {
                    $post[$ymdKey] = '';
                }
            }
            
            $form->setInputFilter($sr->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if ($form->isValid()) {
                $sr->exchangeArray($post);
                $this->getSecurityreminderTable()->setServiceLocator($this->getServiceLocator());

                $srId = $this->getSecurityreminderTable()->saveSecurityreminder($sr);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update security reminder "' . $srId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new security reminder "' . $srId . '"');
                }

                // save files
                $note = new Note();
                $noteData['note_text'] = '';
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_SRM;
                $noteData['note_item_id'] = $srId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'materials');

                // save text note
                if($post['note_text'])
                {
                    $note = new Note();
                    $noteData['note_text'] = $post['note_text'];
                    $noteData['note_item_type'] = \Note\Model\Note::NOTE_SRC;
                    $noteData['note_item_id'] = $srId;
                    $note->exchangeArray($noteData);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note);
                }
                
                // save files
                $note = new Note();
                $noteData['note_text'] = '';
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_SRC;
                $noteData['note_item_id'] = $srId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'comments');
                
                return $this->redirect()->toRoute('securityreminder', array('controller' => 'Securityreminder', 'action' => 'list'));
            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                   // echo "Validation failure '$messageId': $message<br/>";
                }

                if ((int) $id) {
                    $form->bind($srObj);
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);

            if ((int) $id) {
                $srObj->sr_launched_date = ($srObj->sr_launched_date != '0000-00-00') ? $srObj->sr_launched_date : '';
                $form->bind($srObj);
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit security reminder "' . $id . '" page');
            } else {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new security reminder page');
            }
        }

        return array(
            'form' => $form,
            'copyOfMaterials' => $copyOfMaterials,
            'comments' => $comments,
            'formNote' => $formNote,
            'srId' => $id,
            'srObj' => $srObj,
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getSecurityreminderTable()->deleteSecurityreminder($id);
        $this->flashMessenger()->addSuccessMessage('Security reminder has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_SR, $id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete security reminder "' . $id . '" page');

        return $this->redirect()->toRoute('securityreminder', array('controller' => 'securityreminder', 'action' => 'list'));
    }

    public function setstatusAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
        }

        $id = $this->params('id');
        $archived = $this->params('archived');

        $this->getSecurityreminderTable()->setSecurityreminderStatus($id, $archived);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Set status "' . $archived . '" for security reminder "' . $id . '" page');

        return new JsonModel(array('result' => 'true'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getSecurityreminderTable()->unarchiveSecurityreminder($id);
        $this->flashMessenger()->addSuccessMessage('Security reminder has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive for security reminder "' . $id . '" page');

        return $this->redirect()->toRoute('securityreminder', array('controller' => 'securityreminder', 'action' => 'list'));

    }
}
