<?php
namespace Audit\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class AuditPerformanceCriteriaTable implements ServiceLocatorAwareInterface
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

    public function getAuditPerformanceCriteriaPerIdOld($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('active = 1');
        $select->where('audit_performance_criteria_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);
        
        $rows = array();
        foreach ($resultSet as $row) {
            $rows[$row->audit_performance_criteria_id] = $row->audit_performance_criteria_id;
            $rows[$row->audit_question_type_id] = $row->audit_question_type_id;
            $rows[$row->key_activity] = $row->key_activity;
            $rows[$row->description] = $row->description;
            $rows[$row->active] = $row->active;
            $rows[$row->display_order] = $row->display_order;

        }

        return $rows;
        
    }

    public function getAuditPerformanceCriteriaPerId($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_performance_criteria_id = ' . $id);
        $select->where('active = 1');
        
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }
    
}