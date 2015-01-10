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

use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;


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

        return $view;
    }

    public function editAction()
    {
        $id = (int) $this->params('id');
        $type = $this->params('type');

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
                    return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
                }
                if ($post['signedoff'] != 1) {
                    $id = $this->getRemediationplanTable()->clonePlan($id, $post);
                }

                $this->getRemediationplanTable()->setFieldValue($id, 'rp_initials', $post['rp_initials']);
                $this->getRemediationplanTable()->setFieldValue($id, 'rp_initials_approver', $post['rp_initials_approver']);

                $this->getRemediationplanTable()->setFieldValue($id, 'rp_performed_u_id', $post['rp_performed_u_id']);
                $this->getRemediationplanTable()->setFieldValue($id, 'rp_approver_u_id', $post['rp_approver_u_id']);

                $ymd1 = \DateTime::createFromFormat('m/d/Y', $post['rp_incident_date']);
                if (is_object($ymd1)) {
                    if ($ymd1->format('Y') > date("Y")) {
                        $ymd1->setDate('2014', $ymd1->format('m'), $ymd1->format('d'));
                    }
                    $ymd1 = $ymd1->format('Y-m-d');
                } else {
                    $ymd1 = '';
                }
                $this->getRemediationplanTable()->setFieldValue($id, 'rp_incident_date', $ymd1);

                $ymd2 = \DateTime::createFromFormat('m/d/Y', $post['rp_remediation_date']);
                if (is_object($ymd2)) {
                    if ($ymd2->format('Y') > date("Y")) {
                        $ymd2->setDate('2014', $ymd2->format('m'), $ymd2->format('d'));
                    }
                    $ymd2 = $ymd2->format('Y-m-d');
                } else {
                    $ymd2 = '';
                }
                $this->getRemediationplanTable()->setFieldValue($id, 'rp_remediation_date', $ymd2);

                if ($post['signedoff'] == 1) {
                    $this->getRemediationplanTable()->setStatus($id, \Assessment\Model\Remediationplan::STATUS_SIGNED_OFF);
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_SIGNEDOFF, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);
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

                return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
            }
        }

        $rpObj = $this->getRemediationplanTable()->getRemediationplan($id);
        $rpObj->_client_name = stripslashes($rpObj->_client_name);
        $actions = $this->getRemediationplanactionTable()->getRemediationplanactions($id);

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

        /*foreach ($userTable->getContactsByCompanyId($rpObj->rp_c_id, false) as $key => $r) {
            $contacts[$key] = $r;
        }
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
           $contacts[$key] = $r;
        }*/

        /*
        foreach ($userTable->getContactsByCompanyId($rpObj->rp_c_id, false) as $key => $r) {
            $contactsApr[$key] = $r;
        }
        foreach ($userTable->getUsersByRole(array(\Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) as $key => $r) {
            $contactsApr[$key] = $r;
        }
        */

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
            'contactsApr' => $contactsApr
        ));

        if ($type == 'pdf') {
            $domLibPath =  $_SERVER['DOCUMENT_ROOT'] . '/../vendor';

            $domLibPath = $domLibPath . "/dompdf/dompdf_config.inc.php";
            require_once $domLibPath;

            $renderer = new PhpRenderer();

            $map = new Resolver\TemplateMapResolver(array(
                'remediationplan/pdftemplate' => $_SERVER['DOCUMENT_ROOT'] . '/../module/Assessment/view/assessment/remediationplan/edit.phtml',
            ));

            $renderer->setResolver($map);

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
            $model->setTemplate('remediationplan/pdftemplate');

            $html = $renderer->render($model);

            //$html = utf8_encode($html);
            $html = str_replace('§', '&#167;', $html);

            set_time_limit(300);
            ini_set('memory_limit', '-1');

            $dompdf = new \DOMPDF();
            $dompdf->set_paper('a4', 'landscape');
            $dompdf->load_html($html);
            //$dompdf->set_paper( 'letter' , 'portrait' );
            $dompdf->render();
            $dompdf->stream('remediationplan_ ' . date('Y_m_d_h_i_s', time()) . '.pdf');

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

        return $this->redirect()->toRoute('remediationplan', array('controller' => 'remediationplan', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getRemediationplanTable()->unarchiveRemediationplan($id);
        $this->flashMessenger()->addSuccessMessage('Remediation plan has been unarchived');

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
                $post['rpa_rp_id'] = $rpId;
                $rpa->exchangeArray($post);
                $this->getRemediationplanactionTable()->setServiceLocator($this->getServiceLocator());
                $rpaId = $this->getRemediationplanactionTable()->saveRemediationplanaction($rpa, 1);

                // save files
                $note = new Note();
                $noteData['note_text'] = $post['note_text'];
                $noteData['note_item_type'] = \Note\Model\Note::NOTE_RPA;
                $noteData['note_item_id'] = $rpaId;
                $note->exchangeArray($noteData);
                $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                /////////////////
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

        return $viewModel;
    }

    public function remediationplanemailAction()
    {
        $id = $this->params('id');
        $contactUId = $this->params('rpa_contact_u_id');
        $contactUId = $this->params('rpa_contact_u_id');
        $addAttachments = $this->params('add_atts');
        
        $rpaObj = $this->getRemediationplanactionTable()->getRemediationplanaction($id);
        $rpObj = $this->getRemediationplanTable()->getRemediationplan($rpaObj->rpa_rp_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpObj->rp_consultant_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('remediationplan');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $rpObj->_performed_name, $text);
        $text = str_replace('<Target Date>',  $rpaObj->rpa_target_date, $text);

        $subject = str_replace('<Company>', $rpObj->_client_name, $mt->mt_subject);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>',  str_replace('Â', '', $rpaObj->rpa_threat), $text);
        $text = str_replace('<Action plan>',  $rpaObj->rpa_action_plan, $text);
        $text = str_replace('<Policy>',  $rpaObj->rpa_policy, $text);

        $assignee = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpaObj->rpa_contact_u_id);
        
        $notes = null;
        $files = array();
        if ($id && $addAttachments) {
            $notesDef = $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
            if (is_object($notes) && ($notes->count())) {
                $docRoot = $_SERVER['DOCUMENT_ROOT'];
                foreach($notes as $note) {
                    if ($note->_files != '') {
                        $_files = explode(',', $note->_files);
                        if($_files) {
                            foreach ($_files as $_file) {
                                list($fName, $fId) = explode('::', $_file);
                                $filepath = $docRoot . '/data/notefiles/' . $note->note_id . '/' . $fId;
                                if (!file_exists($filepath)) {
                                    $note_id = $this->getNotefilesTable()->getFileNoteByFId($fId);
                                    $filepath = $docRoot . '/data/notefiles/' . $note_id . '/' . $fId;
                                }
                                $files[$fId]['file_name'] = $fName;
                                $files[$fId]['file_path'] = $filepath;
                            }
                        }
                    }
                }
                $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
            }
        }
        
        $viewModel = new ViewModel(array(
            'title' => $mt->mt_name,
            'text' => $text,
            'header_title' => $rpaObj->_contact_name,
            'title_label' => 'Assignee',
            'subject' => $subject,
            'addTo' => isset($assignee->u_email)&& ($assignee->u_email != '') ? $assignee->u_email : '',
            'addAttachments' => $addAttachments,
            'notes' => $notes,
            'files' => $files
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function remediationplanapproveremailAction()
    {
        $id = $this->params('id');
        $rpaObj = $this->getRemediationplanactionTable()->getRemediationplanaction($id);
        $rpObj = $this->getRemediationplanTable()->getRemediationplan($rpaObj->rpa_rp_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpObj->rp_consultant_u_id);
        $addAttachments = $this->params('add_atts');

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('emailapprover');

        $text = $mt->mt_text;
        $text = str_replace('<Client Name>',  $rpObj->_client_name, $text);
        $text = str_replace('<Target Date>',  $rpaObj->rpa_target_date, $text);

        $subject = str_replace('<Company>', $rpObj->_client_name, $mt->mt_subject);

        $text = str_replace('<Consultant Name>',  $contact->u_firstname . ' ' . $contact->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $contact->u_title, $text);
        $text = str_replace('<Consultant phone>',  $contact->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $contact->u_email, $text);

        $text = str_replace('<Task>', str_replace('Â', '', $rpaObj->rpa_threat), $text);
        $text = str_replace('<Action plan>', $rpaObj->rpa_action_plan, $text);

        $approver = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($rpaObj->rpa_approver_u_id);
        
        $notes = null;
        $files = array();
        if ($id && $addAttachments) {
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
             if (is_object($notes) && ($notes->count())) {
                $docRoot = $_SERVER['DOCUMENT_ROOT'];
                foreach($notes as $note) {
                    if ($note->_files != '') {
                        $_files = explode(',', $note->_files);
                        if($_files) {
                            foreach ($_files as $_file) {
                                list($fName, $fId) = explode('::', $_file);
                                $filepath = $docRoot . '/data/notefiles/' . $note->note_id . '/' . $fId;
                                if (!file_exists($filepath)) {
                                    $note_id = $this->getNotefilesTable()->getFileNoteByFId($fId);
                                    $filepath = $docRoot . '/data/notefiles/' . $note_id . '/' . $fId;
                                }
                                $files[$fId]['file_name'] = $fName;
                                $files[$fId]['file_path'] = $filepath;
                            }
                        }
                    }
                }
                $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_RPA);
            }
        }
        
        $viewModel = new ViewModel(array(
            'title' => $mt->mt_name,
            'text' => $text,
            'header_title' => $rpaObj->_contact_name,
            'title_label' => 'Assignee',
            'subject' => $subject,
            'addTo' => isset($approver->u_email)&& ($approver->u_email != '') ? $approver->u_email : '',
            'addAttachments' => $addAttachments,
            'notes' => $notes,
            'files' => $files
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

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

        return 1;
    }

    public function sendemailtestAction()
    {
        $mail = new Mail\Message();
        $mail->setFrom('postmaster@click5dev7.com', 'HIPAA Compliance');


        $html = new \Zend\Mime\Part('test');
        $html->type = 'text/html';
        $body = new \Zend\Mime\Message;

        $body->setParts(array($html));
        $mail->setBody($body);

        $mail->addTo('tomasz.boch@gmail.com');

        $mail->setSubject('test subject');

        /*$options = new SmtpOptions();
        $options
            ->setHost('ip-173-201-177-160.ip.secureserver.net')
            ->setConnectionClass('login')
            ->setName('ip-173-201-177-160.ip.secureserver.net')
            ->setConnectionConfig(array(
                'auth' => 'login',
                'username' => 'hipaa@hipaa.carosh.com',
                'password' => 'y5c97tYv8pQm',
                'ssl' => 'tls',
                'port' => 465
            ));*/

        // GMAIL options
        /*$options = new SmtpOptions();
        $options
            ->setHost('smtp.gmail.com')
            ->setName('smtp.gmail.com')
            ->setConnectionClass('login')
            ->setConnectionConfig(array(
                'auth' => 'login',
                'username' => 'hipaacarosh@gmail.com',
                'password' => 'qv#cgAwev',
                'ssl' => 'tls',
                'port' => 465
            ));*/
        // GMAIL options
        /*$options = new SmtpOptions();
        $options
            ->setHost('smtp.mailgun.org')
            ->setName('smtp.mailgun.org')
            ->setConnectionClass('login')
            ->setConnectionConfig(array(
                'auth' => 'login',
                'username' => 'postmaster@click5dev7.com',
                'password' => '8uru08k1qpb7',
            ));*/

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


}
