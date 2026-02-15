<?php
namespace Assessment\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class AssessmentQuestionOptionTable implements ServiceLocatorAwareInterface
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

    public function getOption($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('aqo_id = ' . $id);
        $select->where('aqo_active = 1');

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

    public function getOptionsRiskScoresByQuestion($aqo_Id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('aqo_id = ' . $aqo_Id);

        $resultSet = $this->tableGateway->selectWith($select);

        $rows = array();
        foreach ($resultSet as $row) {
            $rows[$row->aqo_id] = $row->aqo_risk_score;
            $rows['calculate_risk_score'] = $row->calculate_risk_score;
            $rows['impact_value'] = $row->impact_value;
            $rows['likelihood_value'] = $row->likelihood_value;
            $rows['aqo_title'] = $row->aqo_title;
        }

        return $rows;
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

    public function saveOption(AssessmentQuestionOption $aqo)
    {
        $id = (int) $aqo->aqo_id;

        $data = array();

        $data['aqo_aq_id'] = $aqo->aqo_aq_id;
        $data['aqo_title'] = $aqo->aqo_title;
        $data['aqo_order'] = $aqo->aqo_order;
        $data['aqo_risk_score'] = $aqo->aqo_risk_score;

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getOption($id)) {
                $data['aqo_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('aqo_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }
}