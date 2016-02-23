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
        $assessments = $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->getForImport();
        $i = $j = 0;
        $non_imported_assessments_ids = [];
        foreach ($assessments as $assessment) {

            $ass_addresses = $this->getAddressTable()->getAddresses($assessment->a_id, \Client\Model\AddressItem::ASSESSMENT_TYPE);
            $ass_addresses_arr = [];
            foreach ($ass_addresses as $value) {
                $ass_addresses_arr[] = $value;
            }
            /*$comp_addresses = $this->getAddressTable()->getAddresses($assessment->a_c_id, \Client\Model\AddressItem::COMPANY_TYPE);
            $comp_addresses_arr = [];
            foreach ($comp_addresses as $value) {
                $comp_addresses_arr[] = $value;
            }
            //var_dump($ass_addresses_arr, $comp_addresses_arr);die();
            if (count($ass_addresses_arr) != count($comp_addresses_arr)) {
                $non_imported_assessments_ids[] = $assessment->a_id;
                //continue;
            }

            $flag = 0;
            foreach ($ass_addresses_arr as $key => $ass_address) {
                if ($ass_addresses_arr[$key]->adr_address1 != $comp_addresses_arr[$key]->adr_address1) {                        
                    $flag = 1;
                    break;
                }
            }
            if ($flag) {
                $non_imported_assessments_ids[] = $assessment->a_id;
                //continue;
            }*/
            if (count($ass_addresses_arr) > 1) {//$non_imported_assessments_ids[] = $assessment->a_id;
            $non_imported_assessments_ids[$assessment->a_c_id][] = $assessment->a_id . ' - ' . $assessment->a_status;
        }
        continue;
            foreach ($ass_addresses_arr as $key => $ass_address) {
                if (!$key) continue;
                /*$iai = new ItAssetInventory();
                $iai->iai_c_id = $assessment->a_c_id;
                $iai->iai_location_id = $comp_addresses_arr[$key]->adr_id;
                $iai->iai_type_id = \Itassetinventory\Model\ItAssetInventoryItemTypeTable::TYPE_MANUAL_ENTRY;
                $iai->iai_c_id = $assessment->a_c_id;*/
                //$ailiItems = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->getAiliByLocation($assessment->a_id, $ass_address->adr_id);
                /*foreach ($ailiItems as $key => $item) {
                    var_dump($item);continue;
                    echo $item->aili_name . ' ' . $item->aili_model . ' ' . $item->aili_description . '<br>';
                }*/
                //var_dump($ailiItems);
                $non_imported_assessments_ids[$assessment->a_c_id][] = $assessment->a_id;
                /*echo $assessment->a_id . ' - ' . $assessment->a_c_id . '<br>';           
                echo 'Notes:<br>';
                $notes = $this->getNoteTable()->getNotes($assessment->a_id,  \Note\Model\Note::NOTE_AILI, $ass_address->adr_id);
                foreach ($notes as $key => $note) {
                    var_dump($note);
                }
                echo '<br>';
                echo 'Reports:<br>';
                $reportFiles = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getAilrByLocation($assessment->a_id, $ass_address->adr_id);
                foreach ($reportFiles as $key => $report) {
                    var_dump($report);
                }*/

                echo '<br>';
            }

            /*
                $notes = $this->getNoteTable()->getNotes($assessment->a_id, \Note\Model\Note::NOTE_AILI, $ass_address->adr_id);
                $assessmentsInv = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryTable')->getAssessmentsInventory();
                $ailiItems = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationItemTable')->getAiliByLocation($assessment->a_id, $ass_address->adr_id);
                $reportFiles = $this->getServiceLocator()->get('Assessment\Model\AssessmentInventoryLocationReportTable')->getAilrByLocation($assessment->a_id, $ass_address->adr_id);

                //var_dump($notes);
                var_dump($assessmentsInv);
                //var_dump($ailiItems);
                //var_dump($reportFiles);
                */
            
            
        }
        var_dump($non_imported_assessments_ids);
        die();
    }
}
