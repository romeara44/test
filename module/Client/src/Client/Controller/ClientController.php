<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Client\Form\ClientForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class ClientController extends AbstractActionController
{
    protected $companyTable;
    protected $userTable;
    protected $addressTable;
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
        } else if($identity['u_role_id'] == 5 && !$identity['u_company_id_admin']) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
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

    public function getCompanyTable()
    {
        if (!$this->companyTable) {
            $sm = $this->getServiceLocator();
            $this->companyTable = $sm->get('Client\Model\CompanyTable');
        }
        return $this->companyTable;
    }

    public function getAddressTable()
    {
        if (!$this->addressTable) {
            $sm = $this->getServiceLocator();
            $this->addressTable = $sm->get('Client\Model\AddressTable');
        }
        return $this->addressTable;
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
            'id' => '_id',
            'typeItem' => 'u_id',
            'name' => '_name',
            'company' => 'c_name'
        );

        $mappingTypeItem = array(
            0 => 'all',
            1 => 'companies',
            2 => 'contacts'
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'c_id';
        $paginator = $this->getCompanyTable()->getCompanies(true, $sortCol, $order,  $mappingTypeItem[$roleFilter], $this->getIdentity());
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter,
            'checkClientLimitCompany' => $this->getCompanyTable()->checkClientLimitCompany()
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open client list page');

        return $view;
    }

    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
        $noteform = $request->isPost() && (int) $request->getPost('noteform');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $id);

        $identity = $this->getIdentity();

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $form = new ClientForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());

        $userObj = null;
        $clientObj = null;
        $primaryAddressObj = null;
        $notes = null;
        $clienLimit = true;
        $clientLimitMsg = '';
        $setTrainingManager = true;
        $setTrainingManagerMsg = '';

        if ((int) $id) {
            $userObj = $this->getUserTable()->getUser($id);
            if($userObj->u_first_login == 1) {
                $this->getUserTable()->unSetFirstLogin($id);
            }
            $clientObj = $this->getCompanyTable()->getCompany($userObj->u_company_id);
            $primaryAddressObj = $this->getAddressTable()->getAddress($clientObj->c_primary_adr_id);
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_CONTACT);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            if (!$noteform) {
                $user = new User();
                $uId = is_object($userObj) ? $userObj->u_id : 0;

                $form->setInputFilter($user->getClientInputFilter($this->getServiceLocator(), $id, $uId));
                $form->setData($request->getPost());

                $post = $request->getPost();
                if($form->isValid()) {
                    if(!$id && $post['u_company_id'] && !$this->getUserTable()->checkCompanyLimitClient($post['u_company_id'])) {
                        $clientLimitMsg = 'You cannot create more than ' . $this->getServiceLocator()->get('Client\Model\CompanyTable')->getUsersLimit($post['u_company_id']) . ' user for company.';
                        $clienLimit = false;
                    }

                    $iisTrainingManager = isset($post['is_training_manager']) ? 1 : 0;
                    $curTrainingManager = $this->getCompanyTable()->getTrainingManager($post['u_company_id']);

                    if($iisTrainingManager && $post['u_company_id'] && $curTrainingManager) {
                        $setTrainingManagerMsg = 'Company already has training manager!';
                        $setTrainingManager = false;
                    }

                    if ($clienLimit && $setTrainingManager) {
                        if(!$post['u_role_id']) {
                            $post['u_role_id'] = $identity['u_role_id'] == \Admin\Model\User::ROLE_PARTIAL ? \Admin\Model\User::ROLE_PARTIAL : \Admin\Model\User::ROLE_CLIENT;
                        }
                        $post['u_senior_consultant_u_id'] = $identity['u_id'];
                        $user->exchangeArray($post);
                        $user->u_sent_password = 0;
                        $uId = $this->getUserTable()->saveUser($user);

                        if ($id) {
                            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Update Client "' . $uId . '"');
                            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $id);
                        } else {
                            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Add new Client "' . $uId . '"');
                            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $uId);
                        }

                        $users = $this->getUserTable()->getContactsByCompanyId($post['u_company_id']);

                        $iisPrimaryContact = isset($post['is_primary_contact']) ? 1 : 0;
                        if ($iisPrimaryContact || (count($users) == 1)) {
                            $this->getCompanyTable()->setPrimaryContactId($post['u_company_id'], $uId);
                        } else {
                            $this->getCompanyTable()->unsetPrimaryContactId($post['u_company_id']);
                        }

                        if ($iisTrainingManager) {
                            $this->getCompanyTable()->setTrainingManager($post['u_company_id'], $uId);
                        } else if(!$iisTrainingManager) {
                            $this->getCompanyTable()->unsetTrainingManager($uId);
                        }
                        
                        if($request->getPost('save_send_email')) {
                            $password = sha1($user->u_email . time());
                            $password = substr($password, 0, 6);
                            $this->getServiceLocator()->get('Admin\Model\UserTable')->setNewPassword($uId, $password);

                            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'createuser', 'uId' => $uId, 'password' => $password));
                            $this->flashMessenger()->addSuccessMessage('Client saved and invitation has been sent');
                        } else {
                            $this->flashMessenger()->addSuccessMessage('Client saved');
                        }
                        
                        return $this->redirect()->toRoute('client', array('controller' => 'client', 'action' => 'list'));
                    } else {
                        foreach ($form->getMessages() as $messageId => $message) {
                            //echo "Validation failure '$messageId': $message\n";
                        }
                    }
                }
            } else {
                $note = new Note();
                $formNote->setInputFilter($note->getInputFilter($this->getServiceLocator(), $id));
                $formNote->setData($request->getPost());

                if ($formNote->isValid()) {
                    $post = $request->getPost();

                    $note->exchangeArray($request->getPost());
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                    $this->flashMessenger()->addSuccessMessage('Note saved');

                    return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
                } else {
                    if ((int) $id) {
                        $form->bind($userObj);
                    }
                }
            }
        } else {
            if ((int) $id) {
                $form->bind($userObj);
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open edit client "' . $id . '" page');
            } else {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open add new client page');
            }
        }

        return array(
            'form' => $form,
            'uId' => $id,
            'userObj' => $userObj,
            'username' => is_object($userObj) ? $userObj->u_firstname . ' ' . $userObj->u_lastname : '',
            'clientObj' => $clientObj,
            'primaryAddressObj' => $primaryAddressObj,
            'formNote' => $formNote,
            'notes' => $notes,
            'identity' => $identity,
            'cId' => (int) $this->params('company'),
            'clientLimitMsg' => $clientLimitMsg,
            'setTrainingManagerMsg' => $setTrainingManagerMsg,
            'checkClientLimitCompany' => $this->getCompanyTable()->checkClientLimitCompany(),
            'administrationAccess' => $this->getUserTable()->checkClientAdministrationAccess($userObj)
        );
    }

    public function sendinviteAction()
    {
        $id = (int) $this->params('id');

        $user = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($id);
        $password = sha1($user->u_email . time());
        $password = substr($password, 0, 6);
        $this->getServiceLocator()->get('Admin\Model\UserTable')->setNewPassword($id, $password);

        $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'createuser', 'uId' => $id, 'password' => $password));
        $this->flashMessenger()->addSuccessMessage('Invitation has been sent');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Send client invite "' . $id . '"');

        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

    public function resetpasswordAction()
    {
        $id = (int) $this->params('id');

        if ($id) {
            if($this->getUserTable()->resetPassword($id)) {
                $this->flashMessenger()->addSuccessMessage('Password reset succesfuly.');
            } else {
                $this->flashMessenger()->addErrorMessage('Password reset failure.');
            }
        }

        return $this->redirect()->toRoute('client', array('controller' => 'client', 'action' => 'edit', 'id' => $id));
    }

    public function lockClientAction()
    {
        $result = false;

        $id   = $this->params('id');
        $lock = $this->params('lock');

        if($id && $lock !== null) {
            $result = (bool)$this->getUserTable()->lockUser($id, $lock);
        }

        return new JsonModel(array($result));
    }

    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getUserTable()->deleteUser($id);
        $this->flashMessenger()->addSuccessMessage('User has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete client "' . $id . '"');

        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getUserTable()->unarchiveUser($id);
        $this->flashMessenger()->addSuccessMessage('User has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive client "' . $id . '"');

        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

}
