<?php

namespace Audit\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Audit\Model\AuditInquiry;
use Audit\Model\AuditRecord;
use Audit\Model\AuditRecordType;
use Audit\Model\AuditRecordSection;
use Audit\Model\AuditRecordItem;
use Audit\Model\AuditRoleLocationContact;
use Audit\Model\AuditPerformanceCriteria;

use Client\Model\HipaaSuiteModule;
use Client\Model\HipaaSuiteModuleRole;
use Client\Model\CompanyMasterRole;
use Client\Model\CompanyModuleRole;

use Audit\Form\AuditForm;

use Assessment\Model\Remediationplan;
use Admin\Model\User;
use Note\Model\Note;
use Mail\Model\Mailtemplate;
use Client\Form\ClientForm;
use Businessassociate\Model\Businessassociate;
use Businessassociate\Form\BusinessassociateForm;
use Zend\Session\Container;

class AuditController extends AbstractActionController
{
    protected $auditRecordTable;


    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->layout()->searchRoleFilter = 'audit';
        $container = new Container('activity');
        $container->activity = time();

        $this->layout()->flashMessagesSuccess = $this->flashMessenger()->getSuccessMessages();
        $this->layout()->flashMessagesErrors = $this->flashMessenger()->getErrorMessages();

        if (!$this->hasIdentity()) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $identity = $this->getIdentity();

        if (!in_array($identity['u_role_id'], array(1, 2, 3, 5,8))) {
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        } else if ($identity['u_first_login'] == 1) {
            return $this->redirect()->toRoute('user', array('controller' => 'user', 'action' => 'acceptprivacyterms'));
        }

