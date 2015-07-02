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
use Breachlog\Model\Breachremediationplan;

class BreachremediationplanTable implements ServiceLocatorAwareInterface
{
    protected $tableGateway;
    protected $serviceLocator;
    private $_secureDBKey;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->_secureDBKey  = (new \Zend\Session\Container('application_vars'))->storage['secure_db_key'];
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    private function _encryptValue($value, $expression = true) {
        return $expression ? new \Zend\Db\Sql\Expression('AES_ENCRYPT("' . $value . '", "' . $this->_secureDBKey . '")') : 'AES_ENCRYPT("' . $value . '", "' . $this->_secureDBKey . '")';
    }

    private function _decryptField($field, $expression = true) {
        return $expression ? new \Zend\Db\Sql\Expression('AES_DECRYPT(' . $field . ', "' . $this->_secureDBKey . '")') : 'AES_DECRYPT(' . $field . ', "' . $this->_secureDBKey . '")';
    }

    private function _getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));

        return $authService->getIdentity();
    }

    public function getBreachremediationplans($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();

            $select->columns(array('brp_id'                 => 'brp_id',
                                   'brp_version_index'      => $this->_decryptField('brp_version_index'),
                                   'brp_version_index_item' => $this->_decryptField('brp_version_index_item'),
                                   'brp_writable'           => 'brp_writable',
                                   'brp_bl_id'              => 'brp_bl_id',
                                   'brp_c_id'               => 'brp_c_id',
                                   'brp_consultant_u_id'    => 'brp_consultant_u_id',
                                   'brp_performed_u_id'     => 'brp_performed_u_id',
                                   'brp_approver_u_id'      => 'brp_approver_u_id',
                                   'brp_accepter_u_id'      => 'brp_accepter_u_id',
                                   'brp_create_u_id'        => 'brp_create_u_id',
                                   'brp_update_u_id'        => 'brp_update_u_id',
                                   'brp_parent_brp_id'      => 'brp_parent_brp_id',
                                   'brp_is_version'         => 'brp_is_version',
                                   'brp_status'             => $this->_decryptField('brp_status'),
                                   'brp_incident_date'      => $this->_decryptField('brp_incident_date'),
                                   'brp_remediation_date'   => $this->_decryptField('brp_remediation_date'),
                                   'brp_initials'           => $this->_decryptField('brp_initials'),
                                   'brp_initials_approver'  => $this->_decryptField('brp_initials_approver'),
                                   'brp_active'             => 'brp_active'
                                  )
                                );

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Breachremediationplan());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );


            if ($identity['u_role_id'] == User::ROLE_ADMIN) {
                $select->where('(' . $this->_decryptField('brp_status', false) . ' = 30 AND brp_active = 1) || (brp_active = 0)');
            } else {
                $select->where('brp_active = 1');

                if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                    $select->where('brp_consultant_u_id = ' . $identity['u_id']);
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $select->where('brp_consultant_u_id IN (' . implode(',', $ids) . ')');
                } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                    $select->where('brp_c_id = ' . $identity['u_company_id']);
                }
            }

            $select->join(array('c' => 'companies'), 'brp_c_id = c_id', array('_client_name' => 'c_name'), 'left');
            $select->join(array('u' => 'users'), 'brp_approver_u_id = u.u_id', array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)')), 'left');

            $order = $order ? $order : 'ASC';

            $orders[] = 'brp_version_index ' . $order;
            $orders[] = 'brp_id ASC';
            if ($orderBy) {
                $orders[] = $orderBy . ' ' . $order;
            }

            $select->order($orders);

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brp_id'                 => 'brp_id',
                               'brp_version_index'      => $this->_decryptField('brp_version_index'),
                               'brp_version_index_item' => $this->_decryptField('brp_version_index_item'),
                               'brp_writable'           => 'brp_writable',
                               'brp_bl_id'              => 'brp_bl_id',
                               'brp_c_id'               => 'brp_c_id',
                               'brp_consultant_u_id'    => 'brp_consultant_u_id',
                               'brp_performed_u_id'     => 'brp_performed_u_id',
                               'brp_approver_u_id'      => 'brp_approver_u_id',
                               'brp_accepter_u_id'      => 'brp_accepter_u_id',
                               'brp_create_u_id'        => 'brp_create_u_id',
                               'brp_update_u_id'        => 'brp_update_u_id',
                               'brp_parent_brp_id'      => 'brp_parent_brp_id',
                               'brp_is_version'         => 'brp_is_version',
                               'brp_status'             => $this->_decryptField('brp_status'),
                               'brp_incident_date'      => $this->_decryptField('brp_incident_date'),
                               'brp_remediation_date'   => $this->_decryptField('brp_remediation_date'),
                               'brp_initials'           => $this->_decryptField('brp_initials'),
                               'brp_initials_approver'  => $this->_decryptField('brp_initials_approver'),
                               'brp_active'             => 'brp_active'
                              )
                            );

         if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('brp_active = 1');
         }

         return $this->tableGateway->selectWith($select);
    }

    public function getBreachRemediationPlansForReporting($searchValue = null)
    {
        $identity = $this->_getIdentity();

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brp_id'                 => 'brp_id',
                               'brp_version_index'      => $this->_decryptField('brp_version_index'),
                               'brp_version_index_item' => $this->_decryptField('brp_version_index_item'),
                               'brp_writable'           => 'brp_writable',
                               'brp_bl_id'              => 'brp_bl_id',
                               'brp_c_id'               => 'brp_c_id',
                               'brp_consultant_u_id'    => 'brp_consultant_u_id',
                               'brp_performed_u_id'     => 'brp_performed_u_id',
                               'brp_approver_u_id'      => 'brp_approver_u_id',
                               'brp_accepter_u_id'      => 'brp_accepter_u_id',
                               'brp_create_u_id'        => 'brp_create_u_id',
                               'brp_update_u_id'        => 'brp_update_u_id',
                               'brp_parent_brp_id'      => 'brp_parent_brp_id',
                               'brp_is_version'         => 'brp_is_version',
                               'brp_status'             => $this->_decryptField('brp_status'),
                               'brp_incident_date'      => $this->_decryptField('brp_incident_date'),
                               'brp_remediation_date'   => $this->_decryptField('brp_remediation_date'),
                               'brp_initials'           => $this->_decryptField('brp_initials'),
                               'brp_initials_approver'  => $this->_decryptField('brp_initials_approver'),
                               'brp_active'             => 'brp_active'
                              )
                            );

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Breachremediationplan());
        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            $resultSetPrototype
        );

        if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            $select->where('(' . $this->_decryptField('brp_status', false) . ' = 30 AND brp_active = 1) || (brp_active = 0)');
        } else {
            $select->where('brp_active = 1');

            if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                $select->where('brp_consultant_u_id = ' . $identity['u_id']);
            } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                $ids[] = $identity['u_id'];
                $select->where('brp_consultant_u_id IN (' . implode(',', $ids) . ')');
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $select->where('brp_c_id = ' . $identity['u_company_id']);
            }
        }

        if ($searchValue !== null) {
            $select->where('(rg.rg_number LIKE "%' . $searchValue . '%" OR rg.rg_description LIKE "%' . $searchValue . '%")');
        }
        
        $select->columns(array('brp_id' => 'brp_id', 'brp_remediation_date' => $this->_decryptField('brp_remediation_date')));
        $select->join(array('c' => 'companies'), 'brp_c_id = c_id', array('_client_name' => 'c_name'), 'inner');
        $select->join(array('brprg' => 'breach_remediation_plans_regulations'), new \Zend\Db\Sql\Expression('brp_id = brprg.brprg_brp_id'), array('_brp_brprg_id' => new \Zend\Db\Sql\Expression('brprg.brprg_id'), '_brp_brprg_rg_id' => new \Zend\Db\Sql\Expression('brprg.brprg_rg_id')), 'inner');
        $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('brprg.brprg_rg_id = rg.rg_id'), array('_brp_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'inner');

        $select->group('brp_id');
        $select->order('brp_remediation_date DESC');

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function getForReport($conditionNum = 0, $status = 1, $uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brp_id'                 => 'brp_id',
                               'brp_version_index'      => $this->_decryptField('brp_version_index'),
                               'brp_version_index_item' => $this->_decryptField('brp_version_index_item'),
                               'brp_writable'           => 'brp_writable',
                               'brp_bl_id'              => 'brp_bl_id',
                               'brp_c_id'               => 'brp_c_id',
                               'brp_consultant_u_id'    => 'brp_consultant_u_id',
                               'brp_performed_u_id'     => 'brp_performed_u_id',
                               'brp_approver_u_id'      => 'brp_approver_u_id',
                               'brp_accepter_u_id'      => 'brp_accepter_u_id',
                               'brp_create_u_id'        => 'brp_create_u_id',
                               'brp_update_u_id'        => 'brp_update_u_id',
                               'brp_parent_brp_id'      => 'brp_parent_brp_id',
                               'brp_is_version'         => 'brp_is_version',
                               'brp_status'             => $this->_decryptField('brp_status'),
                               'brp_incident_date'      => $this->_decryptField('brp_incident_date'),
                               'brp_remediation_date'   => $this->_decryptField('brp_remediation_date'),
                               'brp_initials'           => $this->_decryptField('brp_initials'),
                               'brp_initials_approver'  => $this->_decryptField('brp_initials_approver'),
                               'brp_active'             => 'brp_active'
                              )
                            );

        $select->where('brp_active = 1');
        $condition = isset(\Admin\Model\UserTable::$reportCondition[$conditionNum]) ? \Admin\Model\UserTable::$reportCondition[$conditionNum] : null;

        if ($condition != '') {
            $condition = str_replace('?', $this->_decryptField('brp_create_date', false), $condition);
            $select->where($condition);
        }

        $select->columns(array('_client_name' => new \Zend\Db\Sql\Expression('COUNT(brp_id)')));

        if ($status == 1) {
            $select->where($this->_decryptField("brp_status", false) . "IN (10, 20)");
        } else {
            $select->where($this->_decryptField("brp_status", false) . "IN (30, 40)");
        }

        if ($uId) {
            $identity = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($uId);

            if ($identity->u_role_id == User::ROLE_CONSULTANT) {
                $select->where('brp_consultant_u_id = ' . $identity->u_id);
            } elseif ($identity->u_role_id == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity->u_id);
                $ids[] = $identity->u_id;
                $select->where('brp_consultant_u_id IN (' . implode(',', $ids) . ')');
            }
        }

        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }

        return ($row->_client_name);
    }

    public function getBreachremediationplan($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brp_id'                 => 'brp_id',
                               'brp_version_index'      => $this->_decryptField('brp_version_index'),
                               'brp_version_index_item' => $this->_decryptField('brp_version_index_item'),
                               'brp_writable'           => 'brp_writable',
                               'brp_bl_id'              => 'brp_bl_id',
                               'brp_c_id'               => 'brp_c_id',
                               'brp_consultant_u_id'    => 'brp_consultant_u_id',
                               'brp_performed_u_id'     => 'brp_performed_u_id',
                               'brp_approver_u_id'      => 'brp_approver_u_id',
                               'brp_accepter_u_id'      => 'brp_accepter_u_id',
                               'brp_create_u_id'        => 'brp_create_u_id',
                               'brp_update_u_id'        => 'brp_update_u_id',
                               'brp_parent_brp_id'      => 'brp_parent_brp_id',
                               'brp_is_version'         => 'brp_is_version',
                               'brp_status'             => $this->_decryptField('brp_status'),
                               'brp_incident_date'      => $this->_decryptField('brp_incident_date'),
                               'brp_remediation_date'   => $this->_decryptField('brp_remediation_date'),
                               'brp_initials'           => $this->_decryptField('brp_initials'),
                               'brp_initials_approver'  => $this->_decryptField('brp_initials_approver'),
                               'brp_active'             => 'brp_active'
                              )
                            );

        $select->where('brp_id = ' . $id);
        $select->where('brp_active = 1');
        $select->join(array('c' => 'companies'), 'brp_c_id = c_id', array('_client_name' => 'c_name'), 'left');
        $select->join(array('u' => 'users'), 'brp_approver_u_id = u_id', array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_brp_incident_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(brp_incident_date, "%m/%d/%Y")'), '_brp_remediation_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(brp_remediation_date, "%m/%d/%Y")')), 'left');
        $select->join(array('u2' => 'users'), 'brp_consultant_u_id = u2.u_id', array('_consultant_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');
        $select->join(array('u3' => 'users'), 'brp_performed_u_id = u3.u_id', array('_performed_name' => new \Zend\Db\Sql\Expression('CONCAT(u3.u_firstname, " ", u3.u_lastname)')), 'left');
        $select->join(array('u4' => 'users'), 'brp_approver_u_id = u4.u_id', array('_accepter_name' => new \Zend\Db\Sql\Expression('CONCAT(u4.u_firstname, " ", u4.u_lastname)')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brp_id'                 => 'brp_id',
                               'brp_version_index'      => $this->_decryptField('brp_version_index'),
                               'brp_version_index_item' => $this->_decryptField('brp_version_index_item'),
                               'brp_writable'           => 'brp_writable',
                               'brp_bl_id'              => 'brp_bl_id',
                               'brp_c_id'               => 'brp_c_id',
                               'brp_consultant_u_id'    => 'brp_consultant_u_id',
                               'brp_performed_u_id'     => 'brp_performed_u_id',
                               'brp_approver_u_id'      => 'brp_approver_u_id',
                               'brp_accepter_u_id'      => 'brp_accepter_u_id',
                               'brp_create_u_id'        => 'brp_create_u_id',
                               'brp_update_u_id'        => 'brp_update_u_id',
                               'brp_parent_brp_id'      => 'brp_parent_brp_id',
                               'brp_is_version'         => 'brp_is_version',
                               'brp_status'             => $this->_decryptField('brp_status'),
                               'brp_incident_date'      => $this->_decryptField('brp_incident_date'),
                               'brp_remediation_date'   => $this->_decryptField('brp_remediation_date'),
                               'brp_initials'           => $this->_decryptField('brp_initials'),
                               'brp_initials_approver'  => $this->_decryptField('brp_initials_approver'),
                               'brp_active'             => 'brp_active'
                              )
                            );

        $select->join(array('brprg' => 'breach_remediation_plans_regulations'), new \Zend\Db\Sql\Expression('brp_id = brprg.brprg_brp_id'), array('_brp_brprg_id' => new \Zend\Db\Sql\Expression('brprg.brprg_id'), '_brp_brprg_rg_id' => new \Zend\Db\Sql\Expression('brprg.brprg_rg_id')), 'inner');
        $select->join(array('rg' => 'regulations'), new \Zend\Db\Sql\Expression('brprg.brprg_rg_id = rg.rg_id'), array('_brp_regulation' => new \Zend\Db\Sql\Expression('rg.rg_pp_name')), 'inner');
        $select->where("brp_id =" . $id);

        $regulations = $this->tableGateway->selectWith($select);

        foreach ($regulations as $rs) {
            $row->_brp_cur_regulations[$rs->_brp_brprg_id] = $rs->_brp_brprg_rg_id;
        }

        return $row;
    }

    public function saveBreachremediationplan(Breachremediationplan $brp)
    {
        $newBrp = false;

        $data = array(
            'brp_bl_id'             => $brp->brp_bl_id,
            'brp_c_id'              => $brp->brp_c_id,
            'brp_consultant_u_id'   => $brp->brp_consultant_u_id,
            'brp_approver_u_id'     => $brp->brp_approver_u_id,
            'brp_performed_u_id'    => $brp->brp_performed_u_id,
            'brp_accepter_u_id'     => $brp->brp_accepter_u_id,
            'brp_parent_brp_id'     => $brp->brp_parent_brp_id,
            'brp_version_index'     => $this->_encryptValue($brp->brp_version_index),
            'brp_initials'          => $this->_encryptValue($brp->brp_initials),
            'brp_initials_approver' => $this->_encryptValue($brp->brp_initials_approver),
            'brp_remediation_date'  => $this->_encryptValue($brp->brp_remediation_date),
            'brp_incident_date'     => $this->_encryptValue($brp->brp_incident_date),
            'brp_status'            => $this->_encryptValue($brp->brp_status),
            'brp_is_version'        => $brp->brp_is_version,
        );

        if (!$data['brp_status']) {
            $data['brp_status'] = $this->_encryptValue(\Breachlog\Model\Breachremediationplan::STATUS_NEW);
            $newBrp = true;
        }

        $id = (int) $brp->brp_id;

        $identity = $this->_getIdentity();

        if (!$id) {
            $data['brp_consultant_u_id'] = $brp->brp_consultant_u_id;

            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
                $data['brp_consultant_u_id'] = $brp->brp_consultant_u_id;
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $data['brp_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
                $data['brp_c_id'] = $identity['u_company_id'];
            }

            $data['brp_create_u_id'] = $identity['u_id'];
            $data['brp_create_date'] = $this->_encryptValue(date('Y-m-d H:i:s'));
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            if ($newBrp) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_BRP, $id);
            }

            if (!(int) $brp->brp_version_index) {
                $this->tableGateway->update(array('brp_version_index' => $id), array('brp_id' => $id));
            }
        } else {
            if ($this->getBreachremediationplan($id)) {

                $this->tableGateway->update($data, array('brp_id' => $id));
                throw new \Exception('Form id does not exist');
            }
        }
        if ((int) $data['brp_parent_brp_id']) {
            $this->reindexVersion($brp->brp_version_index);
        }

        return $id;
    }

    public function reindexVersion($versionIndex)
    {
        $versionIndex  = (int) $versionIndex;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('brp_id'            => 'brp_id',
                               'brp_version_index' => $this->_decryptField('brp_version_index'),
                               'brp_parent_brp_id' => 'brp_parent_brp_id',
                               'brp_active'        => 'brp_active'
                              )
                            );

        $select->where($this->_decryptField('brp_version_index', false) . ' = ' . $versionIndex);
        $select->where('brp_active = 1');
        $select->where('brp_parent_brp_id IS NOT NULL');

        $select->order('brp_id ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        $indexItem = 1;
        foreach ($resultSet as $rs) {

            $this->tableGateway->update(array('brp_version_index_item' => $this->_encryptValue($indexItem)), array('brp_id' => $rs->brp_id));
            $indexItem++;

        }
    }

    public function setStatus($id, $status)
    {
        $data = array(
            'brp_id' => $id,
            'brp_status' => $this->_encryptValue($status),
        );

        if ($brp = $this->getBreachremediationplan($id)) {
            $currentStatus = $brp->brp_status;
            if ($status < $currentStatus) {
                return;
            }
            if ($status == \Breachlog\Model\Breachremediationplan::STATUS_SIGNED_OFF) {
                $data['brp_writable'] = 0;
            }
            $this->tableGateway->update($data, array('brp_id' => $id));
        }

        return $id;
    }

    public function setFieldValue($id, $field, $value, $encrypt = false)
    {
        $data = array(
            'brp_id' => $id,
             $field => $encrypt ? $this->_encryptValue($value) : $value,
        );

        return $this->tableGateway->update($data, array('brp_id' => $id));
    }

    public function setRegulations($id, $cur_regulations, $regulations = array())
    {
        $identity = $this->_getIdentity();

        $breachRemediationPlanRegulationTable = $this->getServiceLocator()->get('Breachlog\Model\BreachRemediationPlanRegulationTable');

        $breachRemediationPlanRegulationTable->deleteByBreachRemediationPlanId($id);

        if($cur_regulations) {
            foreach ($cur_regulations as $regulation) {
                if($regulation == -1 && $regulations) {
                    $regulationData = array( 'rg_pp_name'     => $regulations[-1]['rg_pp_name']
                                           , 'rg_pp_number'   => $regulations[-1]['rg_pp_number']
                                           , 'rg_number'      => $regulations[-1]['rg_number']
                                           , 'rg_description' => $regulations[-1]['rg_description']
                                           , 'rg_u_owner_id'  => $identity['u_id']
                                           );

                    $rgId = $this->getServiceLocator()->get('Traininglog\Model\RegulationTable')->saveRegulation($regulationData);

                    $breachRemediationPlanRegulationTable->saveBreachRemediationPlanRegulation(array('brprg_brp_id' => $id, 'brprg_rg_id' => $rgId));
                } else {
                    $breachRemediationPlanRegulationTable->saveBreachRemediationPlanRegulation(array('brprg_brp_id' => $id, 'brprg_rg_id' => $regulation));
                }
            }
        }
    }

    public function deleteBreachremediationplan($id)
    {
        $brp = $this->getBreachremediationplan($id);

        $data['brp_id']     = $id;
        $data['brp_active'] = 0;

        $this->tableGateway->update($data, array('brp_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_BRP, $id);

        $this->reindexVersion($brp->brp_version_index);

        return true;
    }

    public function unarchiveBreachremediationplan($id)
    {
        $data['brp_id']     = $id;
        $data['brp_active'] = 1;

        $this->tableGateway->update($data, array('brp_id' => $id));

        return true;
    }

    public function deleteBreachremediationplansByCompanyId($cId, $value = 0)
    {
        $data['brp_active'] = $value;

        $this->tableGateway->update($data, array('brp_c_id' => $cId));

        return true;
    }


    public function reopenBreachremediationplan($id)
    {
        $brp = $this->getBreachremediationplan($id);

        $data['brp_id']       = $id;
        $data['brp_status']   = $this->_encryptValue(20);
        $data['brp_writable'] = 1;

        $this->tableGateway->update($data, array('brp_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_REOPEN, \Application\Model\LogsTable::ITEM_TYPE_BRP, $id);

        $this->reindexVersion($brp->brp_version_index);

        return true;
    }

    public function clonePlan($id)
    {
        $brp = $this->getBreachremediationplan($id);

        // writable to false
        $this->tableGateway->update(array('brp_writable' => 0, 'brp_status' => $this->_encryptValue(\Breachlog\Model\Breachremediationplan::STATUS_CLOSED)), array('brp_id' => $id));

        // create new row
        $brp->brp_id            = 0;
        $brp->brp_parent_brp_id = $id;
        $brp->brp_status        = $this->_encryptValue(\Breachlog\Model\Breachremediationplan::STATUS_NEW);

        $newId = $this->saveBreachremediationplan($brp);

        // copy notes with files
        $noteDb = $this->getServiceLocator()->get('Note\Model\NoteTable');
        $notes  = $noteDb->getNotes($id, \Note\Model\Note::NOTE_BRP);

        foreach ($notes as $note) {
            $oldNoteId          = $note->note_id;
            $note->note_id      = 0;
            $note->note_item_id = $newId;

            $noteId = $noteDb->saveNote($note, array(), true);

            // copy files to notes
            $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
            $files       = $noteFilesDb->getFilesByNoteId($oldNoteId);

            foreach ($files as $file) {
                $dataFile = array();

                $dataFile['nf_f_id']    = $file['nf_f_id'];
                $dataFile['nf_note_id'] = $noteId;

                $noteFilesDb->saveFile($dataFile);
            }
        }

        // copy actions
        $brpaDb  = $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanactionTable');
        $actions = $brpaDb->getBreachremediationplanactions($id);

        foreach ($actions as $action) {
            $oldActionId         = $action->brpa_id;
            $action->brpa_id     = 0;
            $action->brpa_brp_id = $newId;

            $newActionId = $brpaDb->saveBreachremediationplanaction($action);

            // copy notes with files to actions
            $noteDb = $this->getServiceLocator()->get('Note\Model\NoteTable');
            $notes  = $noteDb->getNotes($oldActionId, \Note\Model\Note::NOTE_BRPA);

            foreach ($notes as $note) {
                $oldNoteId          = $note->note_id;
                $note->note_id      = 0;
                $note->note_item_id = $newActionId;

                $noteId = $noteDb->saveNote($note, array(), true);

                // copy files to notes
                $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
                $files       = $noteFilesDb->getFilesByNoteId($oldNoteId);

                foreach ($files as $file) {
                    $dataFile               = array();
                    $dataFile['nf_f_id']    = $file['nf_f_id'];
                    $dataFile['nf_note_id'] = $noteId;

                    $noteFilesDb->saveFile($dataFile);
                }
            }
        }

        return $newId;
    }

}