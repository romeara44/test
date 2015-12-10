<?php
namespace Client\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use Zend\Db\Sql\Expression;

class CompanyRolesTable implements ServiceLocatorAwareInterface
{
    const ROLES_COUNT = 9;

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

    public function getCompanyRoles($cId)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where('cr_c_id = ' . $cId);

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getExistsCompanyRoles($cId = null)
    {
        $roles = array();

        if(!$cId) {
            return $roles;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->where('cr_c_id = ' . $cId);

        $resultSet = $this->tableGateway->selectWith($select);

        
        foreach ($resultSet as $rs) {
            $roles[$rs->cr_ar_id] = $rs->cr_u_id;
        }

        return $roles;
    }

    public function checkFillCompanyRoles($cId = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if($identity['u_role_id'] == User::ROLE_PARTIAL || !$cId) {
            return true;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->where('cr_c_id = ' . $cId);
        $select->where('cr_u_id != 0');

        $resultSet = $this->tableGateway->selectWith($select)->count();

        return $resultSet == self::ROLES_COUNT;
    }

    public function saveCompanyRole(CompanyRoles $cr)
    {
        $data = array();

        $data['cr_c_id']  = $cr->cr_c_id;
        $data['cr_ar_id'] = $cr->cr_ar_id;
        $data['cr_u_id'] = $cr->cr_u_id;
        $id               = (int)$this->getCompanyRoleIdByCompanyAndRole($cr->cr_c_id, $cr->cr_ar_id);

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getCompanyRole($id)) {
                $this->tableGateway->update($data, array('cr_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function getCompanyRole($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('cr_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getCompanyRoleIdByCompanyAndRole($cId, $rId)
    {
        $cId  = (int) $cId;
        $rId  = (int) $rId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('cr_c_id = ' . $cId);
        $select->where('cr_ar_id = ' . $rId);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row->cr_id;
    }

    public function getCompanyRoleByCompanyAndRole($cId, $rId)
    {
        $cId  = (int) $cId;
        $rId  = (int) $rId;

        $select = $this->tableGateway->getSql()->select();
        $select->where('cr_c_id = ' . $cId);
        $select->where('cr_ar_id = ' . $rId);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }
}