<?php
namespace Audit\Model;

use Audit\Model\AuditRecordItem;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

Use Zend\Db\Sql\Sql;
Use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;


class AuditRecordItemMasterTable implements ServiceLocatorAwareInterface
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

    public function getActiveAuditRecordItems($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('active = 1 && audit_record_section_master_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
        
    }

}