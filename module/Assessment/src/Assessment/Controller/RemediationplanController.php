<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Assessment\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Note\Form\NoteForm;
use Assessment\Form\TaskForm;
use Note\Model\Note;
use Assessment\Model\Remediationplanaction;
use Mail\Model\Mailtemplate;
use Zend\Session\Container;
use Assessment\Form\ImportForm;
use Assessment\Model\Remediationplan;

use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\View\Model\JsonModel;


class RemediationplanController extends AbstractActionController
{
    protected $remediationplanTable;
    protected $remediationplanactionTable;
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
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function getRemediationplanTable()
    {
        if (!$this->remediationplanTable) {
            $sm = $this->getServiceLocator();
            $this->remediationplanTable = $sm->get('Assessment\Model\RemediationplanTable');
        }
        return $this->remediationplanTable;
    }

    public function getRemediationplanactionTable()
    {
        if (!$this->remediationplanactionTable) {
            $sm = $this->getServiceLocator();
            $this->remediationplanactionTable = $sm->get('Assessment\Model\RemediationplanactionTable');
        }
        return $this->remediationplanactionTable;
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

    public function getNotefilesTable()
    {
        if (!$this->noteFilesTable) {
            $sm = $this->getServiceLocator();
            $this->noteFilesTable = $sm->get('Note\Model\NotesFilesTable');
        }
        return $this->noteFilesTable;
    }

    public function getCompanyRolesTable()
    {
        if (!$this->companyRolesTable) {
            $sm = $this->getServiceLocator();
            $this->companyRolesTable = $sm->get('Client\Model\CompanyRolesTable');
        }
        return $this->companyRolesTable;
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
            'id' => 'rp_id',
            'status' => 'rp_status',
            'type' => 'rp_type',
            'date' => 'rp_incident_date',
            'cId' => 'rp_c_id',
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'rp_id';
        $paginator = $this->getRemediationplanTable()->getRemediationplans(true, $sortCol, $order, $this->getIdentity());

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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open remediationplan list page');

        return $view;
    }

    public function editAction()
    {
        $id = (int) $this->params('id');
        $type = $this->params('type');
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);

        $request = $this->getRequest();

        $this->getRemediationplanTable()->setStatus($id, \Assessment\Model\Remediationplan::STATUS_OPEN);

        $noteform = $request->isPost() && (int) $request->getPost('noteform');
        $formNote = new NoteForm($this->getServiceLocator());

        $notes = null;
        $rpObj = null;
        if ((int) $id) {
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RP);
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            if ($noteform) {
                if ($post['requestreview'] == 1) {
                    $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'requestreview', 'rpId' => $id, 'uId' => $post['rp_approver_u_id']));
                    $this->flashMessenger()->addSuccessMessage('Request Review sent');
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Request review sent, remediationplan "' . $id . '"');
                    return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
                }

                $fieldValues = array( 'rp_initials'          => $post['rp_initials']
                                    , 'rp_initials_approver' => $post['rp_initials_approver']
                                    , 'rp_performed_u_id'    => $post['rp_performed_u_id']
                                    , 'rp_approver_u_id'     => $post['rp_approver_u_id']
                                    , 'rp_accepter_u_id'     => $post['rp_accepter_u_id']
                                    );

                $fieldValues['rp_approved_date'] = \DateTime::createFromFormat('m/d/Y', $post['rp_approved_date'])->format('Y-m-d');
                $fieldValues['rp_accepted_date'] = \DateTime::createFromFormat('m/d/Y', $post['rp_accepted_date'])->format('Y-m-d');

                $this->getRemediationplanTable()->setFieldValues($id, $fieldValues);

                if ($post['save_and_copy_button']) {
                    $id = $this->getRemediationplanTable()->clonePlan($id, $post);
                } else if($post['signedoff'] == 1){
                    $this->getRemediationplanTable()->clonePlan($id, $post, true);
                }
                
