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
use Zend\View\Helper\ServerUrl;
use Zend\Paginator\Paginator;
use DataCrypt\DbCrypt;

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
    const TYPE_AUTH_SUCCESS = 11;
    const TYPE_AUTH_FAILED = 12;
    const TYPE_AUTH_LOCKED = 13;

    const ITEM_TYPE_COMPANY = 1;
    const ITEM_TYPE_CLIENT = 2;
    const ITEM_TYPE_BA = 3;
    const ITEM_TYPE_BREACHLOG = 4;
    const ITEM_TYPE_BRP = 5;
    const ITEM_TYPE_ASSESSMENT = 6;
    const ITEM_TYPE_RP = 7;
    const ITEM_TYPE_TL = 8;
    const ITEM_TYPE_SR = 9;
    const ITEM_TYPE_ST = 10;
    const ITEM_TYPE_DR = 11;
    const ITEM_TYPE_DTL = 12;
    const ITEM_TYPE_VL = 13;

    protected $tableGateway;
    protected $serviceLocator;
    protected $logDir;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->logDir = ROOT_PATH . '/public/data/logs/';
    }


    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    private function _getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));

        return $authService->getIdentity();
    }

    public function getLogs($onlyOpen = false, $lastId = 0, $upcoming = false)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('lo_id', 'lo_type', 'lo_item_type', 'lo_item_id', 'lo_u_id', 'lo_create_date'));
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

            $select->join(array('bl' => 'breach_logs'), new \Zend\Db\Sql\Expression('bl_id = lo_item_id AND lo_item_type = 4'), array('_bl_id' => 'bl_id', '_bl_name' => DbCrypt::decryptField('bl_name')), 'left');
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

        // echo $select->getSqlString($this->tableGateway->getAdapter()->getPlatform());
        // die;


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

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $lo_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $lo_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $lo_ip = $_SERVER['REMOTE_ADDR'];
        }

        $dataLog['lo_type'] = $type;
        $dataLog['lo_item_type'] = $itemType;
        $dataLog['lo_item_id'] = $itemId;
        $dataLog['lo_u_id'] = $identity['u_id'];
        $dataLog['lo_ip'] = $lo_ip;

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

    public function saveUserFileLog($msg)
    {
        $file = $this->getUserLogFile();

        if($file) {
            $helper = new ServerUrl;
            file_put_contents($file, date('Y-m-d:h:i:s') . "  -----  " . $msg . '; URL: ' . $helper->__invoke(true) . "\r\n", FILE_APPEND);
        }

        return true;
    }

    public function getUserLogFile()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));

        if(!$authService->hasIdentity()) {
            return null;
        }

        $identity = $authService->getIdentity();

        if(!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755);
        }

        $bad = array_merge(
                array_map('chr', range(0,31)),
                array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));

        return $this->logDir . 'logs_' . implode('_', array(str_replace($bad, "", $identity['u_firstname']), str_replace($bad, "", $identity['u_lastname']), $identity['u_id'], date('Y_m_d'))) . '.txt';
    }

    public function getCompanyUsersLoginHistory($companyId, $from, $to)
    {
        $select = $this->tableGateway->getSql()->select();

        $resultSetPrototype = new ResultSet();
        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            $resultSetPrototype
        );
        $select->columns(array('lo_id',
                               'lo_type',
                               'lo_ip',
                               'lo_create_date',
                               '_date' => new \Zend\Db\Sql\Expression('DATE_FORMAT(lo_create_date, "%Y-%m-%d")')
                               )
                        );
        $select->join(array('cl' => 'users'), new \Zend\Db\Sql\Expression('u_id = lo_item_id AND lo_item_type = ' . LogsTable::ITEM_TYPE_CLIENT), array('u_id', '_username' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')), 'inner');
        $select->join(array('c' => 'companies'), new \Zend\Db\Sql\Expression('c_id = u_company_id'), array(), 'inner');

        $select->where('c_id = ' . $companyId);
        $select->where('lo_type IN (' . implode(',', array(LogsTable::TYPE_AUTH_SUCCESS, LogsTable::TYPE_AUTH_FAILED, LogsTable::TYPE_AUTH_LOCKED)) . ')');
        $select->having('_date >= "' . $from . '"');
        $select->having('_date <= "' . $to . '"');

        $select->order('lo_create_date DESC');

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }
}