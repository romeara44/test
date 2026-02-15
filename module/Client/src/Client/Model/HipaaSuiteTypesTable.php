<?php

namespace Client\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HipaaSuiteTypesTable implements ServiceLocatorAwareInterface
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

    public function getHipaaSuiteTypes()
    {
        $select = $this->tableGateway->getSql()->select();

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

}