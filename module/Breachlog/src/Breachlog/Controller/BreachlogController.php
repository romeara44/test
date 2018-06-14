<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Breachlog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Breachlog\Form\BreachlogForm;
use Admin\Model\User;
use Breachlog\Model\Breachlog;
use Breachlog\Model\Breachloganswer;
use Mail\Model\Mailtemplate;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Note\Form\NoteForm;
use Note\Model\Note;

class BreachlogController extends AbstractActionController
{
    protected $breachlogTable;
    protected $breachloganswerTable;
    protected $breachlogquestionTable;
    protected $userTable;
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        // (Chris) handle loading of proper html/css/js?
        $this->layout()->searchRoleFilter = 'breachlog';
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();

        // (Chris) Not sure, does this mean user has to be authenticated?
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();

        // (Chris) Redirect to index if User doesn't have breach access (`u_has_breach`) in User table
        if (!$this->getUserTable()->checkModulesAccess('breach')) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        // (Chris) If you aren't of the correct company role (admin, or high level consultant or whatever), you get redirected
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));

        // (Chris) Redirect to accept terms if it's your first login
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getBreachlogTable()
    {
        if (!isset($this->breachlogTable)) {
            $sm = $this->getServiceLocator();
            $this->breachlogTable = $sm->get('Breachlog\Model\BreachlogTable');
        }
        return $this->breachlogTable;
    }

    public function getBreachlogquestionTable()
    {
        if (!isset($this->breachlogquestionTable)) {
            $sm = $this->getServiceLocator();
            $this->breachlogquestionTable = $sm->get('Breachlog\Model\BreachlogquestionTable');
        }
        return $this->breachlogquestionTable;
    }

    public function getBreachloganswerTable()
    {
        if (!isset($this->breachloganswerTable)) {
            $sm = $this->getServiceLocator();
            $this->breachloganswerTable = $sm->get('Breachlog\Model\BreachloganswerTable');
        }
        return $this->breachloganswerTable;
    }

    public function getUserTable()
    {
        if (!isset($this->userTable)) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getMailtemplateTable()
    {
        if (!isset($this->mailtemplateTable)) {
            $sm = $this->getServiceLocator();
            $this->mailtemplateTable = $sm->get('Mail\Model\MailtemplateTable');
        }
        return $this->mailtemplateTable;
    }

    public function getNoteTable()
    {
        if (!isset($this->noteTable)) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
    }
    
    public function getCompanyRolesTable()
    {
        if (!isset($this->companyRolesTable)) {
            $sm = $this->getServiceLocator();
            $this->companyRolesTable = $sm->get('Client\Model\CompanyRolesTable');
        }
        return $this->companyRolesTable;
    }

    public function getRegulationTable()
    {
        if (!isset($this->regulationTable)) {
            $sm = $this->getServiceLocator();
            $this->regulationTable = $sm->get('Traininglog\Model\RegulationTable');
        }
        return $this->regulationTable;
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

    public function listAction()
    {
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $mappingSortCol = array(
            'id' => 'bl_id',
            'name' => 'bl_name',
            'cName' => 'c_name',
            'date' => 'bl_date_of_occurrence',
            'reportable' => 'bl_reportable'
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'bl_id';
        $paginator = $this->getBreachlogTable()->getBreachlogs(true, $sortCol, $order, $this->getIdentity());

        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage($paginator->getTotalItemCount());

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open breachlog list page');

        return $view;
    }

    // TODO (chris) Is this restricted correctly?
    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
				$etype = (strpos($request, 'type=view') === false) ? '' : 'view';
        $noteform = $request->isPost() && (int) $request->getPost('noteform');

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $identity = $this->getIdentity();

        $form = new BreachlogForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());
        
        $notes = null;
        $blObj = null;
        $userObj = null;
        $companyUsers['options'][''] = 'Please Select';
        $companyRolesMsg = '';
        
        if ((int) $id) {
            $blObj = $this->getBreachlogTable()->getBreachlog($id);
            $companyUsersObj = $this->getUserTable()->getUsersByCompany($blObj->bl_c_id);
            if($companyUsersObj) {
                foreach($companyUsersObj as $companyUserObj) {
                    $companyUsers['options'][$companyUserObj->u_id] = $companyUserObj->u_firstname . ' ' . $companyUserObj->u_lastname; 
                }
            }
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_BL);
        } else if($identity['u_role_id'] == \Admin\Model\User::ROLE_CLIENT) {
            $companyUsersObj = $this->getUserTable()->getUsersByCompany($identity['u_company_id']);
            if($companyUsersObj) {
                foreach($companyUsersObj as $companyUserObj) {
                    $companyUsers['options'][$companyUserObj->u_id] = $companyUserObj->u_firstname . ' ' . $companyUserObj->u_lastname; 
                }
            }
        }

        $questionsErrors = false;
        $answers = array();
        $questionsFormAnswers = array();
        $identity = $this->getIdentity();
        $request = $this->getRequest();

        // At this point we load the blank form....
        if ($request->isPost()) {

            $bl = new Breachlog();
            $post = $request->getPost();

            if(!$blObj || $blObj->bl_reportable == 1) {
                $ymds['bl_date_of_occurrence'] = \DateTime::createFromFormat('m/d/Y', $post['bl_date_of_occurrence']);
            }
            $ymds['bl_date_invest_start'] = \DateTime::createFromFormat('m/d/Y', $post['bl_date_invest_start']);
            $ymds['bl_date_invest_complete'] = \DateTime::createFromFormat('m/d/Y', $post['bl_date_invest_complete']);
            
            foreach($ymds as $ymdKey => $ymd) {
                if (is_object($ymd)) {
                    $post[$ymdKey] = $ymd->format('Y-m-d');
                } else {
                    $post[$ymdKey] = '';
                }
            }
            
            $form->setInputFilter($bl->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($post);

            if (!(int) $id) { // check if all questions answered - only for new breach log
                if (isset($post['questions'])) {
                    foreach ($post['questions'] as $questionId => $question) {
                        $questionsFormAnswers[$questionId] = $question;
                    }
                    if (count($questionsFormAnswers) < 9) {
                        $questionsErrors = true;
                    }
                } else {
                    $questionsErrors = true;
                }
            }

            if(!$checkFillCompanyRoles = $this->getCompanyRolesTable()->checkFillCompanyRoles($post['bl_c_id'])) {
                $companyRolesMsg = 'Please, fill all roles for this company';
            }


            if ($form->isValid() && $checkFillCompanyRoles && !$questionsErrors) {
                $post['bl_consultant_u_id'] = $identity['u_id'];
                if(isset($post['questions'][11]) && $post['questions'][11] == 2) $post['bl_date_of_occurrence'] = '';
                $bl->exchangeArray($post);
                $this->getBreachlogTable()->setServiceLocator($this->getServiceLocator());

                if ($bl->_bl_cur_regulations) {
                    $bl->_bl_cur_regulations = explode(',', $bl->_bl_cur_regulations);
                }

                $blId = $this->getBreachlogTable()->saveBreachlog($bl);

                // save answers
                if (isset($post['questions'])) {
                    foreach ($post['questions'] as $questionId => $question) {
                        $bla = new Breachloganswer();
                        $answer['bla_blq_id'] = $questionId;
                        $answer['bla_value'] = $question;
                        $answer['bla_bl_id'] = $blId;

                        $bla->exchangeArray($answer);
                        $this->getBreachloganswerTable()->setServiceLocator($this->getServiceLocator());
                        $blaId = $this->getBreachloganswerTable()->saveBreachloganswers($bla);
                    }
                    $this->getBreachlogquestionTable()->setServiceLocator($this->getServiceLocator());

                    // This is where we determine if the security incident is a reportable breach
                    $brpId = $this->getBreachlogquestionTable()->setReportable($blId);

                    if ($brpId) {
                        // As per lhecker in BT-6, any reportable breach requires a remediation plan
                        $this->flashMessenger()->addSuccessMessage('This security incident constitutes a reportable breach. Please create a remediation plan.');
                        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'edit', 'id' => $brpId));
                    } else {
                        // As per lhecker in BT-6, a remediation plan should not be created if incident is not a breach
                        $this->flashMessenger()->addSuccessMessage('This security incident does not constitute a reportable breach. No additional reporting is necessary.');
                        $bl->bl_id = $blId;
                        $bl->bl_date_of_occurrence = '';
                        $this->getBreachlogTable()->saveBreachlog($bl);
                    }
                }

                // save text note
                if($post['note_text'])
                {
                    $note = new Note();
                    $noteData['note_text'] = $post['note_text'];
                    $noteData['note_item_type'] = \Note\Model\Note::NOTE_BL;
                    $noteData['note_item_id'] = $blId;
                    $note->exchangeArray($noteData);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, null, false, 'notesFiles', true);
                }
                
                // save files
                $note = new Note();
                $noteData['note_text'] = '';
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_BL;
                $noteData['note_item_id'] = $blId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'notesFiles', true);
                
                if((int)$id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update breachlog "' . $blId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new breachlog "' . $blId . '"');
                }

                return $this->redirect()->toRoute('breachlog', array('controller' => 'breachlog', 'action' => 'list'));
            } else {
                foreach ($form->getMessages() as $messageId => $message) {
                   // echo "Validation failure '$messageId': $message<br/>";
                }

                if(!$blObj || $blObj->bl_reportable == 1) {
                    $ymds['bl_date_of_occurrence'] = \DateTime::createFromFormat('Y-m-d', $post['bl_date_of_occurrence']);
                }
                $ymds['bl_date_invest_start'] = \DateTime::createFromFormat('Y-m-d', $post['bl_date_invest_start']);
                $ymds['bl_date_invest_complete'] = \DateTime::createFromFormat('Y-m-d', $post['bl_date_invest_complete']);
                
                foreach($ymds as $ymdKey => $ymd) {
                    if (is_object($ymd)) {
                        $post[$ymdKey] = $ymd->format('m/d/Y');
                    } else {
                        $post[$ymdKey] = '';
                    }
                }
                $form->setData($post);

                if ((int) $id) {
                    $form->bind($blObj);
                }
            }

        } else {
            if((int)$id) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit breachlog "' . $id . '" page');
            } else {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new breachlog page');
            }
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);

            if ((int) $id) {
                $blObj->bl_date_of_occurrence = ($blObj->bl_date_of_occurrence != '0000-00-00 00:00:00') ? substr($blObj->bl_date_of_occurrence, 0, 10) : '';
                $blObj->bl_date_invest_start = ($blObj->bl_date_invest_start != '0000-00-00 00:00:00') ? substr($blObj->bl_date_invest_start, 0, 10) : '';
                $blObj->bl_date_invest_complete = ($blObj->bl_date_invest_complete != '0000-00-00 00:00:00') ? substr($blObj->bl_date_invest_complete, 0, 10) : '';
                $form->bind($blObj);
            }
        }

        $questions = $this->getBreachlogquestionTable()->getBreachlogquestionsWithAnswers($id);

        return array(
            'form' => $form,
            'notes' => $notes,
            'formNote' => $formNote,
            'blId' => $id,
            'etype' => $etype,
            'blObj' => $blObj,
            'companyUsers' => $companyUsers,
            'questions' => $questions,
            'questionsFormAnswers' => $questionsFormAnswers,
            'questionsErrors' => $questionsErrors,
            'companyRolesMsg' => $companyRolesMsg,
            'regulations' => $this->getRegulationTable()->getRegulations(),
        );
    }

    // TODO is this restrictive enough?
    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getBreachlogTable()->deleteBreachlog($id);
        $this->flashMessenger()->addSuccessMessage('Breach log has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete breachlog "' . $id . '"');

        return $this->redirect()->toRoute('breachlog', array('controller' => 'breachlog', 'action' => 'list'));
    }

    public function encryptAction()
    {
        //$this->getBreachlogTable()->encryptItems();
        return true;
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getBreachlogTable()->unarchiveBreachlog($id);
        $this->flashMessenger()->addSuccessMessage('Breach log has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive breachlog "' . $id . '"');

        return $this->redirect()->toRoute('breachlog', array('controller' => 'breachlog', 'action' => 'list'));

    }
    
    public function getCompanyUsersAction()
    {
        $cId = $this->params('id');

        $users = $this->getUserTable()->getUsersByCompany($cId);
        
        return new JsonModel($users);

    }
}
