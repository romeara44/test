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

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $id);

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

        $form = new CompanyForm($this->getServiceLocator());
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

                $company = new Company();
                $form->setInputFilter($company->getInputFilter($this->getServiceLocator(), $id));
                $form->setData($request->getPost());

                if ($form->isValid()) {
                    $post = $request->getPost();
                    $post['c_owner_u_id'] = $identity['u_id'];
                    $post['c_update_u_id'] = $identity['u_id'];

                    if (!$id) {
                        $post['c_primary_contact_u_id'] = $identity['u_id'];
                    }

                    $company->exchangeArray($request->getPost());
                    $this->getCompanyTable()->setServiceLocator($this->getServiceLocator());
                    $companyId = $this->getCompanyTable()->saveCompany($company);
                    $this->getCompanyTable()->saveAddresses($companyId, $request->getPost());

                    $this->flashMessenger()->addSuccessMessage('Company saved');

                    return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
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
            'ownerContact' => $ownerContact
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
        
        return $this->redirect()->toRoute('client', array('controller' => 'company', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getCompanyTable()->unarchiveCompany($id);
        $this->flashMessenger()->addSuccessMessage('Company has been unarchived');

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

        return $this->getResponse()->setContent(json_encode($primaryAddressObj));
    }

    public function deleteaddressAction()
    {
        $id = $this->params('id');
        $adrId = $this->params('adrId');

        $this->getServiceLocator()->get('Client\Model\AddressTable')->deleteAddress($adrId);
        $this->flashMessenger()->addSuccessMessage('Address has been deleted');

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

        return new JsonModel(array('result' => (boolean)$activate));
    }

}