                if ($post['signedoff'] == 1) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Sign off remediationplan "' . $id . '"');
                    $this->getRemediationplanTable()->setStatus($id, \Assessment\Model\Remediationplan::STATUS_SIGNED_OFF);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_SIGNEDOFF, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);
                } else if($post['save_button']) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Save remediationplan "' . $id . '"');
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);
                }

                $note = new Note();
                $formNote->setInputFilter($note->getInputFilter($this->getServiceLocator(), $id));
                $formNote->setData($request->getPost());

                if ($formNote->isValid()) {
                    $post['note_item_id'] = $id;
                    $note->exchangeArray($post);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Save remediationplan "' . $id . '"');
                    $this->flashMessenger()->addSuccessMessage('Plan saved');
                }

                return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
            }
        } else {
            if ((int) $id) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit remediationplan "' . $id . '" page');
            }
        }
        
        $mappingSortCol = array(
            'status' => 'rpa_status',
            'type' => 'rpa_type',
            'assignee' => '_contact_name',
            'approver' => '_approver_name',
            'date' => 'rpa_target_date',
        );
        
        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : '';
        
        $rpObj = $this->getRemediationplanTable()->getRemediationplan($id);
        $rpObj->_client_name = stripslashes($rpObj->_client_name);
        $actions = $this->getRemediationplanactionTable()->getRemediationplanactions($id, $sortCol, $order);

        $noteTable = $this->getNoteTable();

        $identity = $this->getIdentity();

        $userTable = $this->getServiceLocator()->get('Admin\Model\UserTable');
        $contacts = array();
        $contactsApr = array();

        if ($rpObj->rp_c_id) {
            $company = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompany($rpObj->rp_c_id);
            $userPrimary = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($company->c_primary_contact_u_id);
            $userConsultant = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($company->c_consultant_u_id);

            if (is_object($userPrimary)) {
                $contacts[$userPrimary->u_id] = $userPrimary->u_firstname . ' ' . $userPrimary->u_lastname;
                $contactsApr[$userPrimary->u_id] = $userPrimary->u_firstname . ' ' . $userPrimary->u_lastname;
            }
            if (is_object($userConsultant)) {
                $contacts[$userConsultant->u_id] = $userConsultant->u_firstname . ' ' . $userConsultant->u_lastname;
                $contactsApr[$userConsultant->u_id] = $userConsultant->u_firstname . ' ' . $userConsultant->u_lastname;
            }
        }

        if ($rpObj->rp_a_id) {
            $complianceOfficersIds = $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleLocationContactTable')->getComplianceOfficers($rpObj->rp_a_id);
            $complianceOfficers = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByIds($complianceOfficersIds);
            foreach ($complianceOfficers as $key => $r) {
                $contacts[$key] = $r;
                $contactsApr[$key] = $r;
            }
        }
        $view = new ViewModel(array(
            'id' => $id,
            'formNote' => $formNote,
            'notes' => $notes,
            'rpObj' => $rpObj,
            'actions' => $actions,
            'writable' => is_object($rpObj) ? $rpObj->rp_writable : false,
            'noteTable' => $noteTable,
            'isAdmin' => $identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN ? true : false,
            'contacts' => $contacts,
            'contactsApr' => $contactsApr,
            'approverAccepter' => $this->getRemediationplanTable()->getApproverAccepter($rpObj->rp_id),
            'order_by' => $orderBy,
            'order' => $order,
            'urlOrder' => $order == 'ASC' ? 'DESC' : 'ASC',
            'roleFilter' => $roleFilter
        ));

        if ($type == 'pdf') {
            $domLibPath =  $_SERVER['DOCUMENT_ROOT'] . '/../vendor';

            $domLibPath = $domLibPath . "/dompdf/dompdf_config.inc.php";
            require_once $domLibPath;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Get pdf for remediationplan "' . $id . '"');

            $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');

            $model = new ViewModel(array(
                'id' => $id,
                'formNote' => $formNote,
                'notes' => $notes,
                'rpObj' => $rpObj,
                'actions' => $actions,
                'writable' => $rpObj->rp_writable,
                'noteTable' => $noteTable,
                'isPdf' => true
            ));
            $model->setTemplate('remediationplan/pdfTemplate');

            $html = $renderer->render($model);

            $html = str_replace('§', '&#167;', $html);

            set_time_limit(300);
            ini_set('memory_limit', '-1');

            require_once './vendor/mylib/library/mpdf60/mpdf.php';

            $mpdf = new \mPDF('utf-8', 'A4-L'); 
  
            $mpdf->WriteHTML($html);
            $mpdf->Output('remediationplan_ ' . date('Y_m_d_h_i_s', time()) . '.pdf', 'D');

            exit();
        } elseif ($type == 'csv') {
            $this->_generateCsv($notes, $rpObj, $actions);
        }

        return $view;
    }


    public function savefilesAction()
    {
        $id = (int) $this->params('id');
        $rpId = (int) $this->params('rpId');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            //$id = $this->getBreachremediationplanTable()->clonePlan($id);

            // save files
            $note = new Note();
            $noteData['note_text'] = '';
            $noteData['note_item_type'] = \Note\Model\Note::NOTE_RPA;
            $noteData['note_item_id'] = $id;
            $note->exchangeArray($noteData);
            $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
            $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Save files for remediationplan "' . $rpId . '"');

        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'edit', 'id' => $rpId));
    }

    public function _generateCsv($notes, $rpObj, $actions)
    {
        $csvList[] = 'sep=,';
        $csvList[] = 'Remediation Plan for ' . $rpObj->_client_name;
        $csvList[] = '';
        $csvList[] = array(
                        'STATUS: ' . \Assessment\Model\Remediationplan::$statusesNames[$rpObj->rp_status],
                        'PERFORMED BY: ' . $rpObj->_performed_name,
                        'ASSESSMENT DATE: ' . $rpObj->_rp_incident_date_formatted,
                        'PLAN DATE: ' . $rpObj->_rp_remediation_date_formatted,
                    );
        $csvList[] = '';
        $csvList[] = array(
            'Risk level',
            'Threat',
            'Action Plan',
            'Policy number',
            'Status',
            'Assignee',
            'Target Date',
        );
        $csvList[] = '';
        
        foreach ($actions as $rpa) {
            //$rpa->rpa_threat = str_replace('§', utf8_decode('§'), $rpa->rpa_threat);

            $rpa->rpa_threat = str_replace('Â', '', $rpa->rpa_threat);
            $rpa->rpa_threat = str_replace('§', utf8_decode('§'), $rpa->rpa_threat);

            //echo $rpa->rpa_threat;
            //die;
            $csvList[] = array(
                \Assessment\Model\Remediationplanaction::$levelsNames[$rpa->rpa_risk_level],
                str_replace("\r\n", " ", $rpa->rpa_threat),
                str_replace("\r\n", " ", $rpa->rpa_action_plan),
                $rpa->rpa_policy,
                \Assessment\Model\Remediationplanaction::$statusesNames[$rpa->rpa_status],
                $rpa->_contact_name,
                ($rpa->rpa_target_date != '0000-00-00') ? substr($rpa->rpa_target_date, 0, 10) : '',
            );

            $notesTask = $this->noteTable->getNotes($rpa->rpa_id, \Note\Model\Note::NOTE_RPA);
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
            //$csvList[] = '';
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

        $csvList[] = 'Initials';
        $csvList[] = $rpObj->rp_initials;

        $csvContent = '';
        foreach ($csvList as $row) {
            if (is_array($row)) {
                $csvContent .= '"' . implode('","', $row) . '"' . "\n";
            } else {
                $csvContent .= $row . "\n";
            }
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Get csv for remediationplan "' . $rpObj->rp_id . '"');

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="remediationplan_ ' . date('Y_m_d_h_i_s', time()) . '.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        ob_clean();
        flush();
        echo ($csvContent);
        exit;

        echo '<pre>';
        print_r($csvContent);
        die;
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getRemediationplanTable()->deleteRemediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Remediation plan has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete remediationplan "' . $id . '"');

        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getRemediationplanTable()->unarchiveRemediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Remediation plan has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive remediationplan "' . $id . '"');

        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
    }

    public function reopenAction()
    {
        $identity = $this->getIdentity();

        if ($identity['u_role_id'] != \Admin\Model\User::ROLE_ADMIN) {
            die;
        }

        $id = $this->params('id');

        $this->getRemediationplanTable()->reopenRemediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Remediation plan has been opened');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Reopen remediationplan "' . $id . '"');

        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
    }

    public function edittaskAction()
    {
        $request = $this->getRequest();
        $id = (int) $this->params('id');
        $rpId = $this->params('rpId');

        $rpObj = $this->getRemediationplanTable()->getRemediationplan($rpId);
        $notes = null;
        if ($id) {
            $rpaObj = $this->getRemediationplanactionTable()->getRemediationplanaction($id);
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open remediationplan "' . $rpId . '" task edit page "' . $id . '"');

        $formNote = new NoteForm($this->getServiceLocator());
        $formTask = new TaskForm($this->getServiceLocator(), $rpObj);
        $added = false;
        if ($request->isPost()) {
            $rpa = new Remediationplanaction();
            $formTask->setInputFilter($rpa->getInputFilter($this->getServiceLocator(), $id));
            $formTask->setData($request->getPost());
            $post = $request->getPost();

            if ($formTask->isValid()) {
                if (!$id) {
                    $rpId = $this->getRemediationplanTable()->clonePlan($rpId);
                }
                                
                $ymds['rpa_target_date'] = \DateTime::createFromFormat('m/d/Y', $post['rpa_target_date']);
                
                foreach($ymds as $ymdKey => $ymd) {
                    if (is_object($ymd)) {
                        $post[$ymdKey] = $ymd->format('Y-m-d');
                    } else {
                        $post[$ymdKey] = '';
                    }
                }
                
                $post['rpa_rp_id'] = $rpId;
                $rpa->exchangeArray($post);
                $this->getRemediationplanactionTable()->setServiceLocator($this->getServiceLocator());
                $rpaId = $this->getRemediationplanactionTable()->saveRemediationplanaction($rpa, 1);

                // save text note
                if($post['note_text'])
                {
                    $note = new Note();
                    $noteData['note_text'] = $post['note_text'];
                    $noteData['note_item_type'] = \Note\Model\Note::NOTE_RPA;
                    $noteData['note_item_id'] = $rpaId;
                    $note->exchangeArray($noteData);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note);
                }
                // save files
                $note = new Note();
                $noteData['note_text'] = '';
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_RPA;
                $noteData['note_item_id'] = $rpaId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add remediationplan action "' . $rpaId . '" for remediationplan "' . $rpId . '"');
                
                $added = true;
                //return $this->redirect()->toRoute('breachremediationplan', array('controller' => 'breachremediationplan', 'action' => 'edit', 'id' => $brpId));
            } else {
                if ((int) $id) {
                    $formTask->bind($rpaObj);
                }
            }
        } else {
            if ((int) $id) {
                $rpaObj->rpa_target_date = ($rpaObj->rpa_target_date != '0000-00-00') ? substr($rpaObj->rpa_target_date, 0, 10) : '';
                $rpaThreat = str_replace('Â', '', $rpaObj->rpa_threat);
                $rpaObj->rpa_threat = $rpaThreat;

                $formTask->bind($rpaObj);
            }
        }

        if (is_object($rpaObj)) {
            $rpaThreat = str_replace('Â', '', $rpaObj->rpa_threat);
            $rpaObj->rpa_threat = $rpaThreat;
        }

        $viewModel = new ViewModel(array(
            'formTask' => $formTask,
            'formNote' => $formNote,
            'id' => $id,
            'rpId' => $rpId,
            'added' => $added,
            'notes' => $notes,
            'writable' => is_object($rpObj) ? $rpObj->rp_writable : false,
            'rpaObj' => is_object($rpaObj) ? $rpaObj : false,
        ));

        $viewModel->setTemplate('assessment/remediationplan/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function deletetaskAction()
    {
        $id = $this->params('id');
        $rpId = $this->params('rpId');

        $this->getRemediationplanactionTable()->deleteRemediationplanaction($id);
        $this->flashMessenger()->addSuccessMessage('Task has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete remediationplan task "' . $id . '" for remediationplan "' . $rpId . '"');
        
        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'edit', 'id' => $rpId));
    }

    public function planemailAction()
    {
        $id = $this->params('id');
        $rpaObj = $this->getRemediationplanactionTable()->getRemediationplanaction($id);
        $rpObj = $this->getRemediationplanTable()->getRemediationplan($rpaObj->rpa_rp_id);

        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpObj->rp_consultant_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('breachplan');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $rpObj->_performed_name, $text);
        $text = str_replace('<Target Date>',  $rpaObj->rpa_target_date, $text);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>',  $rpaObj->rpa_threat, $text);
        $text = str_replace('<Action plan>',  $rpaObj->rpa_action_plan, $text);

        $viewModel = new ViewModel(array(
            'title' => str_replace('<Client Name>',  $rpObj->_client_name, $mt->mt_name),
            'text' => $text,
            'header_title' => $rpaObj->_contact_name,
            'title_label' => 'Assignee'
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send email for remediationplan action "' . $rpaObj->rpa_id . '" for remediationplan "' . $rpObj->rp_id . '"');

        return $viewModel;
    }

    public function remediationplanemailAction()
    {
        $id = $this->params('id');
        $rpId = $this->params('rpId');
        $contactUId = $this->params('rpa_contact_u_id');
        $contactUId = $this->params('rpa_contact_u_id');
        $attachmentsIds = $this->params('add_atts') ? explode(',', $this->params('add_atts')) : array();
        $uri = $this->getRequest()->getUri();
        $editTaskUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost()) . $this->url()->fromRoute('remediationplan', array('action' => 'edit', 'id' => $rpId)) . '?edittask=' . $id;
        
        $rpaObj = $this->getRemediationplanactionTable()->getRemediationplanaction($id);
        $rpObj = $this->getRemediationplanTable()->getRemediationplan($rpaObj->rpa_rp_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpObj->rp_consultant_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('remediationplan');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $rpaObj->_contact_name, $text);
        $text = str_replace('<Target Date>',  $rpaObj->rpa_target_date, $text);
        $text = str_replace('<Link>',  $editTaskUrl, $text);
        $text = str_replace('<Approver>',  $rpaObj->_approver_name ? $rpaObj->_approver_name : "Approver", $text);
        $text = str_replace('<Company>',  $rpObj->_client_name, $text);

        $subject = str_replace('<Company>', $rpObj->_client_name, $mt->mt_subject);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>',  str_replace('Â', '', $rpaObj->rpa_threat), $text);
        $text = str_replace('<Action plan>', str_replace("\r\n", '', $rpaObj->rpa_action_plan), $text);
        $text = str_replace('<Policy>',  $rpaObj->rpa_policy, $text);

        $assignee = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpaObj->rpa_contact_u_id);
        
        $notes = null;
        $files = array();
        if ($id && !empty($attachmentsIds)) {
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
            if (is_object($notes) && ($notes->count())) {
                $docRoot = $_SERVER['DOCUMENT_ROOT'];
                $notes->buffer();
                foreach($notes as $note) {
                    if ($note->_files != '') {
                        $_files = explode(',', $note->_files);
                        if($_files) {
                            foreach ($_files as $_file) {
                                list($fName, $fId) = explode('::', $_file);
                                
                                if(!in_array($fId, $attachmentsIds)) continue;
                                
                                $filepath = $docRoot . '/data/notefiles/' . $note->note_id . '/' . $fId;
                                if (!file_exists($filepath)) {
                                    $note_id = $this->getNotefilesTable()->getFileNoteByFId($fId);
                                    $filepath = $docRoot . '/data/notefiles/' . $note_id . '/' . $fId;
                                }
                                $files[$note->note_id][$fId]['file_name'] = $fName;
                                $files[$note->note_id][$fId]['file_path'] = $filepath;
                            }
                        }
                    }
                }
            }
        }
        
        $viewModel = new ViewModel(array(
            'title' => $mt->mt_name,
            'text' => $text,
            'header_title' => $rpaObj->_contact_name,
            'title_label' => 'Assignee',
            'subject' => $subject,
            'addTo' => isset($assignee->u_email)&& ($assignee->u_email != '') ? $assignee->u_email : '',
            'notes' => $notes,
            'files' => $files
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send email for remediationplan "' . $rpObj->rp_id . '"');

        return $viewModel;
    }

    public function remediationplanapproveremailAction()
    {
        $id = $this->params('id');
        $rpId = $this->params('rpId');
        $rpaObj = $this->getRemediationplanactionTable()->getRemediationplanaction($id);
        $rpObj = $this->getRemediationplanTable()->getRemediationplan($rpaObj->rpa_rp_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpObj->rp_consultant_u_id);
        $attachmentsIds = $this->params('add_atts') ? explode(',', $this->params('add_atts')) : array();
        $uri = $this->getRequest()->getUri();
        $editTaskUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost()) . $this->url()->fromRoute('remediationplan', array('action' => 'edit', 'id' => $rpId)) . '?edittask=' . $id;

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('emailapprover');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $rpaObj->_approver_name ? $rpaObj->_approver_name : "Approver", $text);
        $text = str_replace('<Target Date>',  $rpaObj->rpa_target_date, $text);
        $text = str_replace('<Link>',  $editTaskUrl, $text);
        $text = str_replace('<Company>',  $rpObj->_client_name, $text);

        $subject = str_replace('<Company>', $rpObj->_client_name, $mt->mt_subject);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>', str_replace('Â', '', $rpaObj->rpa_threat), $text);
        $text = str_replace('<Action plan>', str_replace("\r\n", '', $rpaObj->rpa_action_plan), $text);

        $approver = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpaObj->rpa_approver_u_id);
        
        $notes = null;
        $files = array();
        if ($id && !empty($attachmentsIds)) {
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
             if (is_object($notes) && ($notes->count())) {
                $docRoot = $_SERVER['DOCUMENT_ROOT'];
                $notes->buffer();
                foreach($notes as $note) {
                    if ($note->_files != '') {
                        $_files = explode(',', $note->_files);
                        if($_files) {
                            foreach ($_files as $_file) {
                                list($fName, $fId) = explode('::', $_file);
                                
                                if(!in_array($fId, $attachmentsIds)) continue;

                                $filepath = $docRoot . '/data/notefiles/' . $note->note_id . '/' . $fId;
                                if (!file_exists($filepath)) {
                                    $note_id = $this->getNotefilesTable()->getFileNoteByFId($fId);
                                    $filepath = $docRoot . '/data/notefiles/' . $note_id . '/' . $fId;
                                }
                                $files[$note->note_id][$fId]['file_name'] = $fName;
                                $files[$note->note_id][$fId]['file_path'] = $filepath;
                            }
                        }
                    }
                }
            }
        }
        
        $viewModel = new ViewModel(array(
            'title' => $mt->mt_name,
            'text' => $text,
            'header_title' => $rpaObj->_contact_name,
            'title_label' => 'Assignee',
            'subject' => $subject,
            'addTo' => isset($approver->u_email)&& ($approver->u_email != '') ? $approver->u_email : '',
            'notes' => $notes,
            'files' => $files
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send approver email for remediationplan action "' . $rpaObj->rpa_id . '" for remediationplan "' . $rpObj->rp_id . '"');

        return $viewModel;
    }

    public function savetaskAction()
    {
        $request = $this->getRequest();
        $post = $request->getPost();

        $values = $post['values'];

        $output = array();
        parse_str($values, $output);

        $rpa = new Remediationplanaction();

        $rpa->exchangeArray($output);
        $this->getRemediationplanactionTable()->setServiceLocator($this->getServiceLocator());
        $rpaId = $this->getRemediationplanactionTable()->saveRemediationplanaction($rpa, 1);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Save task for remediationplan action "' . $rpa->rpa_id . '" for remediationplan "' . $rpa->rpa_rp_id . '"');

        return 1;
    }

    public function sendemailtestAction()
    {
        $mail = new Mail\Message();
        $mail->setFrom('postmaster@click5dev7.com', 'HIPAA Suite');


        $html = new \Zend\Mime\Part('test');
        $html->type = 'text/html';
        $body = new \Zend\Mime\Message;

        $body->setParts(array($html));
        $mail->setBody($body);

        $mail->addTo('tomasz.boch@gmail.com');

        $mail->setSubject('test subject');

        // GMAIL options
        $options = new SmtpOptions();
        $options
            ->setHost('smtp.mailgun.org')
            ->setName('smtp.mailgun.org')
            ->setConnectionClass('login')
            ->setConnectionConfig(array(
                'auth' => 'login',
                'username' => 'postmaster@click5dev7.com',
                'password' => '8uru08k1qpb7',
            ));



        $transport = new SmtpTransport();
        $transport->setOptions($options);
        $transport->send($mail);

        die;
    }

    public function importAction()
    {
        $identity = $this->getIdentity();

        if(!(in_array($identity['u_role_id'], array(\Admin\Model\User::ROLE_ADMIN, \Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT))
             || ($identity['u_role_id'] == \Admin\Model\User::ROLE_CLIENT && $identity['u_company_id_admin']))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $request  = $this->getRequest();
        $errorMsg = '';

        $importForm = new ImportForm($this->getServiceLocator(), $request->getPost());

        if ($request->isPost()) {

            $remediationplan = new Remediationplan();

            $importForm->prepare();
            $importForm->setInputFilter($remediationplan->getInputFilter($this->getServiceLocator()));
            $importForm->setData($request->getPost());

            if ($importForm->isValid()) {

                $post = $request->getPost();

                $post['rp_remediation_date'] = \DateTime::createFromFormat('m/d/Y', $post['rp_remediation_date'])->format('Y-m-d');

                $csvFile = $request->getFiles('files');

                if($csvFile && $csvFile[0]['tmp_name']) {

                    if (($handle = fopen($csvFile[0]['tmp_name'], "r")) !== FALSE) {
                        $startRead = false;
                        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                            if($startRead && $data[0] && array_search(trim($data[0]), Remediationplanaction::$levelsNames) !== false) {
                                $rpa = new Remediationplanaction();
                                $rpa->exchangeArray(
                                                    array( 'rpa_risk_level'  => array_search(trim($data[0]), Remediationplanaction::$levelsNames),
                                                           'rpa_threat'      => trim($data[1]),
                                                           'rpa_status'      => array_search(trim($data[4]), Remediationplanaction::$statusesNames),
                                                           'rpa_target_date' => trim($data[6]),
                                                        )
                                                    );
                                $remediationPlanActions[] = $rpa;
                            }
                            if(isset($data[0]) && $data[0] == 'Risk level') {
                                $startRead = true;
                            }
                        }
                        fclose($handle);
                    }

                    $data['rp_c_id']             = $post['rp_c_id'];
                    $data['rp_performed_u_id']   = $post['rp_performed_u_id'];
                    $data['rp_remediation_date'] = $post['rp_remediation_date'];
                    $data['actions']             = $remediationPlanActions;

                    $rpId = $this->getRemediationplanTable()->importRemediationplan($data);

                    if($rpId) {
                        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'edit', 'id' => $rpId));
                    }
                } else {
                    $errorMsg = 'Please, load correct csv file';
                }
            }
        }

        $view = new ViewModel(array(
            'errorMsg' => $errorMsg,
            'form' => $importForm,
        ));

        return $view;
    }


    public function getCompanyUsersAction()
    {
        $cId = $this->params('id');

        $users = $this->getUserTable()->getUsersByCompany($cId);
        
        return new JsonModel($users);

    }
}
