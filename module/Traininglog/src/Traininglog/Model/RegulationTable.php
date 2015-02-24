<?php
namespace Traininglog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

use Zend\Db\Sql\Expression;

class RegulationTable implements ServiceLocatorAwareInterface
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

    public function getRegulations()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();

        $select->where("(rg_u_owner_id = " . $identity['u_id'] . ' OR rg_u_owner_id IS NULL)');

        $resultSet = $this->tableGateway->selectWith($select);

        $regulations = array();
        
        foreach ($resultSet as $rs) {
            $regulations[$rs->rg_id] = $rs;
        }
        return $regulations;
    }

    public function saveRegulation($data)
    {
        $this->tableGateway->insert($data);

        return $this->tableGateway->lastInsertValue;;
    }

}