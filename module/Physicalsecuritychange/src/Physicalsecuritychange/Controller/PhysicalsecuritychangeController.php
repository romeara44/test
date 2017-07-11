<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Physicalsecuritychange\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Physicalsecuritychange\Form\PhysicalsecuritychangeForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Physicalsecuritychange\Model\Physicalsecuritychange;
use Zend\Session\Container;

class PhysicalsecuritychangeController extends AbstractActionController
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
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5))) {
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

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getPhysicalsecuritychangeTable()
    {
        if (!isset($this->physicalSecurityChangeTable)) {
            $sm = $this->getServiceLocator();
            $this->physicalSecurityChangeTable = $sm->get('Physicalsecuritychange\Model\PhysicalsecuritychangeTable');
        }
        return $this->physicalSecurityChangeTable;
    }

    public function getPhysicalsecuritychangeitemTable()
    {
        if (!isset($this->physicalSecurityChangeitemTable)) {
            $sm = $this->getServiceLocator();
            $this->physicalSecurityChangeitemTable = $sm->get('Physicalsecuritychange\Model\PhysicalsecuritychangeitemTable');
        }
        return $this->physicalSecurityChangeitemTable;
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
            'id'          => 'psc_id',
            'client'           => '_company_name',
            'location' => '_location',
            'type'      => 'psc_change_type',
            'date'        => 'psc_create_date'
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'psc_id';
        $paginator = $this->getPhysicalsecuritychangeTable()->getPhysicalsecuritychanges(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      //var_dump($paginator);
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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open Physicalsecuritychange list page');

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

        $pscObj = null;

        $comments          = null;

        $items = [];
        
        if ((int) $id) {
            $pscObj             = $this->getPhysicalsecuritychangeTable()->getPhysicalsecuritychange($id);
            $comments          = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_PSC);
            $items = $this->getPhysicalsecuritychangeitemTable()->getItems($id);
        }
        
        $form = new PhysicalsecuritychangeForm($this->getServiceLocator(), $pscObj);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $psc = new Physicalsecuritychange();
            $post = $request->getPost();
            
            $form->setInputFilter($psc->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if ($form->isValid()) {
                $psc->exchangeArray($post);
                $pscId = $this->getPhysicalsecuritychangeTable()->savePhysicalsecuritychange($psc);

                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update Physicalsecuritychange "' . $pscId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new Physicalsecuritychange "' . $pscId . '"');
                }
                
                $note = new Note();
                $noteData['note_text'] = $post['note_text'];
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_PSC;
                $noteData['note_item_id'] = $pscId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());
                
                return $this->redirect()->toRoute('physicalsecuritychange', array('controller' => 'physicalsecuritychange', 'action' => 'list'));
            } else {

                if ((int) $id) {
                    $form->bind($pscObj);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit physicalsecuritychange "' . $id . '" page');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new physicalsecuritychange page');
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_PSC, $id);

            if ((int) $id) {
                $form->bind($pscObj);
            }
        }

        return array(
            'form' => $form,
            'comments' => $comments,
            'formNote' => $formNote,
            'pscId' => $id,
            'items' => $items,
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getPhysicalsecuritychangeTable()->deletePhysicalsecuritychange($id);
        $this->flashMessenger()->addSuccessMessage('Physicalsecuritychange has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_PSC, $id);
        
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete Physicalsecuritychange "' . $id . '"');
        
        return $this->redirect()->toRoute('physicalsecuritychange', array('controller' => 'physicalsecuritychange', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getPhysicalsecuritychangeTable()->unarchivePhysicalsecuritychange($id);
        $this->flashMessenger()->addSuccessMessage('Physicalsecuritychange has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive Physicalsecuritychange "' . $id . '"');

        return $this->redirect()->toRoute('physicalsecuritychange', array('controller' => 'physicalsecuritychange', 'action' => 'list'));

    }
}
