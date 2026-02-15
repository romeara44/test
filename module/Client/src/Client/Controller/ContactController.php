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

use Client\Form\ContactForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class ContactController extends AbstractActionController
{
    protected $companyTable;
    protected $userTable;
    protected $addressTable;
    protected $noteTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->searchRoleFilter = 'contact';
        $container = new Container('activity');
        $container->activity = time();
        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();
        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        $identity = $this->getIdentity();
        if (!in_array($identity['u_role_id'], array(1, 2, 3, 4, 5, 7, 8))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        } else if($identity['u_role_id'] == 5 && !$identity['u_company_id_admin']) {
            //return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
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
        $identity = $this->getIdentity();
        //$test = $identity['senior_consultant_c_id'];

        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'u_id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;
        $activeFilter = $this->params()->fromRoute('activeFilter') ? (int) $this->params()->fromRoute('activeFilter') : 1;

        if ($roleFilter == 1) {
            $this->layout()->searchRoleFilter = 'company';
        }

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

        $t = $this->getIdentity();

       // $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'c_id';
        $orderBy = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'u_id';
        //$paginator = $this->getCompanyTable()->getCompanies(true, $sortCol, $order,  $mappingTypeItem[$roleFilter], $this->getIdentity(), $activeFilter);
        //getCompaniesv2
        //$array[] = $location;
        //$paginator = $this->getCompanyTable()->getContactsv2(true, $sortCol, $order,  $mappingTypeItem[$roleFilter], $this->getIdentity(), $activeFilter);
        
        //$paginator = $this->getUserTable()->getFullContactsByCompanyId($identity['u_company_id']);
        $users1 = $this->getUserTable()->getFullContactsByCompanyId($identity['u_company_id'], $orderBy, $order, $activeFilter);
        // foreach ($paginator as $company){
        //     $w = "stuff";
        //     $locations = [];
        //     $response = $this->getAddressTable()->getAddressesWithoutType($company->c_id);
        //     foreach ($response as $loc){
        //         array_push($locations, $loc);
        //     }
        //     //$location[] = 
        //     //$paginator->currentItems->dataSource->currentData = $locations;
        //     $company->locations = $locations;
            
        // }

        //$paginator->setCurrentPageNumber(1);
        //$paginator->setItemCountPerPage(0);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            //'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter,
            'activeFilter' => $activeFilter,
            'checkClientLimitCompany' => $this->getCompanyTable()->checkClientLimitCompany(),
            'users' => $users1
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open client list page');

        return $view;
    }

    public function editAction()
    {
        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        
        $identity = $this->getIdentity();
        $request = $this->getRequest();
        $id = (int) $this->params('id');

        // if ( $request->isPost() && (int)$id == 0 ) {
        //     $purchasedLicensesTemp = $this->getCompanyTable()->checkPurchasedLicensesByCompany($identity['u_company_id']);
        //     $usedLicense = $this->getUserTable()->getUsedLicenseCount($identity['u_company_id']);
        //     if( (int)$purchasedLicensesTemp->c_users_limit <= (int)$usedLicense )
        //     {
        //         return;
        //     }
        // }
        $noteform = $request->isPost() && (int) $request->getPost('noteform');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_CLIENT, $id);

        $form = new ContactForm($this->getServiceLocator());
        $formNote = new NoteForm($this->getServiceLocator());

        $userObj = null;
        $clientObj = null;
        $primaryAddressObj = null;
        $notes = null;
        $clienLimit = true;
        $clientLimitMsg = '';
        $setTrainingManager = true;
        $setTrainingManagerMsg = '';
        $training_managers = array();
        $cId = (int) $this->params('company');
        $purchasedLicensesTemp = null;
        

        if ((int) $id) {
            $userObj = $this->getUserTable()->getUser($id);
            if($userObj->u_first_login == 1) {
                //$this->getUserTable()->unSetFirstLogin($id);
            }
            $clientObj = $this->getCompanyTable()->getCompany($userObj->u_company_id);
            $primaryAddressObj = $this->getAddressTable()->getAddress($clientObj->c_primary_adr_id);
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_CONTACT);
            $training_managers = $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->getTrainingManagersIdsForCompany($userObj->u_company_id);
            $purchasedLicensesTemp = $this->getCompanyTable()->checkPurchasedLicensesByCompany($userObj->u_company_id);
            $usedLicense = $this->getUserTable()->getUsedLicenseCount($userObj->u_company_id);

        } elseif ($cId) {
           $compObj = $this->getCompanyTable()->getCompany($cId);
           $primaryAddressObj = $this->getAddressTable()->getAddress($compObj->c_primary_adr_id);
           $purchasedLicensesTemp = $this->getCompanyTable()->checkPurchasedLicensesByCompany($cId);
           $usedLicense = $this->getUserTable()->getUsedLicenseCount($cId);
        } else {
            $purchasedLicensesTemp = $this->getCompanyTable()->checkPurchasedLicensesByCompany($identity['u_company_id']);
            $usedLicense = $this->getUserTable()->getUsedLicenseCount($identity['u_company_id']);
            
        }

        $purchasedLicense = $purchasedLicensesTemp->c_users_limit;
        $isCompanyAdmin = $this->DetermineCompanyAdmin($identity['u_company_id_admin'], $identity['u_company_id']);        

        $request = $this->getRequest();
        if ($request->isPost()) {
            // if ((int)$id == 0 ) {
            //     $purchasedLicensesTemp = $this->getCompanyTable()->checkPurchasedLicensesByCompany($identity['u_company_id']);
            //     $usedLicense = $this->getUserTable()->getUsedLicenseCount($identity['u_company_id']);
            //     if((int)$purchasedLicensesTemp->c_users_limit <= (int)$usedLicense){
            //         return array();
            //         $form->setData($prg);
            //         return array('form' => $form);
            //     }
            // }

            if (!$noteform) {
                $user = new User();
                $uId = is_object($userObj) ? $userObj->u_id : 0;
                $post = $request->getPost();
                
                $form->setInputFilter($user->getClientInputFilter($this->getServiceLocator(), $id, $uId, $post));
                $form->setData($post);
                
                if($form->isValid()) {
                    if(!$id && $post['u_company_id'] && !$this->getUserTable()->checkCompanyLimitClient($post['u_company_id'])) {
                        $clientLimitMsg = 'You cannot create more than ' . $this->getServiceLocator()->get('Client\Model\CompanyTable')->getUsersLimit($post['u_company_id']) . ' user for company.';
                        $clienLimit = false;
                    }

                    $iisTrainingManager = isset($post['is_training_manager']) ? 1 : 0;
                    
                    if (!$id || $userObj->u_company_id != $post['u_company_id']) {
                        $training_managers = $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->getTrainingManagersIdsForCompany($post['u_company_id']);
                    }                    

                    if($iisTrainingManager) {                        
                        if (count($training_managers) > 2 && !in_array($id, $training_managers)) {
                            $setTrainingManagerMsg = 'Company cannot has more than 3 training managers!';
                            $setTrainingManager = false;
                        }                        
                    }

                    if ($clienLimit && $setTrainingManager) {
                        if(!$post['u_role_id']) {
                            $post['u_role_id'] = $identity['u_role_id'] == \Admin\Model\User::ROLE_PARTIAL ? \Admin\Model\User::ROLE_PARTIAL : \Admin\Model\User::ROLE_CLIENT;
                        }

                        if(!$uId) {
                            $post['u_senior_consultant_u_id'] = $identity['u_id'];
                        }
                        $licensed_user = $post['licensed_user'];
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
                            if (!in_array($uId, $training_managers)) {
                                $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->addTrainingManagerForCompany($post['u_company_id'], $uId);
                            }
                        } else {
                            if (in_array($uId, $training_managers)) {
                                $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->deleteTrainingManagerForCompany($post['u_company_id'], $uId);
                            }
                        }
                        
                        if($request->getPost('save_send_email')) {
                            $password = $this->getServiceLocator()->get('Admin\Model\UserTable')->generatePassword();
                            $this->getServiceLocator()->get('Admin\Model\UserTable')->setNewPassword($uId, $password);

                            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'createuser', 'uId' => $uId, 'password' => htmlspecialchars($password)));
                            $this->flashMessenger()->addSuccessMessage('Client saved and invitation has been sent');
                        } else {
                            $this->flashMessenger()->addSuccessMessage('Client saved');
                        }
                        
                        return $this->redirect()->toRoute('contact', array('controller' => 'contact', 'action' => 'list'));
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

                    return $this->redirect()->toRoute('contact', array('controller' => 'contact', 'action' => 'list'));
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
            'cId' => $cId,
            'clientLimitMsg' => $clientLimitMsg,
            'setTrainingManagerMsg' => $setTrainingManagerMsg,
            'checkClientLimitCompany' => $this->getCompanyTable()->checkClientLimitCompany(),
            'administrationAccess' => $this->getUserTable()->checkClientAdministrationAccess($userObj),
            'training_managers' => $training_managers,
            'purchasedLicense' => $purchasedLicense,
            'usedLicense' => $usedLicense,
            'isCompanyAdmin' => isset($isCompanyAdmin) ? $isCompanyAdmin : false
        );
    }

    public function saveItemStatusAction() {
        $value = (int)$this->getRequest()->getPost('value');
        //$files = $this->getFiles();

        $limit = $this->getUserTable()->getUsersLimit($value);

        // if (!empty($value)){
        //     $pieces = explode("|", $value);
            
        //     $id = substr($pieces[0], strpos($pieces[0], '[') + 1, strlen($pieces[0]) - 8);

        //     $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->saveAuditRecordItemStatus($id, $pieces[1]);
        // }

        return $limit;
    }

    public function sendinviteAction()
    {
        $id = (int) $this->params('id');

        $user = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($id);
        $password = $this->getServiceLocator()->get('Admin\Model\UserTable')->generatePassword();
        $this->getServiceLocator()->get('Admin\Model\UserTable')->setNewPassword($id, $password);

        $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'createuser', 'uId' => $id, 'password' => htmlspecialchars($password)));
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

        //return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
        return $this->redirect()->toRoute('contact', array('controller' => 'contact', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getUserTable()->unarchiveUser($id);
        $this->flashMessenger()->addSuccessMessage('User has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive client "' . $id . '"');

        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

    public function DetermineCompanyAdmin($u_company_id_admin, $u_company_id)
    {
        $isCompanyAdmin = false;
        if ((isset($u_company_id_admin)) && (isset($u_company_id)) && $u_company_id_admin == $u_company_id)
        {
            $isCompanyAdmin = true;
        }
        return $isCompanyAdmin;
    }

}
