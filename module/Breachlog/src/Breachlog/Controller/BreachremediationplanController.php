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
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Note\Form\NoteForm;
use Breachlog\Form\BreachremediationplanForm;
use Breachlog\Form\TaskForm;
use Note\Model\Note;
use Breachlog\Model\Breachremediationplanaction;
use Mail\Model\Mailtemplate;
use Zend\Session\Container;

class BreachremediationplanController extends AbstractActionController
{
    protected $breachremediationplanTable;
    protected $breachremediationplanactionTable;
    protected $userTable;
    protected $noteTable;
    protected $mailtemplateTable;

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
        if (!$this->getUserTable()->checkModulesAccess('breach')) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getBreachremediationplanTable()
    {
        if (!$this->breachremediationplanTable) {
            $sm = $this->getServiceLocator();
            $this->breachremediationplanTable = $sm->get('Breachlog\Model\BreachremediationplanTable');
        }
        return $this->breachremediationplanTable;
    }

    public function getBreachremediationplanactionTable()
    {
        if (!$this->breachremediationplanactionTable) {
            $sm = $this->getServiceLocator();
            $this->breachremediationplanactionTable = $sm->get('Breachlog\Model\BreachremediationplanactionTable');
        }
        return $this->breachremediationplanactionTable;
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

    public function getRegulationTable()
    {
        if (!isset($this->regulationTable)) {
            $sm = $this->getServiceLocator();
            $this->regulationTable = $sm->get('Traininglog\Model\RegulationTable');
        }
        return $this->regulationTable;
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
            'id' => 'brp_id',
            'status' => 'brp_status',
            'date' => 'brp_incident_date',
            'cId' => 'brp_c_id',
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'brp_id';
        $paginator = $this->getBreachremediationplanTable()->getBreachremediationplans(true, $sortCol, $order, $this->getIdentity());

        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open breachlog action list page');

        return $view;
    }

    public function editAction()
    {
        $id = (int) $this->params('id');
        $type = $this->params('type');

        $request = $this->getRequest();

        $this->getBreachremediationplanTable()->setStatus($id, \Breachlog\Model\Breachremediationplan::STATUS_OPEN);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_BRP, $id);

        $noteform = $request->isPost() && (int) $request->getPost('noteform');
        $form = new BreachremediationplanForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());

