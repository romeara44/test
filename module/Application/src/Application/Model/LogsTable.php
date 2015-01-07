<?php
namespace Application\Model;
use Zend\Db\TableGateway\TableGateway;

use Admin\Model\User;
use Zend\Db\Sql\Sql;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;

class LogsTable
{
    const TYPE_OPEN = 1;
    const TYPE_UPLOAD = 2;
    const TYPE_ASSIGN = 3;
    const TYPE_SIGNEDOFF = 4;
    const TYPE_ADD = 5;
    const TYPE_EDIT = 6;
    const TYPE_DELETE = 7;
    const TYPE_UPLOADED_ROLES = 8;
    const TYPE_UPLOADED_INVENTORY = 9;
    const TYPE_REOPEN = 10;

    const ITEM_TYPE_COMPANY = 1;
    const ITEM_TYPE_CLIENT = 2;
    const ITEM_TYPE_BA = 3;
    const ITEM_TYPE_BREACHLOG = 4;
    const ITEM_TYPE_BRP = 5;
    const ITEM_TYPE_ASSESSMENT = 6;
    const ITEM_TYPE_RP = 7;

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

    public function getLogs($onlyOpen = false, $lastId = 0, $upcoming = false)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        if ($onlyOpen) {
            $select->where('lo_type = 1');
        } elseif ($upcoming) {
            $select->where('lo_type = 5 AND lo_item_type = 7');

        } else {
            $select->where('lo_type <> 1');
        }


        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $select->where('lo_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('lo_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $select->where('lo_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SALES_REP) {
            $select->where('lo_u_id = ' . $identity['u_id']);
        }


        if (!$upcoming) {
            $select->join(array('c' => 'companies'), new \Zend\Db\Sql\Expression('c_id = lo_item_id AND lo_item_type = 1'), array('_c_id' => 'c_id', '_c_name' => 'c_name'), 'left');
            $select->join(array('cl' => 'users'), new \Zend\Db\Sql\Expression('u_id = lo_item_id AND lo_item_type = 2'), array('_cl_id' => 'u_id', '_cl_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')), 'left');

            $select->join(array('ba' => 'business_associates'), new \Zend\Db\Sql\Expression('ba_id = lo_item_id AND lo_item_type = 3'), array('_ba_id' => 'ba_id', '_ba_name' => 'ba_name'), 'left');

            $select->join(array('bl' => 'breach_logs'), new \Zend\Db\Sql\Expression('bl_id = lo_item_id AND lo_item_type = 4'), array('_bl_id' => 'bl_id', '_bl_name' => 'bl_name'), 'left');
            $select->join(array('blc' => 'companies'), new \Zend\Db\Sql\Expression('blc.c_id = bl.bl_c_id AND lo_item_type = 4'), array('_blc_id' => 'c_id', '_blc_name' => 'c_name'), 'left');

            $select->join(array('brp' => 'breach_remediation_plans'), new \Zend\Db\Sql\Expression('brp_id = lo_item_id AND lo_item_type = 5'), array('_brp_id' => 'brp_id'), 'left');
            $select->join(array('brpc' => 'companies'), new \Zend\Db\Sql\Expression('brpc.c_id = brp.brp_c_id AND lo_item_type = 5'), array('_brpc_id' => 'c_id', '_brpc_name' => 'c_name'), 'left');

            $select->join(array('a' => 'assessments'), new \Zend\Db\Sql\Expression('a_id = lo_item_id AND lo_item_type = 6'), array('_a_id' => 'a_id'), 'left');
            $select->join(array('ac' => 'companies'), new \Zend\Db\Sql\Expression('ac.c_id = a.a_c_id AND lo_item_type = 6'), array('_ac_id' => 'c_id', '_ac_name' => 'c_name'), 'left');
        }

        $select->join(array('rp' => 'remediation_plans'), new \Zend\Db\Sql\Expression('rp_id = lo_item_id AND lo_item_type = 7'), array('_rp_id' => 'rp_id'), 'left');
        $select->join(array('rpc' => 'companies'), new \Zend\Db\Sql\Expression('rpc.c_id = rp.rp_c_id AND lo_item_type = 7'), array('_rpc_id' => 'c_id', '_rpc_name' => 'c_name'), 'left');

        if ($lastId) {
            $select->where('lo_id < ' . $lastId);
        }

        if ($upcoming) {
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $sql = new Sql($adapter);

            $assessmentsSelect = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getAssessmentsToDo($identity, $lastId);

            $selectU = $sql->select();
            $selectU->from(array('sel3and4' => $select));
            $selectU->combine($assessmentsSelect, 'union', 'all');

            $select = clone $selectU;
        }

        $select->order('lo_id DESC');

        $select->limit(10);

        if ($upcoming) {
            $resultSetPrototype = new ResultSet();
            $paginatorAdapter = new DbSelect(
                $select,
                $adapter,
                $resultSetPrototype
            );
            $resultSet = $paginatorAdapter->getItems(0, 10);
        } else {
            $resultSet = $this->tableGateway->selectWith($select);
        }

        //echo $select->getSqlString($this->tableGateway->getAdapter()->getPlatform());
        //die;


        return $resultSet;
    }

    public function saveLog($type, $itemType, $itemId)
    {
        if (!$itemId) {
            return;
        }
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $dataLog['lo_type'] = $type;
        $dataLog['lo_item_type'] = $itemType;
        $dataLog['lo_item_id'] = $itemId;
        $dataLog['lo_u_id'] = $identity['u_id'];

        $this->tableGateway->insert($dataLog);
        $id = $this->tableGateway->lastInsertValue;

        return $id;
    }

    public function getLog($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('lo_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }


}