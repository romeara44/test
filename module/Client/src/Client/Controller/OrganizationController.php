<?php

namespace Client\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Request;

use Client\Form\ClientForm;
use Note\Form\NoteForm;
use Admin\Model\User;
use Note\Model\Note;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

//use Client\Form\CompanyForm;
use Client\Form\OrganizationForm;
use Client\Form\RenewalForm;
use Client\Model\Company;
use Application\Model\LogsTable;
use Client\Model\CompanyRoles;
use Client\Model\AddressTable;
//use Client\Service\IdentityServiceInterface;
//use SanAuth\Service\IdentityServiceInterface;
//use Client\Service\InitializeClientTableServiceInterface;


class OrganizationController extends AbstractActionController
{
    protected $companyTable;
    protected $userTable;
    protected $addressTable;
    protected $noteTable;
    //protected $identityService;
    //protected $identity;
    //protected $hasIdentity;
    //protected $initializeClientTableService;
    //protected $companyTableReference;
    

    //public function __construct(IdentityServiceInterface $identityService)
    public function __construct()
    {
        // $this->identityService  = $identityService;
        // $this->identity         = $this->identityService->getIdentity();
        // $this->hasIdentity      = $this->identityService->hasIdentity();
        //$this->initializeClientTableService = $initializeClientTableService;
        

    }
    
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
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        //$this->companyTableReference = $this->initializeClientTableService->getCompanyTable($this->companyTable, $this);
        //$this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open Organization List page');

        return parent::onDispatch($e);
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

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getLogTable()
    {
        if (!isset($this->logTable)) {
            $sm = $this->getServiceLocator();
            $this->logTable = $sm->get('Application\Model\LogsTable');
        }
        return $this->logTable;
    }

    public function getNoteTable()
    {
        if (!$this->noteTable) {
            $sm = $this->getServiceLocator();
            $this->noteTable = $sm->get('Note\Model\NoteTable');
        }
        return $this->noteTable;
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
        //$identity = $this->getIdentity();
        //$test = $identity['senior_consultant_c_id'];
        //$companyTableReference = $this->identityService->getCompanyTable($this->companyTable, $this);
        //$companyTableReference = $this->companyTableReference->getCompanyTable($this->companyTable, $this);
        

        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'c_id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;
        $activeFilter = $this->params()->fromRoute('activeFilter') ? (int) $this->params()->fromRoute('activeFilter') : 1;
        $return_url = $this->params()->fromQuery('return_url') ? $this->params()->fromQuery('return_url') : 'remediationplan_list';

        if ($roleFilter == 1) {
            $this->layout()->searchRoleFilter = 'company';
        }

        $mappingSortCol = array(
            'id' => 'c_id',
            //'typeItem' => 'u_id',
            //'name' => '_name',
            'organization' => 'c_name'
        );

        $mappingTypeItem = array(
            0 => 'all',
            1 => 'companies'
        );

        $identity  = $this->getIdentity();

        //$sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'c_id';
        $orderBy = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'c_id';
        //$paginator = $this->getCompanyTable()->getCompanies(true, $sortCol, $order,  $mappingTypeItem[$roleFilter], $this->getIdentity(), $activeFilter);
        //getCompaniesv2
        //$array[] = $location;
        //$paginator = $this->getCompanyTable()->getCompaniesv2($mappingTypeItem[$roleFilter], $this->identity, $orderBy, $order,$activeFilter);
        //$paginator = $this->companyTableReference->getCompaniesv2($mappingTypeItem[$roleFilter], $this->identity, $orderBy, $order, $activeFilter);
        $paginator = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompaniesv2($mappingTypeItem[$roleFilter], $identity, $orderBy, $order, $activeFilter);
        //$paginator = $this->initializeClientTableService->getCompanyTable($this->companyTable, $this, $mappingTypeItem[$roleFilter], $this->identity, $orderBy, $order, $activeFilter);
        //$companyTable, $object, $mappingTypeItem, $identity, $orderBy, $order, $activeFilter

        //$users1 = $this->getUserTable()->getFullContactsByCompanyId($identity['u_company_id'], $orderBy, $order, $activeFilter);
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
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter,
            'activeFilter' => $activeFilter,
            'checkClientLimitCompany' => $this->getCompanyTable()->checkClientLimitCompany()
            //'checkClientLimitCompany' => $this->companyTableReference->checkClientLimitCompany()
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open Organization List page');

        return $view;
    }

