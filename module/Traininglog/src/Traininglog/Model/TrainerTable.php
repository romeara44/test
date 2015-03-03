<?php
namespace Traininglog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

use Zend\Db\Sql\Expression;

class TrainerTable implements ServiceLocatorAwareInterface
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

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getTrainers()
    {
        $select = $this->tableGateway->getSql()->select();

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getTrainersByCompany($companyId = null)
    {
        $select = $this->tableGateway->getSql()->select();

        if($companyId) {
            $select->where('tr_company_id = ' . $companyId);
        } else {
            $select->where('tr_company_id IS NULL');
        }

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function saveTrainer($data)
    {
        $this->tableGateway->insert($data);

        return $this->tableGateway->lastInsertValue;;
    }

    public function getTrainer($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('tr_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }
}