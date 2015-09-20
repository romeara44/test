<?php
namespace Breachlog\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class BreachlogTable implements ServiceLocatorAwareInterface
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

    public function getBreachlogs($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('breach_logs');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Breachlog());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            } else {
                $select->where('bl_active = 1');
                if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                    $select->where('bl_consultant_u_id = ' . $identity['u_id']);
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $select->where('bl_consultant_u_id IN (' . implode(',', $ids) . ')');
                } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
                    $select->where('bl_c_id = ' . $identity['u_company_id']);
                }
            }

            $select->join(array('c' => 'companies'), 'bl_c_id = c_id', array('_client_name' => 'c_name'), 'left');
            ///////////////

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getSearchResultsSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('bl_active = 1');
        $select->where('bl_name LIKE "%' . $searchValue . '%"');

        $select->columns(array('_id' => 'bl_id', '_name' => 'bl_name', '_type' => new \Zend\Db\Sql\Expression('CONCAT("breachlog")')));

        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $select->where('bl_consultant_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('bl_consultant_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $select->where('bl_c_id = ' . $identity['u_company_id']);
        }

        return $select;
    }

    public function getForReport($conditionNum = 0, $reportable = 0, $uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('bl_active = 1');
        $condition = isset(\Admin\Model\UserTable::$reportCondition[$conditionNum]) ? \Admin\Model\UserTable::$reportCondition[$conditionNum] : null;

        if ($condition != '') {
            $condition = str_replace('?', 'bl_create_date', $condition);
            $select->where($condition);
        }

        $select->columns(array('_client_name' => new \Zend\Db\Sql\Expression('COUNT(bl_id)')));

        if (!$reportable) {
            $select->where("(bl_reportable = 0 OR bl_reportable IS NULL)");
        } else {
            $select->where("bl_reportable = 1");
        }

        if ($uId) {
            $identity = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($uId);

            if ($identity->u_role_id == User::ROLE_CONSULTANT) {
                $select->where('bl_consultant_u_id = ' . $identity->u_id);
            } elseif ($identity->u_role_id == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity->u_id);
                $ids[] = $identity->u_id;
                $select->where('bl_consultant_u_id IN (' . implode(',', $ids) . ')');
            }
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return ($row->_client_name);
    }

    public function getBreachlog($id)
    {
        $id     = (int) $id;
        $rowset = $this->tableGateway->select(array('bl_id' => $id));
        $row    = $rowset->current();

        if (!$row) {
            return false;
        }

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('blrg' => 'breach_logs_regulations'), new \Zend\Db\Sql\Expression('bl_id = blrg.blrg_bl_id'), array('_bl_blrg_id' => new \Zend\Db\Sql\Expression('blrg.blrg_id'), '_bl_blrg_rg_id' => new \Zend\Db\Sql\Expression('blrg.blrg_rg_id')), 'inner');
        $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('blrg.blrg_rg_id = rg.rg_id'), array('_bl_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'inner');
        $select->where("bl_id =" . $id);

        $regulations = $this->tableGateway->selectWith($select);

        foreach ($regulations as $rs) {
            $row->_bl_cur_regulations[$rs->_bl_blrg_id] = $rs->_bl_blrg_rg_id;
        }

        return $row;
    }

    public function saveBreachlog(Breachlog $bl)
    {
        $data = array(
            'bl_c_id' => $bl->bl_c_id,
            'bl_name' => $bl->bl_name,
            'bl_invest_led_by' => $bl->bl_invest_led_by,
            'bl_date_of_occurrence' => $bl->bl_date_of_occurrence,
            'bl_date_invest_start' => $bl->bl_date_invest_start,
            'bl_date_invest_complete' => $bl->bl_date_invest_complete,
            'bl_size' => $bl->bl_size,
            'bl_description' => $bl->bl_description,
            'bl_initials_approver' => $bl->bl_initials_approver,
            'bl_initials' => $bl->bl_initials,
            'bl_reportable' => $bl->bl_reportable,
            'bl_approver_u_id' => $bl->bl_approver_u_id,
            'bl_accepter_u_id' => $bl->bl_accepter_u_id
        );

        $id = (int) $bl->bl_id;

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (!$id) {
            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
                $data['bl_consultant_u_id'] = $bl->bl_consultant_u_id;
            } else if ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $data['bl_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
                $data['bl_c_id'] = $identity['u_company_id'];
            }

            $data['bl_create_u_id'] = $identity['u_id'];
            $data['bl_create_date'] = new \Zend\Db\Sql\Expression('NOW()');
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);
        } else {
            if ($this->getBreachlog($id)) {
                $data['bl_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                $data['bl_update_u_id'] = $identity['u_id'];
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);
                $this->tableGateway->update($data, array('bl_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        $breachlogRegulationTable = $this->getServiceLocator()->get('Breachlog\Model\BreachlogRegulationTable');

        $breachlogRegulationTable->deleteByBreachlogId($id);

        if($bl->_bl_cur_regulations) {
            foreach ($bl->_bl_cur_regulations as $_bl_cur_regulation) {
                if($_bl_cur_regulation == -1 && $bl->_regulation) {
                    $regulationData = array( 'rg_pp_name'     => $bl->_regulation[-1]['rg_pp_name']
                                           , 'rg_pp_number'   => $bl->_regulation[-1]['rg_pp_number']
                                           , 'rg_number'      => $bl->_regulation[-1]['rg_number']
                                           , 'rg_description' => $bl->_regulation[-1]['rg_description']
                                           , 'rg_u_owner_id'  => $identity['u_id']
                                           );

                    $rgId = $this->getServiceLocator()->get('Traininglog\Model\RegulationTable')->saveRegulation($regulationData);

                    $breachlogRegulationTable->saveBreachlogRegulation(array('blrg_bl_id' => $id, 'blrg_rg_id' => $rgId));
                } else {
                    $breachlogRegulationTable->saveBreachlogRegulation(array('blrg_bl_id' => $id, 'blrg_rg_id' => $_bl_cur_regulation));
                }
            }
        }

        return $id;
    }

    public function deleteBreachlog($id)
    {
        $data['bl_id'] = $id;
        $data['bl_active'] = 0;
        $this->tableGateway->update($data, array('bl_id' => $id));
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_BREACHLOG, $id);
        return true;
    }

    public function unarchiveBreachlog($id)
    {
        $data['bl_id'] = $id;
        $data['bl_active'] = 1;
        $this->tableGateway->update($data, array('bl_id' => $id));
        return true;
    }


    public function deleteBlByCompanyId($cId = 0, $value = 0)
    {
        $cId = (int) $cId;
        $data['bl_active'] = $value;
        $this->tableGateway->update($data, array('bl_c_id' => $cId));
        return true;
    }


}