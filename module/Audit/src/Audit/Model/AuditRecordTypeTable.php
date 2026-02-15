<?php
namespace Audit\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class AuditRecordTypeTable implements ServiceLocatorAwareInterface
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

    public function getAuditRecordType($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_type_id = '. $id);

        $resultSet = $this->tableGateway->selectWith($select);
        
        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
        
    }

    public function getAuditRecordTypeByAuditRecordId($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_record_id = '. $id);

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
        
    }


    public function getAuditRecordSectionPerSectionIdWithPerformanceCriteria($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();

        $select->join(array('u' => 'users'), 'auditor_u_id = u.u_id', array('_auditor_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)')), 'left');
        $select->join(array('u1' => 'users'), 'acceptor_u_id = u1.u_id', array('_acceptor_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'left');
        $select->join(array('u2' => 'users'), 'approved_u_id = u2.u_id', array('_approved_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');
        $select->join(array('u3' => 'users'), 'create_u_id = u3.u_id', array('_create_name' => new \Zend\Db\Sql\Expression('CONCAT(u3.u_firstname, " ", u3.u_lastname)')), 'left');
        $select->join(array('u4' => 'users'), 'modified_u_id = u4.u_id', array('_modified_name' => new \Zend\Db\Sql\Expression('CONCAT(u4.u_firstname, " ", u4.u_lastname)')), 'left');
        
        $select->join(array('per' => 'audit_performance_criteria'), 'audit_record_item.audit_performance_criteria_id = per.audit_performance_criteria_id', array('_audit_record_type_id' => 'audit_record_type_id'), 'left');
        $select->join(array('per1' => 'audit_performance_criteria'), 'audit_record_item.audit_performance_criteria_id = per1.audit_performance_criteria_id', array('_key_activity' => 'key_activity'), 'left');
        $select->join(array('per2' => 'audit_performance_criteria'), 'audit_record_item.audit_performance_criteria_id = per2.audit_performance_criteria_id', array('_description' => 'description'), 'left');
        
        $select->where('audit_performance_criteria_id = ' . $id);
        
        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
    }

    public function getAuditRecordTypeByRecordId($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();

        $select->join(array('u' => 'users'), 'auditor_u_id = u.u_id', array('_auditor_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)')), 'left');
        $select->join(array('u1' => 'users'), 'acceptor_u_id = u1.u_id', array('_acceptor_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'left');
        $select->join(array('u2' => 'users'), 'approved_u_id = u2.u_id', array('_approved_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');
        $select->join(array('u3' => 'users'), 'create_u_id = u3.u_id', array('_create_name' => new \Zend\Db\Sql\Expression('CONCAT(u3.u_firstname, " ", u3.u_lastname)')), 'left');
        $select->join(array('u4' => 'users'), 'modified_u_id = u4.u_id', array('_modified_name' => new \Zend\Db\Sql\Expression('CONCAT(u4.u_firstname, " ", u4.u_lastname)')), 'left');
        
        //$select->join(array('per' => 'audit_performance_criteria'), 'audit_record_type.audit_performance_criteria_id = per.audit_performance_criteria_id', array('_audit_question_type_id' => 'audit_question_type_id'), 'left');
        //$select->join(array('per1' => 'audit_performance_criteria'), 'audit_record_type.audit_performance_criteria_id = per1.audit_performance_criteria_id', array('_key_activity' => 'key_activity'), 'left');
        //$select->join(array('per2' => 'audit_performance_criteria'), 'audit_record_type.audit_performance_criteria_id = per2.audit_performance_criteria_id', array('_performance_description' => 'description'), 'left');
        
        $select->where('audit_record_type.audit_record_id = ' . $id);
        
        $resultSet = $this->tableGateway->selectWith($select);
        
        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getAuditRecordTypeByRecordQuestionTypeId($id, $questionId)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();

        $select->join(array('u' => 'users'), 'auditor_u_id = u.u_id', array('_auditor_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)')), 'left');
        $select->join(array('u1' => 'users'), 'acceptor_u_id = u1.u_id', array('_acceptor_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'left');
        $select->join(array('u2' => 'users'), 'approved_u_id = u2.u_id', array('_approved_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');
        $select->join(array('u3' => 'users'), 'create_u_id = u3.u_id', array('_create_name' => new \Zend\Db\Sql\Expression('CONCAT(u3.u_firstname, " ", u3.u_lastname)')), 'left');
        $select->join(array('u4' => 'users'), 'modified_u_id = u4.u_id', array('_modified_name' => new \Zend\Db\Sql\Expression('CONCAT(u4.u_firstname, " ", u4.u_lastname)')), 'left');
        
        if (isset($questionId)) {
            $select->where('audit_record_type.audit_record_id = ' . $id . '&& audit_record_type.audit_question_type_id = ' . $questionId);
        }
        else {
            $select->where('audit_record_type.audit_record_id = ' . $id);
        }
        
        $resultSet = $this->tableGateway->selectWith($select);
        
        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }
    
    public function saveType(AuditRecordType $audit_record_type)
    {
        $id = (int) $audit_record_type->audit_record_type_id;

        $data = array();

        $data['audit_question_type_id'] = $audit_record_type->audit_question_type_id;
        $data['audit_record_id'] = $audit_record_type->audit_record_id;
        $data['auditor_u_id'] = $audit_record_type->auditor_u_id;

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this-getAuditRecordType($id)) {
                //$data['aqo_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('audit_record_type_id' => $id));
            } else {
                throw new \Exception('Audit Record Type id does not exist');
            }
        }

        return $id;
    }

    public function insertType(AuditRecord $auditRecord, AuditRecordSectionMaster $auditRecordSectionMaster){
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $currentAuditRecordType = $this->getAuditRecordTypeByRecordQuestionTypeId($auditRecord->audit_record_id, $auditRecordSectionMaster->audit_record_type_id);

        if (!$currentAuditRecordType){
            
            $audit_record_type = new AuditRecordType();
            
            $audit_record_type->audit_question_type_id = $auditRecordSectionMaster->audit_record_type_id;
            $audit_record_type->audit_record_id = $auditRecord->audit_record_id;
            $audit_record_type->create_u_id = $identity['u_id'];
            $audit_record_type->modified_u_id = $identity['u_id'];
            
            $currentAuditRecordTypeId = $this->saveType($audit_record_type);

            $currentAuditRecordType = $this->getAuditRecordType($currentAuditRecordTypeId);
        }

        return $currentAuditRecordType;
    }

    public function cloneAuditRecordType(AuditRecordType $recordType, $auditRecordId) {
        
        $newAuditRecordType = new AuditRecordType();
        $newAuditRecordType->audit_question_type_id = $recordType->audit_question_type_id;
        $newAuditRecordType->audit_record_id = $auditRecordId;
        $newAuditRecordType->is_locked = 0;
        $savedAuditRecordTypeId = $this->saveType($newAuditRecordType);

        return $savedAuditRecordTypeId;
    }
}