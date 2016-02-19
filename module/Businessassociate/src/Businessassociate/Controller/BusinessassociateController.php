<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Businessassociate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Businessassociate\Form\BusinessassociateForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Businessassociate\Model\Businessassociate;
use Businessassociate\Model\Businessassociateuser;
use Businessassociate\Model\Businessassociateanswer;
use Note\Model\Note;
use Mail\Model\Mailtemplate;
use Zend\Session\Container;

class BusinessassociateController extends AbstractActionController
{
    protected $businessassociateTable;
    protected $businessassociateanswerTable;
    protected $userTable;
    protected $noteTable;
    protected $mailtemplateTable;
    protected $companyRolesTable;

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

    public function getBusinessassociateTable()
    {
        if (!$this->businessassociateTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateTable = $sm->get('Businessassociate\Model\BusinessassociateTable');
        }
        return $this->businessassociateTable;
    }

    public function getBusinessassociateanswerTable()
    {
        if (!$this->businessassociateanswerTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateanswerTable = $sm->get('Businessassociate\Model\BusinessassociateanswerTable');
        }
        return $this->businessassociateanswerTable;
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

    public function getMailtemplateTable()
    {
        if (!$this->mailtemplateTable) {
            $sm = $this->getServiceLocator();
            $this->mailtemplateTable = $sm->get('Mail\Model\MailtemplateTable');
        }
        return $this->mailtemplateTable;
    }

    public function getCompanyRolesTable()
    {
        if (!$this->companyRolesTable) {
            $sm = $this->getServiceLocator();
            $this->companyRolesTable = $sm->get('Client\Model\CompanyRolesTable');
        }
        return $this->companyRolesTable;
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
            'id' => 'ba_id',
            'name' => 'ba_name',
            'client' => 'ba_c_id'
        );


        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'ba_id';
        $paginator = $this->getBusinessassociateTable()->getBusinessassociates(true, $sortCol, $order, $this->getIdentity());
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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open business associate list page');
        
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

        $form = new BusinessassociateForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());

        $baObj = null;
        $userObj = null;
        $notes = null;
        $companyRolesMsg = '';

