<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Itassetinventory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Itassetinventory\Form\ItAssetInventoryForm;
use Itassetinventory\Model\ItAssetInventory;
use Admin\Model\User;
use Note\Model\Note;
use Note\Form\NoteForm;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

class ItassetinventoryController extends AbstractActionController
{
    protected $companyTable;
    protected $businessassociateTable;
    protected $assessmentTable;
    protected $addressTable;
    protected $userTable;
    protected $noteTable;
    protected $mailtemplateTable;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->searchRoleFilter = 'assessment';
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

    public function getCompanyTable()
    {
        if (!$this->companyTable) {
            $sm = $this->getServiceLocator();
            $this->companyTable = $sm->get('Client\Model\CompanyTable');
        }
        return $this->companyTable;
    }

    public function getBusinessassociateTable()
    {
        if (!$this->businessassociateTable) {
            $sm = $this->getServiceLocator();
            $this->businessassociateTable = $sm->get('Businessassociate\Model\BusinessassociateTable');
        }
        return $this->businessassociateTable;
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

    public function getItassetinventoryTable()
    {
        if (!$this->assessmentTable) {
            $sm = $this->getServiceLocator();
            $this->assessmentTable = $sm->get('Itassetinventory\Model\ItassetinventoryTable');
        }
        return $this->assessmentTable;
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
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;
        $search     = $this->params()->fromRoute('search')     ? $this->params()->fromRoute('search')           : null;

        $mappingSortCol = array(
            'id' => 'iai_id',
            'cName' => '_c_name',
            'location' => '_location',
            'type' => '_type',
            'date' => 'iai_create_date',
        );

        $mappingTypeItem = array(
            0 => null,
            1 => 1,
            2 => 0
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'iai_id';
        $paginator = $this->getItassetinventoryTable()->getItAssetInventories(true, $sortCol, $order, $this->getIdentity(), $search, $mappingTypeItem[$roleFilter]);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'roleFilter' => $roleFilter,
            'search' => $search,
            'hasIdentity' => $this->hasIdentity()
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open it asset inventory list page');

        return $view;
    }

    public function editAction()
    {
        $id = (int) $this->params('id');

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);

        $formNote = new NoteForm($this->getServiceLocator());

        $iaiObj  = null;
        $reports = null;
        $notes   = null;
        $assetInvs   = [];
        $items   = [];
        $itemReports   = [];

        if ((int) $id) {
            $iaiObj = $this->getItAssetInventoryTable()->getItassetinventory($id);
            $reports = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_IAIR);
            $notes   = $this->getNoteTable()->getNotes($id, \Note\Model\Note::NOTE_IAIN);
            $items = $this->getServiceLocator()->get('Itassetinventory\Model\ItassetInventoryItemTable')->getItemsByInventory($id);
            $itemReports = $this->getServiceLocator()->get('Itassetinventory\Model\ItassetInventoryReportTable')->getByInventory($id);
        }
//foreach ($notes as $note) {var_dump($note);}die();
        $assetInvs = $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTypeTable')->getAllActive();

        $form = new ItAssetInventoryForm($this->getServiceLocator(), $iaiObj);

        $request = $this->getRequest();
        $identity = $this->getIdentity();

        $isPrivacy = false;

        if ($request->isPost()) {
            $post = $request->getPost();

            $iai = new ItAssetInventory();
            $form->setInputFilter($iai->getInputFilter($this->getServiceLocator(), $id));
            $form->setData($request->getPost());

            if($post['iai_c_id']) {
                $locations = $this->getCompanyTable()->getCompanyLocations($post['iai_c_id']);
                $form->get('iai_location_id')->setValueOptions($locations);
            }

            if ($form->isValid()) {

                $post['iai_owner_u_id']  = $identity['u_id'];
                $post['iai_update_u_id'] = $identity['u_id'];

                $iai->exchangeArray($post);
                $this->getItassetinventoryTable()->setServiceLocator($this->getServiceLocator());

                $iaiId = $this->getItassetinventoryTable()->saveItassetinventory($iai, $request->getFiles());

                if($iaiId) {
                    $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_IAI, $iaiId);

                    // save files
                    $note = new Note();
                    $noteData['note_text'] = '';
                    $noteData['note_item_type'] = \Note\Model\Note::NOTE_IAIR;
                    $noteData['note_item_id'] = $iaiId;
                    $note->exchangeArray($noteData);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'report');
                    // save text note
                    if($post['note_text'])
                    {
                        $note = new Note();
                        $noteData['note_text'] = $post['note_text'];
                        $noteData['note_item_type'] = \Note\Model\Note::NOTE_IAIN;
                        $noteData['note_item_id'] = $iaiId;
                        $note->exchangeArray($noteData);
                        $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                        $noteId = $this->getNoteTable()->saveNote($note);
                    }
                    
                    // save files
                    $note = new Note();
                    $noteData['note_text'] = '';
                    $noteData['note_item_type'] = \Note\Model\Note::NOTE_IAIN;
                    $noteData['note_item_id'] = $iaiId;
                    $note->exchangeArray($noteData);
                    $this->getNoteTable()->setServiceLocator($this->getServiceLocator());
                    $noteId = $this->getNoteTable()->saveNote($note, $request->getFiles(), false, 'notes');
                }

                return $this->redirect()->toRoute('itassetinventory', array('controller' => 'Itassetinventory', 'action' => 'list'));
            }

        } else {
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);

            if ((int) $id) {
                $form->bind($iaiObj);
            }
        }

        $companyId = $this->params('companyId');
        $company = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getCompany($companyId);

        $viewParams = array(
            'form' => $form,
            'iaiId' => $id,
            'iaiObj' => $iaiObj,
            'formNote' => $formNote,
            'reports' => $reports,
            'notes' => $notes,
            'assetInvs' => $assetInvs,
            'items' => $items,
            'itemReports' => $itemReports,
            'companyId' => $companyId,
            'manualTypeId' => \Itassetinventory\Model\ItAssetInventoryItemTypeTable::TYPE_MANUAL_ENTRY,
            'company' => $company
        );

        $viewModel = new ViewModel($viewParams);

        $viewModel->setTemplate('itassetinventory/itassetinventory/edit.phtml');

        return $viewModel;
    }


    public function deleteAction()
    {
        $id = $this->params('id');

        $this->getItassetinventoryTable()->deleteItassetinventory($id);
        $this->flashMessenger()->addSuccessMessage('Itassetinventory has been deleted');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Delete It Asset Inventory Log "' . $id . '"');

        return $this->redirect()->toRoute('itassetinventory', array('controller' => 'Itassetinventory', 'action' => 'list'));
    }

    public function unarchiveAction()
    {
        $id = $this->params('id');

        $this->getItassetinventoryTable()->unarchiveItassetinventory($id);
        $this->flashMessenger()->addSuccessMessage('Itassetinventory has been unarchived');

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Unarchive It Asset Inventory Log "' . $id . '"');

        return $this->redirect()->toRoute('itassetinventory', array('controller' => 'Itassetinventory', 'action' => 'list'));
    }

    public function getcompanylocationsAction()
    {
        $cId = $this->params('id');

        $locations = $this->getCompanyTable()->getCompanyLocations($cId);

        return new JsonModel($locations);
    }

    public function importAction() {
        $ass_no_loc = [];
        $ass_errs = [];
        $assessments = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForImport();
        foreach ($assessments as $assessment) {
            $ass_addresses = $this->getAddressTable()->getAddresses($assessment->a_id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
            $comp_addresses = $this->getAddressTable()->getAddresses($assessment->a_c_id, \Client\Model\AddressItem::COMPANY_TYPE);
/*
            if ($ass_addresses->count() > 1 || $comp_addresses->count() > 1) {
                $ass_no_loc[] = $assessment->a_id;
                continue;
            }*/
            $iaiId = 0;
            $flag = 1;
            foreach ($ass_addresses as $ass_address) {
                foreach ($comp_addresses as $comp_address) {
                    $ailiItems = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->getAiliByLocation($assessment->a_id, $ass_address->adr_id);
                    $notes = $this->getNoteTable()->getNotes($assessment->a_id,  \Note\Model\Note::NOTE_AILI, $ass_address->adr_id);
                    $reportFiles = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getAilrByLocation($assessment->a_id, $ass_address->adr_id);
                    var_dump($ailiItems);die();
                    foreach ($ailiItems as $key => $items) {
                        foreach ($items as $item) {
                            if (!$iaiId) {
                                $data = array(
                                    'iai_c_id'        => $assessment->a_c_id,
                                    'iai_location_id' => $comp_address->adr_id,
                                    'iai_type_id'     => \Itassetinventory\Model\ItAssetInventoryItemTypeTable::TYPE_MANUAL_ENTRY,
                                    'iai_owner_u_id'        => $item->aili_create_u_id,
                                    'iai_update_u_id' => $item->aili_update_u_id,
                                    'iai_create_date'     => $item->aili_create_date,
                                    'iai_update_date'     => $item->aili_update_date,
                                );
                                $iaiId = $this->getItassetinventoryTable()->importItassetinventory($data);
                                if (!$iaiId) {
                                    $flag = 0;
                                    break;
                                }
                            }
                            $data_iaii = [];
                            $data_iaii['iaii_iai_id'] = $iaiId;
                            $data_iaii['iaii_iaiit_id'] = $key;
                            $data_iaii['iaii_name'] = $item->aili_name;
                            $data_iaii['iaii_model'] = $item->aili_model;
                            $data_iaii['iaii_description'] = $item->aili_description;
                            $data_iaii['iaii_create_u_id'] = $item->aili_create_u_id;
                            $data_iaii['iaii_update_u_id'] = $item->aili_update_u_id;
                            $data_iaii['iaii_create_date'] = $item->aili_create_date;
                            $data_iaii['iaii_update_date'] = $item->aili_update_date;

                            $iaii_id = $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->importIaii($data_iaii);
                            if (!$iaii_id) {
                                $flag = 0;
                            }
                        }
                        
                    }
                    
                    foreach ($notes as $note) {
                        $note_dest = new Note;
                        $note_dest->note_item_type = \Note\Model\Note::NOTE_IAIN;
                        $note_dest->note_item_id = $iaiId;
                        if (!$this->getNoteTable()->copyNote($note, $note_dest)) {
                            $flag = 0;
                        }
                    } 
                    foreach ($reportFiles as $reports) {//var_dump($report);continue;
                        foreach ($reports as $report) {
                            if ($report->ailr_ai_id == 5) continue;
                            if (!$this->getServiceLocator()->get('Itassetinventory\Model\ItassetInventoryReportTable')->createReportFromAssessmentInventoryLocationReport($iaiId, $report)) {
                                $flag = 0;
                            }
                        }
                    }
                    break;
                }
                break;
            }            
            if (!$flag) {
                $ass_errs[] = $assessment->a_id;
            }
        }
        var_dump($ass_no_loc, $ass_errs);
        die();
    }
}