        return parent::onDispatch($e);
    }

    public function listAction()
    {
        
        $orderBy = $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id';
        $order = $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : 'DESC';
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
        $roleFilter = $this->params()->fromRoute('roleFilter') ? (int) $this->params()->fromRoute('roleFilter') : 0;

        $mappingSortCol = array(
            'id' => 'audit_record_id',
            'date' => 'date_audited'
        );

        $sortCol = isset($mappingSortCol[$orderBy]) ? $mappingSortCol[$orderBy] : 'audit_record_id';
        $paginator = $this->getAuditRecordTable()->getAuditRecords(true, $sortCol, $order, $this->getIdentity());
        
        //$paginator->setCurrentPageNumber(1);
        //$paginator->setItemCountPerPage($paginator->getTotalItemCount());

        $res = [];
        foreach ($paginator as $ar) {
                        
            $remediationPlanForIndex = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplan($ar->rp_id);
            $ar->_remediationPlanIndex = (!isset($remediationPlanForIndex->rp_version_index_item)) ? ( $remediationPlanForIndex->rp_version_index ) : ( $remediationPlanForIndex->rp_version_index . '-' . $remediationPlanForIndex->rp_version_index_item . '-' . (($remediationPlanForIndex->rp_type == 1) ? 'S' : 'P') );

            $res[$ar->audit_record_id][] = $ar;

        }

                
        $view = new ViewModel(array(
            'order_by' => $orderBy,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
            'hasIdentity' => $this->hasIdentity(),
            'roleFilter' => $roleFilter,
            'res' => $res,
        ));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog('Open audit record list page');

        return $view;
    }

    public function createNewAudit(AuditRecord $auditRecord){
        $identity = $this->getIdentity();

        $remediationPlanTableResponse = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplan($auditRecord->rp_id);
        $activeAuditRecordSections = $this->getServiceLocator()->get('Audit\Model\AuditRecordSectionMasterTable')->insertNewSectionMasterItems($auditRecord, $remediationPlanTableResponse->rp_type);

        return $auditRecord;
    }

    public function newauditAction(){
        $request = $this->getRequest();
        $identity = $this->getIdentity();

        $selectedCompanyId = 0;
        $updateAuditRecordId = 0;
        $leadAuditor = 0;
        $validationRoles = "";
        $leadAuditorId = $this->getServiceLocator()->get('Client\Model\CompanyMasterRoleTable')->getCompanyMasterRoleIdPerRoleName("Lead Auditor *");

        if ($request->isGet()){
            $selectedCompanyId = $this->params()->fromQuery('company');
        }

        $companyTable = $this->getServiceLocator()->get('Client\Model\CompanyTable');

        foreach ($companyTable->getCompaniesPairs() as $key => $r) {
            $companies[$key] = $r;
        }

        if ($request->isPost()){

            $auditRoleLocationContacts = $this->params()->fromPost('auditRoleLocationContact');

            If ($auditRoleLocationContacts[1] != ""){
                $selectedCompanyId = $this->params()->fromPost('postCompanyId');
                $selectedRemediationPlan = $this->params()->fromPost('selectedRemediationPlan');
                $locationId = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getLocationIdByRemediationplanId($selectedRemediationPlan);
    
                $auditRecordNew = new AuditRecord();
                $auditRecordNew->rp_id = $selectedRemediationPlan;
                $auditRecordNew->audit_status_id = 5; //TODO This magic number should be updated.
                $auditRecordNew->company_id = $selectedCompanyId;
                $auditRecordNew->date_audited = date('Y-m-d H:i:s');
    
                $updateAuditRecordId = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->saveAuditRecord($auditRecordNew);
    
                
                $leadAuditor = $this->getServiceLocator()->get('Audit\Model\AuditRoleLocationContactTable')->saveAuditContacts($leadAuditorId, $auditRoleLocationContacts, $locationId, $updateAuditRecordId);
                $auditRecordToSave = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->getAuditRecord($updateAuditRecordId);
                $auditRecordToSave->auditor_u_id = $leadAuditor;
    
                $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->saveAuditRecord($auditRecordToSave);
    
                $aId = $this->createNewAudit($auditRecordToSave);
    
                return $this->redirect()->toRoute('audit', array('controller' => 'audit', 'action' => 'edit', 'id' => $auditRecordToSave->audit_record_id));
            }
            else {
                $validationRoles = "Lead Auditor required";
                
            }

        }

        $remediationPlans = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getListOfRemediationPlans($selectedCompanyId);
        $auditRoles = $this->getServiceLocator()->get('Client\Model\HipaaSuiteModuleRoleTable')->getListOfRoles(5);

        //Start of Company Consultant Retrieval
        $companyConsultants = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getByCompany($selectedCompanyId);
        $companyUsers = array();
        foreach ($companyConsultants as $consultant) {
            $consultantUser = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($consultant->cc_consultant_id);
            
            array_push($companyUsers, $consultantUser);
        }

        //End of Company Consultant Retrieval

        $viewParams = array(
            'companies' => isset($companies) ? $companies : 0,
            'selectedCompany' => $selectedCompanyId,
            'remediationPlans' => $remediationPlans,
            'companyUsers' => $companyUsers, //$this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($selectedCompanyId),
            'auditRoles' => $auditRoles,
            'companyConsultants' => $companyConsultants,
            'validationError' => $validationRoles
        );


        $viewModel = new ViewModel($viewParams);

        return $viewModel;

    }

    public function saveItemStatusAction() {
        $value = $this->getRequest()->getPost('value');
        //$files = $this->getFiles();

        if (!empty($value)){
            $pieces = explode("|", $value);
            
            $id = substr($pieces[0], strpos($pieces[0], '[') + 1, strlen($pieces[0]) - 8);

            $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->saveAuditRecordItemStatus($id, $pieces[1]);
        }

        return false;
    }

    public function cloneAudit(AuditRecord $auditRecord){
        
        $savedAuditRecordId = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->cloneAuditRecord($auditRecord);
        
        // Audit Record Type Copy
        $sourceAuditRecordTypes = $this->getServiceLocator()->get('Audit\Model\AuditRecordTypeTable')->getAuditRecordTypeByAuditRecordId($auditRecord->audit_record_id);
        foreach ($sourceAuditRecordTypes as $sourceAuditRecordType) {
            $savedAuditRecordTypeId = $this->getServiceLocator()->get('Audit\Model\AuditRecordTypeTable')->cloneAuditRecordType($sourceAuditRecordType, $savedAuditRecordId);

            // Audit Record Section Copy
            $sourceAuditRecordSections = $this->getServiceLocator()->get('Audit\Model\AuditRecordSectionTable')->getAuditRecordSectionByTypeId($sourceAuditRecordType->audit_record_type_id);
            foreach ($sourceAuditRecordSections as $sourceAuditRecordSection){
                $savedAuditRecordSectionId = $this->getServiceLocator()->get('Audit\Model\AuditRecordSectionTable')->cloneAuditRecordSection($sourceAuditRecordSection, $savedAuditRecordTypeId);

                // Audit Record Item Copy
                $sourceAuditRecordItems = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->getAuditRecordItemsPerSectionId($sourceAuditRecordSection->audit_record_section_id);
                foreach ($sourceAuditRecordItems as $sourceAuditRecordItem){
                    $savedAuditRecordItemId = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->cloneAuditRecordItem($sourceAuditRecordItem, $savedAuditRecordSectionId);

                    // Audit Record Notes
                    $sourceAuditRecordNotes = $this->getServiceLocator()->get('Note\Model\NoteTable')->getNoteByAndItemId(\Note\Model\Note::NOTE_AUDIT_ITEM, $auditRecord->audit_record_id, $sourceAuditRecordItem->audit_record_item_id);
                    foreach ($sourceAuditRecordNotes as $sourceAuditRecordNote) {
                        $savedAuditNoteId = $this->getServiceLocator()->get('Note\Model\NoteTable')->cloneNote($sourceAuditRecordNote, $savedAuditRecordId, $savedAuditRecordItemId);

                        // //Audit Record Note Files
                        $sourceAuditRecordFiles = $this->getServiceLocator()->get('Note\Model\NotesFilesTable')->getFilesByNoteId($sourceAuditRecordNote->note_id);
                        foreach ($sourceAuditRecordFiles as $sourceAuditRecordFile) {
            
                            // Get the source file from the files table record
                            $sourceFile = $this->getServiceLocator()->get('Application\Model\FilesTable')->getFile($sourceAuditRecordFile->nf_f_id);
                            
                            $newFile = array();
                            $newFile['f_name'] = $sourceFile->f_name;
                            $newFile['f_type'] = $sourceFile->f_type;
                            $newFile['f_create_date'] = $sourceFile->f_create_date;
                            $newFile['f_encrypted'] = $sourceFile->f_encrypted;
                            
                            $savedFileId = $this->getServiceLocator()->get('Application\Model\FilesTable')->saveFile($newFile);
                            //////$savedFileId = $this->getServiceLocator()->get('Application\Model\FilesTable')->cloneFile($sourceFile);

                            $newNoteFile = array();
                            $newNoteFile['nf_f_id'] = $savedFileId;
                            $newNoteFile['nf_note_id'] = $savedAuditNoteId;
                            $newNoteFile['nf_active'] = $sourceAuditRecordFile->nf_active;
                            
                            $savedNoteFileId = $this->getServiceLocator()->get('Note\Model\NotesFilesTable')->saveFile($newNoteFile);
                            //////$savedNoteFileId = $this->getServiceLocator()->get('Note\Model\NotesFilesTable')->cloneNoteFile($savedFileId, $savedAuditNoteId, $sourceAuditRecordFile);

                            $notesFolder = 'public/data/notefiles';
                            //var_dump($savedAuditNoteId);
                                                        
                            //$this->getServiceLocator()->get('Note\Model\NotesFilesTable')->clonePhysicalFile($savedAuditNoteId, $sourceAuditRecordFile, $sourceFile, $savedFileId);
                            if (file_exists($notesFolder . '/' . $savedAuditNoteId)) {
                            } else {
                                mkdir($notesFolder . '/' . $savedAuditNoteId);
                            }
                            //var_dump($notesFolder . '/' . $sourceAuditRecordFile->nf_note_id . '/' . $sourceFile->f_id);
                            //var_dump($notesFolder . '/' . $savedAuditNoteId . '/' . $savedFileId);

                            copy($notesFolder . '/' . $sourceAuditRecordFile->nf_note_id . '/' . $sourceFile->f_id, $notesFolder . '/' . $savedAuditNoteId . '/' . $savedFileId);
                            //copy($notesFolder . '/' . $sourceAuditRecordFile->nf_note_id . '/' . $sourceFile->f_id, $notesFolder . '/' . $savedAuditNoteId . '/' . $savedFileId);
                        }
                        //$this->getServiceLocator()->get('Note\Model\NotesFilesTable')->cloneNoteFiles($savedAuditNoteId, $sourceAuditRecordNote);
                    }
                }
            }
            
        }

        return $auditRecord;

    }

    public function editAction(){

        if (!$this->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage('You must log in');
            return $this->redirect()->toRoute('application', array('controller' => 'index', 'action' => 'index'));
        }

        $question_type = \Audit\Model\AuditRecord::QUEST_TYPE_SECURITY; //3;
        $selectedReviewedId = 0;
        $selectedAuditorApprovedId = 0;
        $isSignedOff = 0;
        $isSignedOffCanClose = 0;
        $isSignedOffError = 0;

        $id = $this->params('id');

        $companyRolesMsg = '';

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_OPEN, \Application\Model\LogsTable::ITEM_TYPE_AUDIT, $id);

        $editAuditRecord = null;

        if ((int) $id) {
            $editAuditRecord = $this->getAuditRecordTable()->getAuditRecord($id);
            $remediationPlanRecord = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplanByRemediationId($editAuditRecord->rp_id);
            $question_type = $remediationPlanRecord->rp_type == 1 ? \Audit\Model\AuditRecord::QUEST_TYPE_SECURITY : \Audit\Model\AuditRecord::QUEST_TYPE_PRIVACY;
            $editAuditRecord->reviewed_date = $editAuditRecord->reviewed_date != null ? \DateTime::createFromFormat('Y-m-d',  $editAuditRecord->reviewed_date)->format('m/d/Y') : '';
            $editAuditRecord->auditor_approve_date = $editAuditRecord->auditor_approve_date != null ? \DateTime::createFromFormat('Y-m-d',  $editAuditRecord->auditor_approve_date)->format('m/d/Y') : '';
            $selectedReviewedId = $editAuditRecord->reviewed_u_id;
            $selectedAuditorApprovedId = $editAuditRecord->auditor_approve_u_id;
        }

        $identity = $this->getIdentity();

        $request = $this->getRequest();

        $valid = false;

        $form = new AuditForm($this->getServiceLocator());

        $form->get('submit')->setAttribute('value', 'Edit');

        if ($request->isPost() && !$editAuditRecord->is_locked) {
            $post = $request->getPost();
            $files = $request->getFiles();

            if (!isset($remediationPlanRecord)){
                $remediationPlanRecord = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplanByRemediationId($editAuditRecord->rp_id);
            }
            $question_type = $remediationPlanRecord->rp_type == 1 ? \Audit\Model\AuditRecord::QUEST_TYPE_SECURITY : \Audit\Model\AuditRecord::QUEST_TYPE_PRIVACY;


            $editAuditRecord->reviewed_u_id = $post['audit_review_approve_u_id'];
            $editAuditRecord->reviewed_initials = $post['audit_review_approve_initial'];
            $editAuditRecord->reviewed_date = ($post['audit_review_approve_date'] != '') ? \DateTime::createFromFormat('m/d/Y',  $post['audit_review_approve_date'])->format('Y-m-d') : '';
            $editAuditRecord->auditor_approve_u_id = $post['auditor_approve_u_id'];
            $editAuditRecord->auditor_approve_initials = $post['auditor_approve_initial'];
            $editAuditRecord->auditor_approve_date = ($post['auditor_approve_date'] != '') ? \DateTime::createFromFormat('m/d/Y', $post['auditor_approve_date'])->format('Y-m-d') : '';

            $selectedReviewedId = $post['audit_review_approve_u_id'];
            $selectedAuditorApprovedId = $post['auditor_approve_u_id'];

            if ($post['signedoff'] == 1) {

                $unansweredItemsCount = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->getUnansweredItemsCount($id);

                if ($unansweredItemsCount < 1 && $this->signAndLockAudit($post) ){
                    $editAuditRecord->is_locked = 1;
                    $editAuditRecord->audit_status_id = 6;
                    $isSignedOff = 1;
                    $isSignedOffCanClose = 1;
                }
                else {
                    $isSignedOffError = 1;
                }

            }

            $id = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->saveAuditRecord($editAuditRecord);

            $form = new AuditForm($this->getServiceLocator());

            $form->setData($request->getPost());

            if (isset($post['status'])) {
                //Save Audit Record Items to Database
                foreach ($post['status'] as $key => $rs) {

                    $auditRecordItemToEdit = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->getAuditRecordItemPerItemId($key);
                    $auditRecordItemToEdit->auditor_u_id = $identity['u_id'];
                    $auditRecordItemToEdit->audited_date = date("Y/m/d");
                    $auditRecordItemToEdit->item_status_id = $rs;

                    $auditRecordItemReturn = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->saveIndividualAuditRecordItem($auditRecordItemToEdit, $id, $post, $files);
                }
            }

            if ($isSignedOffCanClose) {
                $auditRecord = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->getAuditRecord($id);
                $this->cloneAudit($auditRecord);

                return $this->redirect()->toRoute('audit', array('controller' => 'audit', 'action' => 'list'));
            }

        }

        $audit_section_items = [];

        $audit_record = $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->getAuditRecord($id);
        $audit_record->reviewed_date = $audit_record->reviewed_date != null ? \DateTime::createFromFormat('Y-m-d',  $audit_record->reviewed_date)->format('m/d/Y') : '';
        $audit_record->auditor_approve_date = $audit_record->auditor_approve_date != null ? \DateTime::createFromFormat('Y-m-d',  $audit_record->auditor_approve_date)->format('m/d/Y') : '';

        if ($audit_record){
            $audit_record_type = $this->getServiceLocator()->get('Audit\Model\AuditRecordTypeTable')->getAuditRecordTypeByRecordQuestionTypeId($audit_record->audit_record_id, $question_type);

            $audit_record_sections = $this->getServiceLocator()->get('Audit\Model\AuditRecordSectionTable')->getAuditRecordSectionByTypeId($audit_record_type->audit_record_type_id);

            foreach ($audit_record_sections as $key => $audit_section) {
                $audit_items = [];
                
                $audit_record_itemsx = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->getAuditRecordItemsPerSectionId($audit_section->audit_record_section_id);
                
                foreach ($audit_record_itemsx as $keyx => $audit_item) {
                    $audit_item->_notes = $this->getServiceLocator()->get('Note\Model\NoteTable')->getNotes($audit_record->audit_record_id, \Note\Model\Note::NOTE_AUDIT_ITEM, $audit_item->audit_record_item_id);
                    array_push($audit_items, $audit_item);
                    
                }

                $audit_section->_audit_items = $audit_items;
                
                //Get Notes for Audit Section
                $remediationPlanNotes = [];
                $remediationPlanFiles = [];
                $remediation_plan_actions_by_policy = $this->getServiceLocator()->get('Assessment\Model\RemediationplanactionTable')->getRemediationplanactionsByPolicy($audit_record->rp_id, $audit_section->_performance_policy_numbers);//NOTE_RPA
                foreach ($remediation_plan_actions_by_policy as $individual_remediation_plan_action) {
                    if($individual_remediation_plan_action->rpa_id == 14535) {
                        $y = 1;
                    }
                    $notes_by_remediation_plan_action = $this->getServiceLocator()->get('Note\Model\NoteTable')->getNotes($individual_remediation_plan_action->rpa_id, \Note\Model\Note::NOTE_RPA, null, 'text');//NOTE_RPA
                    $files_by_remediation_plan_action = $this->getServiceLocator()->get('Note\Model\NoteTable')->getNotes($individual_remediation_plan_action->rpa_id, \Note\Model\Note::NOTE_RPA, null, 'file');//NOTE_RPA
                    foreach($notes_by_remediation_plan_action as $individual_plan_action_note) {
                        array_push($remediationPlanNotes, $individual_plan_action_note);
                        if($individual_plan_action_note->note_item_id == 14535) {
                            $y = 1;
                        }
                        if(is_array($individual_plan_action_note->_files) && (!empty($individual_plan_action_note->_files))){
                            $t = 1;
                        }
                    }
                    foreach($files_by_remediation_plan_action as $file) {
                        array_push($remediationPlanFiles, $file);
                    }
                }

                $audit_section->_performance_notes = $remediationPlanNotes;
                $audit_section->_policy_number_files = $remediationPlanFiles;
                array_push($audit_section_items, $audit_section);
            }
        }
        //_performance_description

        if (isset($post['saveclose']) && $post['saveclose']) {
            //Sava and Close button code 
            return $this->redirect()->toRoute('audit', array('controller' => 'audit', 'action' => 'list'));
        }

        $remediationPlanTableResponse = $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->getRemediationplan($audit_record->rp_id);

        $viewParams = array(
            'form' => $form,
            'aId' => $id,
            'companyRolesMsg' => $companyRolesMsg,
            'audit_record' => $audit_record,
            'auditRecordSectionItems' => $audit_section_items,
            'selectedReviewedId' => $selectedReviewedId,
            'selectedAuditorApprovedId' => $selectedAuditorApprovedId,
            'reviewApprove' => $this->getServiceLocator()->get('Assessment\Model\RemediationPlanTable')->getAuditReviewApprove($audit_record->rp_id),
            'auditorApprove' => $this->getServiceLocator()->get('Audit\Model\AuditRecordTable')->getAuditorApproveSignoff($audit_record->company_id),
            'isSignedOff' => $isSignedOff,
            'isSignedOffError' => $isSignedOffError,
            'audit_title' => $remediationPlanTableResponse->rp_type == \Assessment\Model\Assessment::TYPE_SECURITY_RISK ? 'Security' : 'Privacy',
            'eType' => (int)$audit_record->audit_status_id == 6 ? 'view' : ''
        );

        $viewModel = new ViewModel($viewParams);

        return $viewModel;

    }

    public function signAndLockAudit($post){
        $result = true;  // Allow signing and locking

        if ( empty($post['audit_review_approve_u_id']) && empty($post['audit_review_approve_initial']) && empty($post['audit_review_approve_date']) && empty($post['auditor_approve_u_id']) && empty($post['auditor_approve_initial']) && empty($post['auditor_approve_date'])) {
            $result = false;  // Dis-allow signing and locking
        }

        return $result;
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

    public function getAuditRecordTable()
    {
        if (!$this->auditRecordTable) {
            $sm = $this->getServiceLocator();
            $this->auditRecordTable = $sm->get('Audit\Model\AuditRecordTable');
        }
        return $this->auditRecordTable;
    }

}
