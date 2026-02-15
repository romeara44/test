<?php
namespace Audit\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class AuditInquiryTable implements ServiceLocatorAwareInterface
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

    public function getAuditInquiryPerPerformanceId($id)
    {
                
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('audit_performance_criteria_id = ' . $id);
        $select->where('active = 1');
        
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAuditInquiryPerId($id)
    {
        $id = (int)$id;
        
        $select = $this->tableGateway->getSql()->select();
        
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }
        
        return $row;
    }

    public function getOptionsRiskScores()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqo_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);
        
        $rows = array();
        
        foreach ($resultSet as $row) {
            $rows[$row->aqo_id] = $row->aqo_risk_score;
            $rows['calculate_risk_score'] = $row->calculate_risk_score;
            $rows['impact_value'] = $row->impact_value;
            $rows['likelihood_value'] = $row->likelihood_value;
        }
        
        return $rows;
    }
    
}