        $notes = null;
        $brpObj = null;
        if ((int) $id) {
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_BRP);
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);
            if ($noteform) {
                if ($post['requestreview'] == 1) {
                    $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'requestreview', 'brpId' => $id, 'uId' => $post['brp_approver_u_id']));
                    $this->flashMessenger()->addSuccessMessage('Request Review sent');
                    return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'list'));
                }
                $id = $this->getBreachremediationplanTable()->clonePlan($id);


                $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_initials', $post['brp_initials'], true);
                $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_initials_approver', $post['brp_initials_approver'], true);

                $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_performed_u_id', $post['brp_performed_u_id']);
                $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_approver_u_id', $post['brp_approver_u_id']);
                $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_accepter_u_id', $post['brp_accepter_u_id']);
                $this->getBreachremediationplanTable()->setRegulations($id, $post['_brp_cur_regulations'] , $post['_regulation']);

                if (!$post['signedoff']) {
                    $ymd1 = \DateTime::createFromFormat('m/d/Y', $post['brp_incident_date']);
                    if (is_object($ymd1)) {
                        if ($ymd1->format('Y') > date("Y")) {
                            $ymd1->setDate('2014', $ymd1->format('m'), $ymd1->format('d'));
                        }
                        $ymd1 = $ymd1->format('Y-m-d');
                    } else {
                        $ymd1 = '';
                    }
                    $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_incident_date', $ymd1, true);

                    $ymd2 = \DateTime::createFromFormat('m/d/Y', $post['brp_remediation_date']);
                    if (is_object($ymd2)) {
                        if ($ymd2->format('Y') > date("Y")) {
                            $ymd2->setDate('2014', $ymd2->format('m'), $ymd2->format('d'));
                        }
                        $ymd2 = $ymd2->format('Y-m-d');
                    } else {
                        $ymd2 = '';
                    }
                    $this->getBreachremediationplanTable()->setFieldValue($id, 'brp_remediation_date', $ymd2, true);
                }

                if ($post['signedoff'] == 1) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Sign off breachlog action "' . $id . '"');
                    $this->getBreachremediationplanTable()->setStatus($id, \Breachlog\Model\Breachremediationplan::STATUS_SIGNED_OFF);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_SIGNEDOFF, \Application\Model\LogsTable::ITEM_TYPE_BRP, $id);
                }

                $note = new Note();
                $formNote->setInputFilter($note->getInputFilter($this->getServiceLocator(), $id));
                $formNote->setData($request->getPost());

                if ($formNote->isValid()) {
                    $post['note_item_id'] = $id;
                    $note->exchangeArray($post);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                    $this->flashMessenger()->addSuccessMessage('Plan saved');
                }

                return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'list'));
            }
        } else if ($type != 'pdf') {
            if((int)$id) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit breachlog action "' . $id . '" page');
            } else {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new breachlog action page');
            }
        }

        $brpObj = $this->getBreachremediationplanTable()->getBreachremediationplan($id);
        $actions = $this->getBreachremediationplanactionTable()->getBreachremediationplanactions($id);
        $noteTable = $this->getNoteTable();

        $form->bind($brpObj);

        $identity = $this->getIdentity();

        $userTable = $this->getServiceLocator()->get('Admin\Model\UserTable');
        $contacts = array();
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
            $contacts[$key] = $r;
        }

        $contactsApr = array();
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
            $contactsApr[$key] = $r;
        }

        $view = new ViewModel(array(
            'id' => $id,
            'formNote' => $formNote,
            'form' => $form,
            'notes' => $notes,
            'brpObj' => $brpObj,
            'actions' => $actions,
            'writable' => is_object($brpObj) ? $brpObj->brp_writable : false,
            'noteTable' => $noteTable,
            'isAdmin' => $identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN ? true : false,
            'contacts' => $contacts,
            'contactsApr' => $contactsApr,
            'approverAccepter' => $userTable->getContactsByCompanyId($brpObj->brp_c_id),
            'regulations' => $this->getRegulationTable()->getRegulations(),
        ));

        if ($type == 'pdf') {
            $domLibPath =  $_SERVER['DOCUMENT_ROOT'] . '/../vendor';

            $domLibPath = $domLibPath . "/dompdf/dompdf_config.inc.php";
            require_once $domLibPath;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Get pdf for breachlog plan "' . $id . '"');

            $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');

            $model = new ViewModel(array(
                'id' => $id,
                'form' => $form,
                'formNote' => $formNote,
                'notes' => $notes,
                'brpObj' => $brpObj,
                'actions' => $actions,
                'writable' => $brpObj->brp_writable,
                'noteTable' => $noteTable,
                'regulations' => $this->getRegulationTable()->getRegulations(),
                'isPdf' => true
            ));
            $model->setTemplate('breachremediationplan/pdfTemplate');

            $html = $renderer->render($model);

            $html = str_replace('§', '&#167;', $html);

            set_time_limit(300);
            ini_set('memory_limit', '-1');

            require_once './vendor/mylib/library/mpdf60/mpdf.php';

            $mpdf = new \mPDF('utf-8', 'A4-L'); 
  
            $mpdf->WriteHTML($html);
            $mpdf->Output('breachremediationplan_ ' . date('Y_m_d_h_i_s', time()) . '.pdf', 'D');

            exit();
        } elseif ($type == 'csv') {
            $this->_generateCsv($notes, $brpObj, $actions);
        }

        return $view;
    }


    public function savefilesAction()
    {
        $id = (int) $this->params('id');
        $brpId = (int) $this->params('brpId');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            //$id = $this->getBreachremediationplanTable()->clonePlan($id);

            // save files
            $note = new Note();
            $noteData['note_text'] = '';
            $noteData['note_item_type'] = \Note\Model\Note::NOTE_BRPA;
            $noteData['note_item_id'] = $id;
            $note->exchangeArray($noteData);
            $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
            $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Save files for breachlog action "' . $id . '" for breachlog "' . $brpId . '"');

        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'edit', 'id' => $brpId));
    }

    public function _generateCsv($notes, $brpObj, $actions)
    {
        ob_start();
        $csvList[] = 'sep=,';
        $csvList[] = 'Breach Remediation Plan for ' . $brpObj->_client_name;
        $csvList[] = '';
        $csvList[] = array(
                        'STATUS: ' . \Breachlog\Model\Breachremediationplan::$statusesNames[$brpObj->brp_status],
                        'PERFORMED BY: ' . $brpObj->_performed_name,
                        'BREACH INCIDENT DATE: ' . $brpObj->_brp_incident_date_formatted,
                        'REMEDIATION PLAN DATE: ' . $brpObj->_brp_remediation_date_formatted,
                    );
        $csvList[] = '';
        $csvList[] = array(
            'Task',
            'Action Plan',
            'Status',
            'Assignee',
            'Target Date',
        );
        $csvList[] = '';
        foreach ($actions as $brpa) {
            $csvList[] = array(
                $brpa->brpa_task,
                $brpa->brpa_action_plan,
                \Breachlog\Model\Breachremediationplanaction::$statusesNames[$brpa->brpa_status],
                $brpa->_contact_name,
                ($brpa->brpa_target_date != '0000-00-00') ? substr($brpa->brpa_target_date, 0, 10) : '',
            );

            $notesTask = $this->noteTable->getNotes($brpa->brpa_id, \Note\Model\Note::NOTE_BRPA);
            if (is_object($notesTask) && ($notesTask->count())) {
                $csvList[] = 'Attachments:';
                foreach ($notesTask as $note) {
                    if ($note->_files != '') {
                        $_files = explode(',', $note->_files);
                        foreach ($_files as $_file) {
                            $_file = explode('::', $_file);
                            $_fileName = $_file[0];
                            $_fileId = $_file[1];

                            $csvList[] = $_fileName;
                        }
                    }
                }
            }
            $csvList[] = '';
        }
        $csvList[] = '';

        if (is_object($notes) && ($notes->count())) {
            foreach ($notes as $note) {
                $csvList[] = $note->_username . ' on ' . $note->_note_create_date_format;
                $csvList[] = $note->note_text;

                if ($note->_files != '') {
                    $_files = explode(',', $note->_files);
                    $csvList[] = 'Attachments:';
                    foreach ($_files as $_file) {
                        $_file = explode('::', $_file);
                        $_fileName = $_file[0];
                        $_fileId = $_file[1];

                        $csvList[] = $_fileName;
                    }
                }
                $csvList[] = '';
            }
        }

        $csvList[] = 'Initials:';
        $csvList[] = $brpObj->brp_initials;

        if($brpObj->_brp_cur_regulations) {
            $regulations = $this->getRegulationTable()->getRegulations();
            if($regulations) {
                $csvList[] = 'Regulations:';
                foreach ($regulations as $regulation) {
                    if(in_array($regulation->rg_id, $brpObj->_brp_cur_regulations) !== false) {
                        $csvList[] = array('Policy&Procedure Name: ', $regulation->rg_pp_name);
                        $csvList[] = array('Policy&Procedure Number: ', $regulation->rg_pp_number);
                        $csvList[] = array('Regulation Number: ', $regulation->rg_number);
                        if($regulation->rg_u_owner_id) {
                            $csvList[] = array('Regulation Description:', $regulation->rg_description);
                        }
                        $csvList[] = '';
                    }
                }
            }
        }

        $csvContent = '';
        foreach ($csvList as $row) {
            if (is_array($row)) {
                $csvContent .= '"' . implode('","', $row) . '"' . "\n";
            } else {
                $csvContent .= $row . "\n";
            }
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Get csv for breachlog plan "' . $brpObj->brp_id . '"');
// print_r($csvContent);exit;
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="breachremediationplan_ ' . date('Y_m_d_h_i_s', time()) . '.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        @ob_clean();
        @flush();
        echo ($csvContent);
        exit;

        echo '<pre>';
        print_r($csvContent);
        die;
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getBreachremediationplanTable()->deleteBreachremediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Breach remediation plan has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete breachlog plan "' . $id . '"');

        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getBreachremediationplanTable()->unarchiveBreachremediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Breach remediation plan has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive breachlog plan "' . $id . '"');

        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'list'));
    }

    public function reopenAction()
    {
        $identity = $this->getIdentity();

        if ($identity['u_role_id'] != \Admin\Model\User::ROLE_ADMIN) {
            die;
        }

        $id = $this->params('id');

        $this->getBreachremediationplanTable()->reopenBreachremediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Breach Remediation plan has been opened');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Reopen breachlog plan "' . $id . '"');

        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'list'));
    }

    public function edittaskAction()
    {
        $request = $this->getRequest();
        $id = (int) $this->params('id');
        $brpId = $this->params('brpId');

        $brpObj = $this->getBreachremediationplanTable()->getBreachremediationplan($brpId);
        $notes = null;
        if ($id) {
            $brpaObj = $this->getBreachremediationplanactionTable()->getBreachremediationplanaction($id);
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_BRPA);
        }

        $formNote = new NoteForm($this->getServiceLocator());
        $formTask = new TaskForm($this->getServiceLocator(), $brpObj);
        $added = false;
        if ($request->isPost()) {
            $brpa = new Breachremediationplanaction();
            $formTask->setInputFilter($brpa->getInputFilter($this->getServiceLocator(), $id));
            $formTask->setData($request->getPost());
            $post = $request->getPost();

            if ($formTask->isValid()) {
                if (!$id) {
                    $brpId = $this->getBreachremediationplanTable()->clonePlan($brpId);
                }
                
                $ymds['brpa_target_date'] = \DateTime::createFromFormat('m/d/Y', $post['brpa_target_date']);
                
                foreach($ymds as $ymdKey => $ymd) {
                    if (is_object($ymd)) {
                        $post[$ymdKey] = $ymd->format('Y-m-d');
                    } else {
                        $post[$ymdKey] = '';
                    }
                }
                
                $post['brpa_brp_id'] = $brpId;
                $brpa->exchangeArray($post);
                $this->getBreachremediationplanactionTable()->setServiceLocator($this->getServiceLocator());
                $brpaId = $this->getBreachremediationplanactionTable()->saveBreachremediationplanaction($brpa);

                if ($id) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update breachlog plan action "' . $brpaId . '" for breachlog plan "' . $brpId . '"');
                } else {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add breachlog plan action "' . $brpaId . '" for breachlog plan "' . $brpId . '"');
                }
                // save files
                $note = new Note();

                $noteData['note_text'] = $post['note_text'];
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_BRPA;
                $noteData['note_item_id'] = $brpaId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                /////////////////
                $added = true;
                //return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'edit', 'id' => $brpId));
            } else {
                if ((int) $id) {
                    $formTask->bind($brpaObj);
                }
            }
        } else {

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open breachlog plan task edit page "' . $id . '" for breachlog plan "' . $brpId . '"');

            if ((int) $id) {
                $brpaObj->brpa_target_date = ($brpaObj->brpa_target_date != '0000-00-00') ? substr($brpaObj->brpa_target_date, 0, 10) : '';
                $formTask->bind($brpaObj);
            }
        }

        $viewModel = new ViewModel(array(
            'formTask' => $formTask,
            'formNote' => $formNote,
            'id' => $id,
            'brpId' => $brpId,
            'added' => $added,
            'notes' => $notes,
            'writable' => is_object($brpObj) ? $brpObj->brp_writable : false,
            'brpaObj' => is_object($brpaObj) ? $brpaObj : false,
        ));

        $viewModel->setTemplate('breachlog/breachremediationplan/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function deletetaskAction()
    {
        $id = $this->params('id');
        $brpId = $this->params('brpId');

        $this->getBreachremediationplanactionTable()->deleteBreachremediationplanaction($id);
        $this->flashMessenger()->addSuccessMessage('Task has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete breachlog plan action "' . $id . '" for breachlog plan "' . $brpId . '"');
        
        return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'edit', 'id' => $brpId));
    }

    public function breachplanemailAction()
    {
        $id = $this->params('id');
        $brpaObj = $this->getBreachremediationplanactionTable()->getBreachremediationplanaction($id);
        $brpObj = $this->getBreachremediationplanTable()->getBreachremediationplan($brpaObj->brpa_brp_id);

        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($brpObj->brp_consultant_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('breachplan');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $brpObj->_client_name, $text);
        $text = str_replace('<Target Date>',  $brpaObj->brpa_target_date, $text);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>',  $brpaObj->brpa_task, $text);
        $text = str_replace('<Action plan>',  $brpaObj->brpa_action_plan, $text);

        $viewModel = new ViewModel(array(
            'title' => str_replace('<Client Name>',  $brpObj->_client_name, $mt->mt_name),
            'text' => $text,
            'header_title' => $brpaObj->_contact_name,
            'title_label' => 'Assignee'
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send email breach remediation plan action "' . $id . '" for breach remediation plan "' . $brpObj->brp_id . '"');

        return $viewModel;
    }

    public function remediationplanemailAction()
    {
        $id = $this->params('id');
        $brpaObj = $this->getBreachremediationplanactionTable()->getBreachremediationplanaction($id);
        $brpObj = $this->getBreachremediationplanTable()->getBreachremediationplan($brpaObj->brpa_brp_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($brpObj->brp_consultant_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('remediationplan');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $brpObj->_client_name, $text);
        $text = str_replace('<Target Date>',  $brpaObj->brpa_target_date, $text);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>',  $brpaObj->brpa_task, $text);
        $text = str_replace('<Action plan>',  $brpaObj->brpa_action_plan, $text);

        $viewModel = new ViewModel(array(
            'title' => $mt->mt_name,
            'text' => $text,
            'header_title' => $brpaObj->_contact_name,
            'title_label' => 'Assignee'
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send email remediation plan action "' . $id . '" for breach remediation plan "' . $brpObj->brp_id . '"');


        return $viewModel;
    }

    public function remediationplanapproveremailAction()
    {
        $id = $this->params('id');
        $brpaObj = $this->getBreachremediationplanactionTable()->getBreachremediationplanaction($id);
        $brpObj = $this->getBreachremediationplanTable()->getBreachremediationplan($brpaObj->brpa_brp_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($brpObj->brp_consultant_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('emailapprover');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $brpObj->_client_name, $text);
        $text = str_replace('<Target Date>',  $brpaObj->brpa_target_date, $text);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>',  $brpaObj->brpa_task, $text);
        $text = str_replace('<Action plan>',  $brpaObj->brpa_action_plan, $text);

        $viewModel = new ViewModel(array(
            'title' => $mt->mt_name,
            'text' => $text,
            'header_title' => $brpaObj->_contact_name,
            'title_label' => 'Assignee'
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send approver email for breach remediation plan action "' . $id . '" for breach remediation plan "' . $brpObj->brp_id . '"');

        return $viewModel;
    }


}
