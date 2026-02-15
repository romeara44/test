<?php
namespace Client\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class HipaaSuiteModuleTable implements ServiceLocatorAwareInterface
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

    public function getHipaaSuiteModuleId($id)
    {
        $id = (int)$id;
        
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('hipaa_suite_module_id = ' . $id);
                
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }
        
        return $row;
    }

    public function getHipaaSuiteModules()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('active = 1');
        $select->order('display_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
    }
    
}