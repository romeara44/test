<?php
namespace Traininglog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

use Zend\Db\Sql\Expression;

class TrainingReportTable implements ServiceLocatorAwareInterface
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

    public function getTrainingReportUrlByCompany($id)
    {
        // $authService = new \Zend\Authentication\AuthenticationService();
        // $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        // $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        $select->where('company_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $types = array();
        // foreach ($resultSet as $rs) {
        //     $types[$rs->tlt_id] = $rs->tlt_name;
        // }
        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }


    // public function saveTraininglogtype($data)
    // {
    //     $this->tableGateway->insert($data);

    //     return $this->tableGateway->lastInsertValue;;
    // }

}