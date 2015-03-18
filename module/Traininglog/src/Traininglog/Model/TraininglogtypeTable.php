<?php
namespace Traininglog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

use Zend\Db\Sql\Expression;

class TraininglogtypeTable implements ServiceLocatorAwareInterface
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

    public function getTraininglogtypes()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();

        $resultSet = $this->tableGateway->selectWith($select);

        $types = array();
        foreach ($resultSet as $rs) {
            $types[$rs->tlt_id] = $rs->tlt_name;
        }

        return $types;
    }

    public function getTraininglogtypesByCompany($companyId = null)
    {
        $select = $this->tableGateway->getSql()->select();

        if($companyId) {
            $select->where('tlt_company_id = ' . $companyId . ' OR tlt_company_id IS NULL');
        } else {
            $select->where('tlt_company_id IS NULL');
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $types = array();
        foreach ($resultSet as $rs) {
            $types[$rs->tlt_id] = $rs->tlt_name;
        }

        return $types;
    }

    public function saveTraininglogtype($data)
    {
        $this->tableGateway->insert($data);

        return $this->tableGateway->lastInsertValue;;
    }

}