        if ((int) $id) {
            $baObj = $this->getBusinessassociateTable()->getBusinessassociate($id);
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_BUSINESSASSOCIATE);
        }

        $checkIfUserAnswered = false;
        $answers = array();
        $identity = $this->getIdentity();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($noteform) {

                $note = new Note();
                $formNote->setInputFilter($note->getInputFilter($this->getServiceLocator(), $id));
                $formNote->setData($request->getPost());

                if ($formNote->isValid()) {
                    $post = $request->getPost();
                    $note->exchangeArray($request->getPost());
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                    $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociatereportTable')->saveReports($id, $request->getFiles());

                    $this->flashMessenger()->addSuccessMessage('Note saved');

                    return $this->redirect()->toRoute('businessassociate', array('controller' => 'company', 'action' => 'list'));
                } else {
                    if ((int) $id) {
                        $form->bind($baObj);
                    }
                }               
                
            } else {
                $ba = new Businessassociate();
                $post = $request->getPost();
                $uId = is_object($baObj) ? $baObj->ba_contact_u_id : 0;

                $form->setInputFilter($ba->getInputFilter($this->getServiceLocator(), $id, $uId));
                $form->setData($request->getPost());

                if(!$checkFillCompanyRoles = $this->getCompanyRolesTable()->checkFillCompanyRoles($post['ba_c_id'])) {
                    $companyRolesMsg = 'Please, fill all roles for this company';
                }

                if ($form->isValid() && $checkFillCompanyRoles) {

                    $post['ba_consultant_u_id'] = $identity['u_id'];

                    $ba->exchangeArray($post);
                    $this->getBusinessassociateTable()->setServiceLocator($this->getServiceLocator());
                    $baId = $this->getBusinessassociateTable()->saveBusinessassociate($ba);

                    // save contact person
                    $user = new User();
                    $post['u_role_id'] = User::ROLE_BUSINESS_ASSOCIATE;
                    $user->exchangeArray($post);

                    $this->getUserTable()->setServiceLocator($this->getServiceLocator());
                    $user->u_sent_password = 0;

                    $uId = $this->getUserTable()->saveUser($user);
                    $this->getBusinessassociateTable()->setContactId($baId, $uId);

                    $this->flashMessenger()->addSuccessMessage('Business associate saved');

                    if ((int) $id) {
                        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update business associate edit page "' . $baId . '"');
                    } else {
                        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new business associate edit page "' . $baId . '"');
                    }

                    return $this->redirect()->toRoute('businessassociate', array('controller' => 'businessassociate', 'action' => 'list'));
                } else {
                    foreach ($form->getMessages() as $messageId => $message) {
                       // echo "Validation failure '$messageId': $message<br/>";
                    }

                    if ((int) $id) {
                        $form->bind($baObj);
                        //$addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::COMPANY_TYPE);
                    }
                }
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_BA, $id);

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open business associate edit page "' . $id . '"');

            if ((int) $id) {
                if ($baObj->ba_contact_u_id) {
                    $userObj = $this->getUserTable()->getUser($baObj->ba_contact_u_id);
                    $form->get('u_id')->setValue($baObj->ba_contact_u_id);

                    $form->get('u_firstname')->setValue($userObj->u_firstname);
                    $form->get('u_lastname')->setValue($userObj->u_lastname);
                    $form->get('u_title')->setValue($userObj->u_title);
                    $form->get('u_office_phone')->setValue($userObj->u_office_phone);
                    $form->get('u_office_phone_inner')->setValue($userObj->u_office_phone_inner);
                    $form->get('u_direct_phone')->setValue($userObj->u_direct_phone);
                    $form->get('u_direct_phone_inner')->setValue($userObj->u_direct_phone_inner);
                    $form->get('u_cell_phone')->setValue($userObj->u_cell_phone);
                    $form->get('u_other_phone')->setValue($userObj->u_other_phone);
                    $form->get('u_other_phone_inner')->setValue($userObj->u_other_phone_inner);
                    $form->get('u_fax')->setValue($userObj->u_fax);
                    $form->get('u_email')->setValue($userObj->u_email);

                    $checkIfUserAnswered = (int) $this->getBusinessassociateanswerTable()->checkIfAnswersExists($id);
                    if ($checkIfUserAnswered) {
                        $answers = $this->getBusinessassociateanswerTable()->getBaAnswers($id);
                    }

                }
                $form->bind($baObj);
            }
        }

        $questions = $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociatequestionTable')->getBusinessassociatequestions();

        $reportFiles = $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociatereportTable')->getReportsFiles($id);

        return array(
            'form' => $form,
            'formNote' => $formNote,
            'baId' => $id,
            'notes' => $notes,
            'answers' => $answers,
            'invitedStatus' => isset($baObj->ba_assessment_invited_status) ? $baObj->ba_assessment_invited_status : 0,
            'invitedDate' => isset($baObj->ba_assessment_invited_date) ? $baObj->ba_assessment_invited_date : 0,
            'questions' => $questions,
            'signoff' => isset($baObj->ba_status) && ($baObj->ba_status == 1) ? 1 : 0,
            'signoffDate' => isset($baObj->ba_sign_off_date) ? $baObj->ba_sign_off_date : '',
            'checkIfUserAnswered' => $checkIfUserAnswered,
            'companyRolesMsg' => $companyRolesMsg,
            'reportFiles' => $reportFiles,
        );
    }

    public function getschedulecallAction()
    {
        $id = $this->params('id');
        $ba = $this->getBusinessassociateTable()->getBusinessassociate($id);
        $consultant = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($ba->ba_consultant_u_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($ba->ba_contact_u_id);

        $addTo = $contact->u_email;

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('schedulecall');
        $text = $mt->mt_text;

        $subject = $mt->mt_subject;
        $text = str_replace('<Business Associate>',  $ba->ba_name, $mt->mt_text);

        if ($ba->ba_c_id) {
            $companyTable = $this->getServiceLocator()->get('Client\Model\CompanyTable');
            $company = $companyTable->getCompany($ba->ba_c_id);
            $text = str_replace('<Company>',  $company->c_name, $text);
            $subject = str_replace('<Company>', $company->c_name, $mt->mt_subject);
        }

        $text = str_replace('<Consultant Name>',  $consultant->u_firstname . ' ' . $consultant->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $consultant->u_title, $text);
        $text = str_replace('<Consultant phone>',  $consultant->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $consultant->u_email, $text);

        $viewModel = new ViewModel(array(
            'text' => $text,
            'title' => $mt->mt_name,
            'header_title' => $ba->ba_name,
            'title_label' => 'Name',
            'addTo' => $addTo,
            'subject' => $subject,
            'company' => isset($company->c_name) ? $company->c_name : ''
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $baId = $this->getBusinessassociateTable()->setCallDate($id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Get shedule call for business associate "' . $id . '"');

        return $viewModel;
    }

    public function invitetoassessmentAction()
    {
        $id = $this->params('id');
        $ba = $this->getBusinessassociateTable()->getBusinessassociate($id);
        $consultant = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($ba->ba_consultant_u_id);
        $contact = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($ba->ba_contact_u_id);

        $mt = $this->getMailtemplateTable()->getMailtemplateByKey('invitetoassessment');
        $text = $mt->mt_text;

        $identity = $this->getIdentity();

        $subject = $mt->mt_subject;
        $text = str_replace('<Business Associate>',  $ba->ba_name, $text);

        if ($ba->ba_c_id) {
            $companyTable = $this->getServiceLocator()->get('Client\Model\CompanyTable');
            $company = $companyTable->getCompany($ba->ba_c_id);
            $text = str_replace('<Company>',  $company->c_name, $text);
            $subject = str_replace('<Company>', $company->c_name, $mt->mt_subject);
        }
        $text = str_replace('<username>',  $contact->u_email, $text);
        $text = str_replace('<password>',  '********', $text);

        $text = str_replace('<Consultant Name>',  $consultant->u_firstname . ' ' . $consultant->u_firstname, $text);
        $text = str_replace('<Consultant Title>',  $consultant->u_title, $text);
        $text = str_replace('<Consultant phone>',  $consultant->u_office_phone, $text);
        $text = str_replace('<Consultant email>',  $consultant->u_email, $text);

        $viewModel = new ViewModel(array(
            'text' => $text,
            'title' => $mt->mt_name,
            'addTo' => $contact->u_email,
            'header_title' => $ba->ba_name,
            'title_label' => 'Name',
            'subject' => $subject,
            'passwordToSent' => 1,
            'passwordUId' => $ba->ba_contact_u_id,
            'company' => isset($company->c_name) ? $company->c_name : ''
        ));

        $viewModel->setTemplate('businessassociate/businessassociate/modaltemplate.phtml');

        $viewModel->setTerminal(true);

        $baId = $this->getBusinessassociateTable()->setInviteDate($id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send invite email for business associate edit page "' . $id . '"');

        return $viewModel;
    }

    public function questionsformAction()
    {
        $questions = $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociatequestionTable')->getBusinessassociatequestions();

        $request = $this->getRequest();

        $identity = $this->getIdentity();

        $viewModel = new ViewModel();

        if ($request->isPost()) {
            $post = $request->getPost();

            $files = $request->getFiles();

            if (isset($post['questions'])) {
                $this->getBusinessassociateanswerTable()->deleteBusinessassociateanswers($post['baId']);
                foreach ($post['questions'] as $questionId => $question) {
                    $notesFiles = isset($files['notesFiles'][$questionId]) ? $files['notesFiles'][$questionId] : array();

                    $baa = new Businessassociateanswer();
                    $answer['baa_baq_id'] = $questionId;
                    $answer['baa_value'] = $question;
                    $answer['baa_ba_id'] = $post['baId'];
                    $baa->exchangeArray($answer);
                    $this->getBusinessassociateanswerTable()->setServiceLocator($this->getServiceLocator());
                    $baaId = $this->getBusinessassociateanswerTable()->saveBusinessassociateanswers($baa);

                    // save note to answer
                    $note = new Note();

                    $postNote['note_text'] = $post['notes'][$questionId];
                    $postNote['note_item_type'] = \Note\Model\Note::NOTE_BUSINESSASSOCIATE_ANSWER;
                    $postNote['note_item_id'] = $baaId;
                    $note->exchangeArray($postNote);

                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, array('notesFiles' => $notesFiles));
                }
            }

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Question page for business associate "' . $post['baId'] . '"');
        }


        return $this->redirect()->toRoute('businessassociate', array('controller' => 'businessassociate', 'action' => 'edit', 'id' => $post['baId']));
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getBusinessassociateTable()->deleteBusinessassociate($id);
        $this->flashMessenger()->addSuccessMessage('Ba has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete business associate "' . $id . '"');

        return $this->redirect()->toRoute('businessassociate', array('controller' => 'businessassociate', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getBusinessassociateTable()->unarchiveBusinessassociate($id);
        $this->flashMessenger()->addSuccessMessage('Ba has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive business associate "' . $id . '"');

        return $this->redirect()->toRoute('businessassociate', array('controller' => 'businessassociate', 'action' => 'list'));
    }


    public function signoffAction()
    {
        $id = $this->params('id');

        $this->getBusinessassociateTable()->signoffBusinessassociate($id);
        $this->flashMessenger()->addSuccessMessage('Ba has been closed');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Sign off business associate "' . $id . '"');

        return $this->redirect()->toRoute('businessassociate', array('controller' => 'businessassociate', 'action' => 'list'));
    }

    public function importAction() {
        $non_imported_assessments_ids = [];
        $assessments = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForImport();
        foreach ($assessments as $assessment) {
            $flag = 1;
            $ass_addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($assessment->a_id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
            foreach ($ass_addresses as $key => $ass_address) {
                $notes = $this->getNoteTable()->getNotes($assessment->a_id, \Note\Model\Note::NOTE_ABAL, $ass_address->adr_id);
                $abals = $this->getServiceLocator()->get('Assessment\Model\AssessmentBusinessAssociateLocationTable')->getAbalsByLocation($assessment->a_id, $ass_address->adr_id);
                $reportFiles = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getReportsFiles($assessment->a_id, $ass_address->adr_id, 5);
                foreach ($abals as $abal) {
                    if (!$abal->abal_ba_id) continue;
                    if (!$this->getBusinessassociateTable()->getBusinessassociate($abal->abal_ba_id)) continue;
                    foreach ($notes as $note) {
                        $note_dest = new Note;
                        $note_dest->note_item_type = \Note\Model\Note::NOTE_BUSINESSASSOCIATE;
                        $note_dest->note_item_id = $abal->abal_ba_id;
                        if (!$this->getNoteTable()->copyNote($note, $note_dest)) {
                            $flag = 0;
                        }
                    } 
                    foreach ($reportFiles as $report) {
                        if (!$this->getServiceLocator()->get('Businessassociate\Model\BusinessassociatereportTable')->createReportFromAssessmentInventoryLocationReport($abal->abal_ba_id, $report)) {
                            $flag = 0;
                        }
                    }                   
                }
            }
            if (!$flag) {
                $non_imported_assessments_ids[] = $assessment->a_id;
            }
        }
        var_dump($non_imported_assessments_ids);die();
    }
}
