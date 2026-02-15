<?php
namespace Audit\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Note\Model\Note;

Use Zend\Db\Sql\Sql;
Use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class AuditRecordItemTable implements ServiceLocatorAwareInterface
{
    protected $tableGateway;
    protected $serviceLocator;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getAuditRecordItemPerItemId($id){
        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_item_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getAuditRecordItemPerRecordId($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_id = '. $id);

        $resultSet = $this->tableGateway->selectWith($select);
        
        $rows = array();
        foreach ($resultSet as $row) {
            $rows[$row->audit_record_id] = $row->aqo_risk_score;
        }

        return $rows;
        
    }

    public function getAuditRecordItemsPerRecordId($id)
    {
        $id = (int)$id;

        //$select = $this->tableGateway->getSql()->select();
        //$select->join(array('u2' => 'users'), 'auditor_u_id = u2.u_id', array('_auditor_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');

        //$select->join(array('ars' => 'audit_record_section'), 'audit_record_item.audit_record_section_id = ars.audit_record_section_id', array('_audit_performance_criteria_id' => 'audit_performance_criteria_id'), 'left');
        //$select->join(array('apc' => 'audit_performance_criteria'), 'audit_record_section.audit_performance_criteria_id = apc.audit_performance_criteria_id', array('_audit_question_type_id' => 'audit_question_type_id'), 'left');
        //$select = new Select();
        //$select->from('audit_record_item')
        //    ->columns(array('audit_record_item.*', 'ari_audit_record_section_id' => 'audit_record_section.audit_record_section_id'))
        //    ->join('audit_record_section', 'audit_record_item.audit_record_section_id' = 'audit_record_section.audit_record_section_id');
        
        //$select->from('audit_record_item');
        //$select->columns(array('audit_record_item.*', 'ari_audit_record_section_id' => 'audit_record_section.audit_record_section_id'));
        //$select->joinLeft(array('audit_record_section', 'audit_record_item.audit_record_section_id' = 'audit_record_section.audit_record_section_id');
        //$select = $db->select();

        //$select()->from( array('ari' => 'audit_record_item'));
        
        

        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($adapter);

        $select = $sql->select()
            ->from(array('ari' => 'audit_record_item'))
            ->columns([
                'audit_record_item_id' => 'audit_record_item_id', 
                'rpa_id' => 'rpa_id',
                'audit_inquiry_id' => 'audit_inquiry_id',
                'auditor_u_id' => 'auditor_u_id',
                'audited_date' => 'audited_date',
                'item_status_id' => 'item_status_id',
                'audit_record_section_id' => 'audit_record_section_id'
                
            ])
            ->join(
                ['ars' => 'audit_record_section'],
                'ars.audit_record_section_id = ari.audit_record_section_id',
                ['audit_record_section_id' => 'audit_record_section_id']
            )
            ->join(
                ['apc' => 'audit_performance_criteria'],
                'apc.audit_performance_criteria_id = ars.audit_performance_criteria_id',
                [
                    '_audit_performance_criteria_id' => 'audit_performance_criteria_id',
                    '_audit_question_type_id' => 'audit_question_type_id',
                    '_key_activity' => 'key_activity',
                    '_performance_description' => 'description'
                ]
            )
            ->join(
                ['ai' => 'audit_inquiry'],
                'ai.audit_inquiry_id = ari.audit_inquiry_id',
                [
                    '_policy_number' => 'policy_number',
                    '_inquiry_description' => 'description',
                    '_check_mark_required' => 'check_mark_required'
                ]
            );

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        return $resultSet;
    }

    public function getAuditRecordItemsPerSectionId($id)
    {
        $id = (int)$id;
        
        $select = $this->tableGateway->getSql()->select();
        $select->join(array('ai1' => 'audit_inquiry'), 'audit_record_item.audit_inquiry_id = ai1.audit_inquiry_id', array('_policy_number' => 'policy_number'), 'left');
        $select->join(array('ai2' => 'audit_inquiry'), 'audit_record_item.audit_inquiry_id = ai2.audit_inquiry_id', array('_inquiry_description' => 'description'), 'left');
        $select->join(array('ai3' => 'audit_inquiry'), 'audit_record_item.audit_inquiry_id = ai3.audit_inquiry_id', array('_check_mark_required' => 'check_mark_required'), 'left');

        $select->where('audit_record_section_id = '. $id);

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
        
    }

    public function saveAuditRecordItemStatus($id, $value)
    {
        $id1 = (int)$id;

        $data = array();

        $data['item_status_id'] = $value;
        
        $this->tableGateway->update($data, array('audit_record_item_id' => $id1));
        
        return $id1;
        
    }

    public function saveAuditRecordItem(AuditRecordItem $audit_record_item)
    {
        $id = (int)$audit_record_item->audit_record_item_id;

        $data = array();

        $data['audit_inquiry_id'] = $audit_record_item->audit_inquiry_id;
        $data['auditor_u_id'] = $audit_record_item->auditor_u_id;
        $data['audited_date'] = $audit_record_item->audited_date;
        $data['item_status_id'] = $audit_record_item->item_status_id;
        $data['audit_record_section_id'] = $audit_record_item->audit_record_section_id;


        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            //if ($this->getAuditRecordSectionPerSectionId($id)) {
            if ($this->getAuditRecordItemPerItemId($id)) {
                //$data['aqo_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('audit_record_item_id' => $id));
            } else {
                throw new \Exception('Audit Record Item Id does not exist');
            }
        }

        return $id;
        
    }

    public function saveIndividualAuditRecordItem(AuditRecordItem $audit_record_item, $audit_record_id, $post, $files)
    {
        $id = (int)$audit_record_item->audit_record_item_id;

        $data = array();

        $data['auditor_u_id'] = $audit_record_item->auditor_u_id;
        $data['audited_date'] = $audit_record_item->audited_date;
        $data['item_status_id'] = $audit_record_item->item_status_id;

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            $this->tableGateway->update($data, array('audit_record_item_id' => $id));
        }

        ////////////////////////////
        if (isset($files['notesFiles'][$id]) || ($post['notes'][$id] != '')) {
                    
            $postNote['note_item_type'] = \Note\Model\Note::NOTE_AUDIT_ITEM;
            $postNote['note_item_id'] = $audit_record_id;  //Set to AuditRecordId
            $postNote['note_subitem_id'] = $id;

            // save text note
            if($post['notes'][$id] != '')
            {
                $note = new Note();
                $postNote['note_text'] = $post['notes'][$id];
                $note->exchangeArray($postNote);
                $this->getServiceLocator()->get('Note\Model\NoteTable')->setServiceLocator($this->getServiceLocator());
                $this->getServiceLocator()->get('Note\Model\NoteTable')->saveNote($note);
            }
            
            // save file note
            if (isset($files['notesFiles'][$id]))
            {
                $note = new Note();
                $postNote['note_text'] = '';
                $note->exchangeArray($postNote);
                $notesFiles = $files['notesFiles'][$id];
                
                $this->getServiceLocator()->get('Note\Model\NoteTable')->setServiceLocator($this->getServiceLocator());
                $this->getServiceLocator()->get('Note\Model\NoteTable')->saveNote($note, array('notesFiles' => $notesFiles));
            }
        }
        ////////////////////////////

        return $id;
        
    }

    public function insertItems(AuditRecordSectionMaster $auditRecordSectionMaster, $savedAuditRecordSection){
        $activeAuditRecordItems = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemMasterTable')->getActiveAuditRecordItems($auditRecordSectionMaster->audit_record_section_master_id);
        
        foreach ($activeAuditRecordItems as $key => $value1) {
            
            $audit_record_item = new AuditRecordItem();
            
            $audit_record_item->audit_inquiry_id = $value1->audit_inquiry_id;
            $audit_record_item->item_status_id = 4; //Pass =1, Fail = 2, Substitue = 3, Blank = 4
            $audit_record_item->audit_record_section_id = $savedAuditRecordSection;

            $savedAuditRecordItem = $this->saveAuditRecordItem($audit_record_item);

        }

        return $savedAuditRecordItem;
    }

    public function cloneAuditRecordItem(AuditRecordItem $sourceAuditRecordItem, $savedAuditRecordSectionId) {
        $auditRecordItem = new AuditRecordItem();

        $auditRecordItem->audit_inquiry_id = $sourceAuditRecordItem->audit_inquiry_id;
        $auditRecordItem->auditor_u_id = $sourceAuditRecordItem->auditor_u_id;
        $auditRecordItem->audited_date = $sourceAuditRecordItem->audited_date;
        $auditRecordItem->item_status_id = $sourceAuditRecordItem->item_status_id;
        $auditRecordItem->audit_record_section_id = $savedAuditRecordSectionId;

        $savedAuditRecordItemId = $this->saveAuditRecordItem($auditRecordItem);

        return $savedAuditRecordItemId;
    }

}