    public function editAction()
    {
        
        
        $request = $this->getRequest();
        //$test5 = $this->getRequest()->isPost();

        $organizationId = (int) $this->params('id');
        $noteform = $request->isPost() && (int) $request->getPost('noteform');
        $renewalform = $request->isPost() && (int) $request->getPost('renewalform');
        $rolesform = $request->isPost() && (int) $request->getPost('rolesform');
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;
        $return_url = $this->params()->fromQuery('return_url') ? $this->params()->fromQuery('return_url') : 'remediationplan_list';

        
        // $request1 = new \Zend\Http\PhpEnvironment\Request();
        // $http_referer = $request1->getServer('HTTP_REFERER');
        // $parsed = parse_url($http_referer, PHP_URL_PATH); //dashboard/client


        $this->getLogTable()->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $organizationId);

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }
        // if (!$this->hasIdentity()) {
        //     $this->flashMessenger()->addErrorMessage('You must log in');
        //     return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        // }

        //$checkClientLimitCompany = $this->companyTableReference->checkClientLimitCompany();
        $checkClientLimitCompany = $this->getServiceLocator()->get('Client\Model\CompanyTable')->checkClientLimitCompany();
        

        if(!$organizationId) {
            if(!$checkClientLimitCompany) {
                $this->flashMessenger()->addErrorMessage('You cannot create more than 1 company');

                if ($return_url == 'dashboard') {
                    return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                } else {
                    return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                }
            }
        }

        


        $form = new OrganizationForm($this->getServiceLocator(), $organizationId);
        $formNote = new NoteForm($this->getServiceLocator());
        
        $companyObj = null;
        $contacts = null;
        $primaryContactId = null;
        $trainingManagerIds = null;
        $checkHasPartial = null;
        $existsCompanyRoles = array();
        $ownerContact = null;
        $notes = null;
        $formRenewal = null;
        $purchasedLicensesTemp = null;
        $purchasedLicense = 0;
        $usedLicense = 0;

        if ($organizationId) {

            //$companyObj = $this->companyTableReference->getCompany($organizationId);
            $companyObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompany($organizationId);
            //get Hipaa
            //hipaa_suite_type_id;
            

            if(!$companyObj) {
                if ($return_url == 'dashboard') {
                    return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                } else {
                    return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                }
            }

            //$purchasedLicensesTemp = $this->companyTableReference->checkPurchasedLicensesByCompany($organizationId);
            $purchasedLicensesTemp = $this->getServiceLocator()->get('Client\Model\CompanyTable')->checkPurchasedLicensesByCompany($organizationId);
            $purchasedLicense = $purchasedLicensesTemp->c_users_limit;
            $usedLicense = $this->getUserTable()->getUsedLicenseCount($organizationId);

            $contacts = $this->getUserTable()->getUsersByCompany($organizationId, $companyObj->c_primary_contact_u_id);
            $notes = $this->getNoteTable()->getNotes($organizationId, \Note\Model\Note::NOTE_COMPANY);

            $primaryContactId = $companyObj->c_primary_contact_u_id;

            $trainingManagerIds = $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->getTrainingManagersIdsForCompany($organizationId);

            $checkHasPartial = $this->getUserTable()->checkHasPartial($organizationId);
            $existsCompanyRoles = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getExistsCompanyRoles($organizationId);

            $ownerContact = $this->getUserTable()->getUser($companyObj->c_owner_u_id);
            $formRenewal = new RenewalForm($this->getServiceLocator(), $companyObj);
        }

        $addresses = array();
        $identity = $this->getIdentity();        

        
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if (isset($post['revert-alias'])) {
                $this->getServiceLocator()->get('Assessment\Model\CompanyAssessmentRoleAlias')->deleteCompanyAssessmentRoleAlias($organizationId, $post['revert-alias']);
                
                if ($return_url == 'dashboard') {
                    return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                } else {
                    return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                }

            } elseif ($renewalform){
                //$this->companyTableReference->saveRenewalData($organizationId, $request->getPost());
                $this->getServiceLocator()->get('Client\Model\CompanyTable')->saveRenewalData($organizationId, $request->getPost());
                
                if ($return_url == 'dashboard') {
                    return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                } else {
                    return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                }
            } elseif ($noteform) {
                $note = new Note();
                $formNote->setInputFilter($note->getInputFilter($this->getServiceLocator(), $organizationId));
                $formNote->setData($request->getPost());

                if ($formNote->isValid()) {
                    $post = $request->getPost();

                    $note->exchangeArray($request->getPost());
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles());

                    $this->flashMessenger()->addSuccessMessage('Note saved');
                    
                    if ($return_url == 'dashboard') {
                        return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                    } else {
                        return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                    }

                } else {
                    if ($organizationId) {
                        $form->bind($companyObj);
                        $addresses = $this->getAddressTable()->getAddresses($organizationId, \Client\Model\AddressItem::COMPANY_TYPE);
                    }
                }
            } else if($rolesform) {
                $post = $request->getPost();

                if (isset($post['rolesform'])) {
                    foreach ($post['rolesform'] as $roleId => $uRId) {
                        $cr = new CompanyRoles();

                        $dataCr['cr_c_id']  = $organizationId;
                        $dataCr['cr_ar_id'] = $roleId;
                        $dataCr['cr_u_id']  = $uRId;

                        $cr->exchangeArray($dataCr);
                        $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->saveCompanyRole($cr);
                    }
                }

                if (isset($post['aliasform'])) {
                    foreach ($post['aliasform'] as $roleId => $alias) {
                        $data['alias'] = $alias;
                        $data['roleId'] = $roleId;
                        $data['companyId'] = $organizationId;
                        $this->getServiceLocator()->get('Assessment\Model\CompanyAssessmentRoleAlias')->saveCompanyAssessmentRoleAlias($data);
                    }
                }

                if ($return_url == 'dashboard') {
                    return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                } else {
                    return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                }
            } else {
                $post = $request->getPost();
                                
                $post['c_owner_u_id'] = $identity['u_id'];
                $post['c_update_u_id'] = $identity['u_id'];

                if (!$organizationId) {
                    $post['c_primary_contact_u_id'] = $identity['u_id'];
                }
                    
                $company = new Company();
                $company->exchangeArray($post);
                $form->setData($post);
                $form->setInputFilter($company->getInputFilter($this->getServiceLocator(), $organizationId));                

                $inputFilter = $form->getInputFilter();
                
                
                //if ((int)($company->c_id) && ($company->c_name != '')) { 
                if ($form->isValid()) {           
                    //var_dump($this->getServiceLocator());
                    //$this->companyTableReference->setServiceLocator($this->getServiceLocator());
                    //$companyId = $this->companyTableReference->saveCompany($company);
                    $companyId = $this->getServiceLocator()->get('Client\Model\CompanyTable')->saveCompany($company);
                    //$this->companyTableReference->saveAddresses($companyId, $post);
                    $this->getServiceLocator()->get('Client\Model\CompanyTable')->saveAddresses($companyId, $post);

                    $this->flashMessenger()->addSuccessMessage('Company saved');

                    if ($organizationId) {
                        $this->getLogTable()->saveUserFileLog('Update company "' . $companyId . '"');
                    } else {
                        $this->getLogTable()->saveUserFileLog('Add new company "' . $companyId . '"');
                    }

                    if(isset($post['save_continue'])) {
                        return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'edit', 'id' => $companyId , '#' => 'tab3'));
                    } else {
                        if ($return_url == 'dashboard') {
                            return $this->redirect()->toRoute('dashboard', array('controller' => 'dashboard', 'action' => 'client'));
                        } else {
                            return $this->redirect()->toRoute('organization', array('controller' => 'organization', 'action' => 'list'));
                        }
                    }

                } else {
                    if ($organizationId) {
                        $form->bind($companyObj);
                        $addresses = $this->getAddressTable()->getAddresses($organizationId, \Client\Model\AddressItem::COMPANY_TYPE);
                    }
                }
            }

        } else {
            if ($organizationId) {
                $form->bind($companyObj);
                $addresses = $this->getAddressTable()->getAddresses($organizationId, \Client\Model\AddressItem::COMPANY_TYPE);
                $this->getLogTable()->saveUserFileLog('Open edit company "' . $organizationId . '" page');
            } else {
                $this->getLogTable()->saveUserFileLog('Open add new company page');
            }
        }
        

        return array(
            'form' => $form,
            'formNote' => $formNote,
            'formRenewal' => $formRenewal,
            'cId' => $organizationId,
            'addresses' => $addresses,
            'contacts' => $contacts,
            'notes' => $notes,
            'companyObj' => $companyObj,
            'assessmentsRoles' => $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles(1, false, $organizationId),
            'primaryContactId'=> $primaryContactId,
            'trainingManagerIds'=> $trainingManagerIds,
            'roleId' => $identity['u_role_id'],
            'checkClientLimitCompany' => $checkClientLimitCompany,
            'checkHasPartial' => $checkHasPartial,
            'existsCompanyRoles' => $existsCompanyRoles,
            'ownerContact' => $ownerContact,
            'fromDate' => date('m/d/Y'),
            'toDate' => date('m/d/Y'),
            'administrationAccess' => $this->getUserTable()->checkCompanyAdministrationAccess($companyObj),
            'purchasedLicense' => $purchasedLicense,
            'usedLicense' => $usedLicense
        );
    }

    
}
