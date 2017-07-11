<?php
namespace Itassetinventory\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ItAssetInventoryItemTypeTable implements ServiceLocatorAwareInterface
{
    const TYPE_MANUAL_ENTRY = 6;

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

    public function getAllActive()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('iaiit_active = 1');

        $select->order('iaiit_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }
}