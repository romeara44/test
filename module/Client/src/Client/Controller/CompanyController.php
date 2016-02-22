<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Client\Controller;

use Client\Form\CompanyForm;
use Note\Form\NoteForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Client\Model\Company;
use Application\Model\LogsTable;
use Client\Model\CompanyRoles;
use Note\Model\Note;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class CompanyController extends AbstractActionController
{
    protected $companyTable;
    protected $addressTable;
    protected $userTable;
    protected $noteTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->searchRoleFilter = 'company';
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

    public function editAction()
    {
        $request = $this->getRequest();

        $id = (int) $this->params('id');
        $noteform = $request->isPost() && (int) $request->getPost('noteform');
        $rolesform = $request->isPost() && (int) $request->getPost('rolesform');
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $this->getLogTable()->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $id);

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $checkClientLimitCompany = $this->getCompanyTable()->checkClientLimitCompany();
        
        if(!$id) {
            if(!$checkClientLimitCompany) {
                $this->flashMessenger()->addErrorMessage('You cannot create more than 1 companies');
                return $this->redirect()->toRoute('client', array('controller' => 'client', 'action' => 'list'));
            }
        }

        $form = new CompanyForm($this->getServiceLocator(), $id);
        $formNote = new NoteForm($this->getServiceLocator());
        $companyObj = null;
        $contacts = null;
        $primaryContactId = null;
        $trainingManagerId = null;
        $checkHasPartial = null;
        $existsCompanyRoles = array();
        $ownerContact = null;
        $notes = null;

        if ($id) {
            $companyObj = $this->getCompanyTable()->getCompany($id);
            if(!$companyObj) {
                return $this->redirect()->toRoute('client', array('controller' => 'client', 'action' => 'list'));
            }

            $contacts = $this->getUserTable()->getUsersByCompany($id, $companyObj->c_primary_contact_u_id);
            $notes = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_COMPANY);

            $primaryContactId = $companyObj->c_primary_contact_u_id;
            $trainingManagerId = $companyObj->c_training_manager_u_id;

            $checkHasPartial = $this->getUserTable()->checkHasPartial($id);
            $existsCompanyRoles = $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->getExistsCompanyRoles($id);

            $ownerContact = $this->getUserTable()->getUser($companyObj->c_owner_u_id);
        }

        $addresses = array();
        $identity = $this->getIdentity();

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

                    $this->flashMessenger()->addSuccessMessage('Note saved');

                    return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
                } else {
                    if ($id) {
                        $form->bind($companyObj);
                        $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::COMPANY_TYPE);
                    }
                }
            } else if($rolesform) {
                $post = $request->getPost();

                if (isset($post['rolesform'])) {
                    foreach ($post['rolesform'] as $roleId => $uRId) {
                        $cr = new CompanyRoles();

                        $dataCr['cr_c_id']  = $id;
                        $dataCr['cr_ar_id'] = $roleId;
                        $dataCr['cr_u_id']  = $uRId;

                        $cr->exchangeArray($dataCr);
                        $this->getServiceLocator()->get('Client\Model\CompanyRolesTable')->saveCompanyRole($cr);
                    }
                }
                return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
            } else {
                $post = $request->getPost();
                $post['c_owner_u_id'] = $identity['u_id'];
                $post['c_update_u_id'] = $identity['u_id'];

                if (!$id) {
                    $post['c_primary_contact_u_id'] = $identity['u_id'];
                }
                    
                $company = new Company();
                $company->exchangeArray($post);
                $form->setData($post);
                $form->setInputFilter($company->getInputFilter($this->getServiceLocator(), $id));                

                if ($form->isValid()) {                    
                    
                    $this->getCompanyTable()->setServiceLocator($this->getServiceLocator());
                    $companyId = $this->getCompanyTable()->saveCompany($company);
                    $this->getCompanyTable()->saveAddresses($companyId, $post);

                    $this->flashMessenger()->addSuccessMessage('Company saved');

                    if ($id) {
                        $this->getLogTable()->saveUserFileLog('Update company "' . $companyId . '"');
                    } else {
                        $this->getLogTable()->saveUserFileLog('Add new company "' . $companyId . '"');
                    }

                    if(isset($post['save_continue'])) {
                        return $this->redirect()->toRoute('company', array('controller' => 'company', 'action' => 'edit', 'id' => $companyId , '#' => 'tab3'));
                    } else {
                        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
                    }

                } else {
                    if ($id) {
                        $form->bind($companyObj);
                        $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::COMPANY_TYPE);
                    }
                }
            }

        } else {
            if ($id) {
                $form->bind($companyObj);
                $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::COMPANY_TYPE);
                $this->getLogTable()->saveUserFileLog('Open edit company "' . $id . '" page');
            } else {
                $this->getLogTable()->saveUserFileLog('Open add new company page');
            }
        }

        return array(
            'form' => $form,
            'formNote' => $formNote,
            'cId' => $id,
            'addresses' => $addresses,
            'contacts' => $contacts,
            'notes' => $notes,
            'companyObj' => $companyObj,
            'assessmentsRoles' => $this->getServiceLocator()->get('Assessment\Model\AssessmentRoleTable')->getAssessmentsRoles(),
            'primaryContactId'=> $primaryContactId,
            'trainingManagerId'=> $trainingManagerId,
            'roleId' => $identity['u_role_id'],
            'checkClientLimitCompany' => $checkClientLimitCompany,
            'checkHasPartial' => $checkHasPartial,
            'existsCompanyRoles' => $existsCompanyRoles,
            'ownerContact' => $ownerContact,
            'fromDate' => date('m/d/Y'),
            'toDate' => date('m/d/Y'),
            'administrationAccess' => $this->getUserTable()->checkCompanyAdministrationAccess($companyObj)
        );
    }

    public function deleteAction()
    {
        $identity = $this->getIdentity();
        $id       = $this->params('id');

        if($identity['u_company_id'] != $id) {
            $this->getCompanyTable()->deleteCompany($id);
            $this->flashMessenger()->addSuccessMessage('Company has been deleted');
        }

        $this->getLogTable()->saveUserFileLog('Delete company "' . $id . '"');

        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getCompanyTable()->unarchiveCompany($id);
        $this->flashMessenger()->addSuccessMessage('Company has been unarchived');

        $this->getLogTable()->saveUserFileLog('Unarchive company "' . $id . '"');

        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

    public function viewAction()
    {

    }

    public function getprimaryaddressAction()
    {
        $id = (int) $this->params('id');

        $companyObj = $this->getCompanyTable()->getCompany($id);
        $primaryAddressObj = $this->getAddressTable()->getAddress($companyObj->c_primary_adr_id);

        $this->getLogTable()->saveUserFileLog('Get primary address for company "' . $id . '"');

        return $this->getResponse()->setContent(json_encode(array('company' => $companyObj, 'address' => $primaryAddressObj)));
    }

    public function deleteaddressAction()
    {
        $id = $this->params('id');
        $adrId = $this->params('adrId');

        $this->getServiceLocator()->get('Client\Model\AddressTable')->deleteAddress($adrId);
        $this->flashMessenger()->addSuccessMessage('Address has been deleted');

        $this->getLogTable()->saveUserFileLog('Delete address "' . $adrId . '"" for company "' . $id . '"');

        return $this->redirect()->toRoute('company', array('controller' => 'company', 'action' => 'edit', 'id' => $id));
    }

    public function activatepartialusersAction()
    {
        $request = $this->getRequest();
        $id      = null;

        if ($request->isPost()) {
            $post = $request->getPost();
            if(isset($post['id'])) $id = (int)$post['id'];
        }

        if(!$id) {
            return false;
        }

        $activate = $this->getUserTable()->activatePartialsByCompany($id);

        $this->getLogTable()->saveUserFileLog('Active partial users company "' . $id . '"');

        return new JsonModel(array('result' => (boolean)$activate));
    }

    public function usersloginhistoryAction()
    {
        $request = $this->getRequest();

        if($request->isXmlHttpRequest()) {
            $countPerPage = 10;

            $cId  = $this->params('id')                ? $this->params('id')                : null;
            $page = $this->params('page')              ? $this->params('page')              : null;
            $from = $this->params()->fromQuery('from') ? $this->params()->fromQuery('from') : null;
            $to   = $this->params()->fromQuery('to')   ? $this->params()->fromQuery('to')   : null;

            $from = date('Y-m-d', strtotime($from));
            $to   = date('Y-m-d', strtotime($to));

            $paginator = $this->getLogTable()->getCompanyUsersLoginHistory($cId, $from, $to);

            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($countPerPage);

            $statusesMap = array(LogsTable::TYPE_AUTH_SUCCESS => 'Success',
                                 LogsTable::TYPE_AUTH_FAILED  => 'Unsuccess',
                                 LogsTable::TYPE_AUTH_LOCKED  => 'Locked'
                                );

            $view = new ViewModel;

            $view->setVariables(array(
                'order_by'    => 'date',
                'order'       => 'DESC',
                'cId'         => $cId,
                'from'        => $from,
                'to'          => $to,
                'page'        => $page,
                'statusesMap' => $statusesMap,
                'paginator'   => $paginator
            ));

            $view->setTemplate('client/company/users_login_history.phtml');

            $view->setTerminal(true);
        }
        
        return $view;
    }

    public function lockedusersAction()
    {
        $request = $this->getRequest();

        if($request->isXmlHttpRequest()) {
            $countPerPage = 10;

            $cId  = $this->params('id')   ? $this->params('id')   : null;
            $page = $this->params('page') ? $this->params('page') : null;

            $paginator = $this->getUserTable()->getLockedCompanyUsers($cId);

            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($countPerPage);

            $view = new ViewModel;

            $view->setVariables(array(
                'order_by'    => 'date',
                'order'       => 'DESC',
                'cId'         => $cId,
                'page'        => $page,
                'paginator'   => $paginator
            ));

            $view->setTemplate('client/company/locked_users.phtml');

            $view->setTerminal(true);
        }
        
        return $view;
    }

    public function getAddressesAction()
    {
        $id = (int) $this->params('id');

        $addresses = $this->getAddressTable()->getAddresses($id, \Client\Model\AddressItem::COMPANY_TYPE);

        $res = array();

        foreach ($addresses as $address) {
            $res[] = $address;
        }

        return $this->getResponse()->setContent(json_encode(array('addresses' => $res)));
    }
}
