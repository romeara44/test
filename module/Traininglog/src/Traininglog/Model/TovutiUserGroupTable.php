<?php
// module/Traininglog/src/traininglog/Model/TovutiUserGroupTable.php
namespace Traininglog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Distinct;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;


use Traininglog\Model\TovutiUserGroup;

class TovutiUserGroupTable implements ServiceLocatorAwareInterface
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


    public function getTovutiUserGroup($id)
    {
        $id = (int)$id;

        $select = $this->tableGateway->getSql()->select();
        $select->where("id = {$id}");
        
        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getAllTovutiUserGroupsPerCompany($companyName)
    {
    
        $select = $this->tableGateway->getSql()->select();
        $select->where('company_name = ' . "'" . $companyName . "'");
        
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function saveTovutiUserGroupRecord(TovutiUserGroup $tovuti_user_group)
    {
        $id = (int)$tovuti_user_group->tovuti_user_group_id;

        $data = array();
        $tovutiUserGroupArray = (array) $tovuti_user_group;
        
        if ($id == 0)
        {
            $this->tableGateway->insert($tovutiUserGroupArray);
            $id = $this->tableGateway->lastInsertValue;
        }
        else
        {
            if ($this->getTovutiUserGroup($id))
            {
                $this->tableGateway->update($traineeArray, array('tovuti_user_group_id' => $id));
            }
            else
            {
                throw new \Exception('Tovuti User Group Id does not exist');
            }

        }

        return $id;
    }


}