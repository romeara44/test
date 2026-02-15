<?php
namespace Client\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class CompanyMasterRoleTable implements ServiceLocatorAwareInterface
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

    public function getCompanyMasterRoleId($id)
    {
        $id = (int)$id;
        
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('company_master_role_id = ' . $id);
                
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }
        
        return $row;
    }

    public function getCompanyMasterRoles()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('active = 1');

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
    }

    public function getCompanyMasterRoleIdPerRoleName($roleName)
    {
        $select = $this->tableGateway->getSql()->select();
        
        $select->where("role_name = '" . $roleName . "'");
        $select->where('active = 1');
                
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }
        
        return $row->company_master_role_id;
    }
    
}