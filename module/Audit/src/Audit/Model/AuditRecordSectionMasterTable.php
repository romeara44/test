<?php
namespace Audit\Model;

use Audit\Model\AuditRecord;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;


class AuditRecordSectionMasterTable implements ServiceLocatorAwareInterface
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

    public function getActiveAuditRecordSections($remediationType)
    {
        $recordType = \Assessment\Model\Assessment::TYPE_SECURITY_RISK;

        if ($remediationType == \Assessment\Model\Assessment::TYPE_SECURITY_RISK) {
            $recordType = 3;
        }

        if ($remediationType == \Assessment\Model\Assessment::TYPE_PRIVACY_RISK) {
            $recordType = 4;
        }
        
        $select = $this->tableGateway->getSql()->select();
        //$select->where('active = 1 && audit_record_type_id = ' . $recordType);
        $select->where->equalTo('active', 1);
        $select->where->and;
        $select->where->equalTo('audit_record_type_id', $recordType);


        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
        
    }

    public function insertNewSectionMasterItems(AuditRecord $auditRecord, $recordType){
        //Insert Audit Section
        $activeAuditRecordSections = $this->getActiveAuditRecordSections($recordType);
        
        foreach ($activeAuditRecordSections as $key => $value){
            
            $currentAuditRecordType = $this->getServiceLocator()->get('Audit\Model\AuditRecordTypeTable')->insertType($auditRecord, $value);
            
            $savedAuditRecordSection = $this->getServiceLocator()->get('Audit\Model\AuditRecordSectionTable')->insertSection($currentAuditRecordType, $value);
            
            $auditRecordItemMaster = $this->getServiceLocator()->get('Audit\Model\AuditRecordItemTable')->insertItems($value, $savedAuditRecordSection);

        }
    }
    
    
}