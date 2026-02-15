<?php
namespace Client\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;


Use Zend\Db\Sql\Sql;
Use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;

class HipaaSuiteModuleRoleTable implements ServiceLocatorAwareInterface
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

    public function getHipaaSuiteModuleRoleId($id)
    {
        $id = (int)$id;
        
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('hipaa_suite_module_role_id = ' . $id);
                
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }
        
        return $row;
    }
    

    public function getHipaaSuiteModuleRolesPerModule($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('cmr' => 'company_master_role'), 'hipaa_suite_module_role.company_master_role_id = cmr.company_master_role_id', array('_role_name' => 'role_name'), 'left');
        
        $select->where('hipaa_suite_module_id = ' . $id);
        $select->where('hipaa_suite_module_role.active = 1');
        $select->order('hipaa_suite_module_role.display_order ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    
    public function getListOfRoles($moduleId) {
        //Retriev the list of Audit Roles Code
        $auditRoleResult = $this->getHipaaSuiteModuleRolesPerModule($moduleId);

        $auditRoles = array();

        foreach ($auditRoleResult as $row) {

            $hipaaSuiteModuleRole = new HipaaSuiteModuleRole();//array_push($auditRoles, $row->_role_name);
            $hipaaSuiteModuleRole->hipaa_suite_module_role_id = $row->hipaa_suite_module_role_id;
            $hipaaSuiteModuleRole->hipaa_suite_module_id = $row->hipaa_suite_module_id;
            $hipaaSuiteModuleRole->company_master_role_id = $row->company_master_role_id;
            $hipaaSuiteModuleRole->active = $row->active;
            $hipaaSuiteModuleRole->display_order = $row->display_order;
            $hipaaSuiteModuleRole->_role_name = $row->_role_name;

            array_push($auditRoles, $hipaaSuiteModuleRole);

        }

        return $auditRoles;

    }
    
}  