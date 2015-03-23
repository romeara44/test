<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Traininglog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Traininglog\Form\TraininglogForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Traininglog\Model\Regulation;
use Traininglog\Model\Traininglogtype;
use Traininglog\Model\Traininglog;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class TraininglogController extends AbstractActionController
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

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }

    public function getTraininglogTable()
    {
        if (!$this->traininglogTable) {
            $sm = $this->getServiceLocator();
            $this->traininglogTable = $sm->get('Traininglog\Model\TraininglogTable');
        }
        return $this->traininglogTable;
    }

    public function getTraininglogtypeTable()
    {
        if (!$this->traininglogtypeTable) {
            $sm = $this->getServiceLocator();
            $this->traininglogtypeTable = $sm->get('Traininglog\Model\TraininglogtypeTable');
        }
        return $this->traininglogtypeTable;
    }

    public function getRegulationTable()
    {
        if (!$this->regulationTable) {
            $sm = $this->getServiceLocator();
            $this->regulationTable = $sm->get('Traininglog\Model\RegulationTable');
            $this->regulationTable = $sm->get('Traininglog\Model\RegulationTable');
        }
        return $this->regulationTable;
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
            'title'          => 'tl_title',
            'type'           => 'tl_tlt_id',
            'conducted_date' => 'tl_conducted_date',
            'hire_date'      => 'tl_hire_date',
            'trainer'        => '_tl_trainer_name'
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol   = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'tl_id';
        $paginator = $this->getTraininglogTable()->getTraininglogs(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
      
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

        $form     = new TraininglogForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());

        $notes = null;
        $tlObj = null;
        
        if ((int) $id) {
            $tlObj             = $this->getTraininglogTable()->getTraininglog($id);
            $trainingMaterials = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_TLT);
            $comments          = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_TLC);
        }
        
        $identity = $this->getIdentity();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $tl = new Traininglog();
            $post = $request->getPost();

            if($post['_tl_trainer'] != '-1') {
                list($post['tl_trainer_type'], $post['tl_trainer_id']) = explode('_', $post['_tl_trainer']);
            }

            $ymds['tl_conducted_date'] = \DateTime::createFromFormat('m/d/Y', $post['tl_conducted_date']);
            $ymds['tl_hire_date']      = \DateTime::createFromFormat('m/d/Y', $post['tl_hire_date']);
            
            foreach($ymds as $ymdKey => $ymd) {
                if (is_object($ymd)) {
                    $post[$ymdKey] = $ymd->format('Y-m-d');
                } else {
                    $post[$ymdKey] = '';
                }
            }
            
            $form->setInputFilter($tl->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if ($form->isValid()) {
                $tl->exchangeArray($post);
                $this->getTraininglogTable()->setServiceLocator($this->getServiceLocator());
                $attendeesFile = $request->getFiles('attendees');
                if(isset($attendeesFile[0]['tmp_name']) && $attendeesFile[0]['tmp_name']) {
                    if (($handle = fopen($attendeesFile[0]['tmp_name'], "r")) !== FALSE) {
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                           $tl->tl_attendees .= implode(', ', $data) . "\r\n";
                        }
                        fclose($handle);
                    }
                }

                $tlId = $this->getTraininglogTable()->saveTraininglog($tl);

                // save files
                $note = new Note();
                $noteData['note_text'] = '';
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_TLT;
                $noteData['note_item_id'] = $tlId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'training');

                // save text note
                if($post['note_text'])
                {
                    $note = new Note();
                    $noteData['note_text'] = $post['note_text'];
                    $noteData['note_item_type'] = \Note\Model\Note::NOTE_TLC;
                    $noteData['note_item_id'] = $tlId;
                    $note->exchangeArray($noteData);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note);
                }
                
                // save files
                $note = new Note();
                $noteData['note_text'] = '';
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_TLC;
                $noteData['note_item_id'] = $tlId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'comments');
                
                return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'list'));
            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                   // echo "Validation failure '$messageId': $message<br/>";
                }

                if ((int) $id) {
                    $form->bind($tlObj);
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);

            if ((int) $id) {
                $tlObj->tl_conducted_date = ($tlObj->tl_conducted_date != '0000-00-00') ? $tlObj->tl_conducted_date : '';
                $tlObj->tl_hire_date      = ($tlObj->tl_hire_date != '0000-00-00') ? $tlObj->tl_hire_date : '';
                $form->bind($tlObj);
            }
        }

        return array(
            'form' => $form,
            'trainingMaterials' => $trainingMaterials,
            'comments' => $comments,
            'formNote' => $formNote,
            'tlId' => $id,
            'tlObj' => $tlObj,
            'regulations' => $this->getRegulationTable()->getRegulations()
        );
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getTraininglogTable()->deleteTraininglog($id);
        $this->flashMessenger()->addSuccessMessage('Training log has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_TL, $id);

        return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'list'));
    }

    public function setstatusAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
        }

        $this->getTraininglogTable()->setTraininglogStatus($post['id'], $post['archived']);

        return new JsonModel(array('result' => 'true'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getTraininglogTable()->unarchiveTraininglog($id);
        $this->flashMessenger()->addSuccessMessage('Training log has been unarchived');

        return $this->redirect()->toRoute('traininglog', array('controller' => 'traininglog', 'action' => 'list'));

    }

}
