<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class StatusLookupTable implements ServiceLocatorAwareInterface
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

    public function getCalculatedRiskScoreByQuestion($aqo_Id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqo_id = ' . $aqo_Id);
        $resultSet = $this->tableGateway->selectWith($select);
        
        $risk_score = 0;

        foreach ($resultSet as $row) {
            if ($row->calculate_risk_score == '1'){
                
                $risk_score = $row->impact_value * $row->likelihood_value;
            }
            else {
                $twentyFivePercentOfLikelihood = ((float)$row->likelihood_value * 0.25);
                $risk_score = $row->impact_value * ($twentyFivePercentOfLikelihood);
            }
            
        }
        
        return $risk_score;
    }

}