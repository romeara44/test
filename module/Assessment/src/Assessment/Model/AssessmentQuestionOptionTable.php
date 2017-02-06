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
        }

        return $rows;
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