<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;


class RoleTable implements ServiceLocatorAwareInterface
{
    protected $tableGateway;
    protected $serviceLocator;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getRoles($uRoleId = null, $identityRoleid = null)
    {
        $select = $this->tableGateway->getSql()->select();

        if($uRoleId != \Admin\Model\User::ROLE_PARTIAL) {
            if(in_array($identityRoleid, array(\Admin\Model\User::ROLE_SENIOR_CONSULTANT, \Admin\Model\User::ROLE_CONSULTANT))) {
                $select->where('role_id IN (5, 6, 7)');
            } else {
                $select->where('role_id <> 1');
            }
        } else {
            $select->where('role_id IN (5, 7)');
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $roles = array();
        foreach ($resultSet as $rs) {
            $roles[$rs['role_id']] = $rs['role_name'];
        }

        return $roles;
    }

    public function getRoleName($roleId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('role_id = ' . (int) $roleId);
        $resultSet = $this->tableGateway->selectWith($select);

        $roles = array();
        foreach ($resultSet as $rs) {
            return $rs['role_name'];
        }

        return '';
    }

}