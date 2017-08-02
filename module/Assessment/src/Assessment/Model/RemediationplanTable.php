<?php
namespace Assessment\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Assessment\Model\Remediationplan;
use Zend\Db\Sql\Predicate\PredicateSet;

class RemediationplanTable implements ServiceLocatorAwareInterface
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

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getRemediationplans($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('remediation_plans');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Remediationplan());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            } else {
                $where_str = '';
                if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                    $where_str .= 'rp_consultant_u_id = ' . $identity['u_id'];
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $where_str .= 'rp_consultant_u_id IN (' . implode(',', $ids) . ')';
                } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
                    $companies = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompanies($identity['u_id']);
                    $companies[] = $identity['u_company_id'];

                    $where_str .= 'rp_c_id IN (' . implode(',', $companies) . ') ';
                }

                $companies_ids = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getCompaniesIdsForConsultant($identity['u_id']);
                if ($companies_ids) {
                    if ($where_str) {
                        $where_str = '(' . $where_str . ' OR rp_c_id IN (' . implode(',', $companies_ids) . '))';
                      } else {
                        $where_str = 'rp_c_id IN (' . implode(',', $companies_ids) . ')';
                      }
                }
                if ($where_str) {
                    $where_str .= ' AND rp_active = 1';
                } else {
                    $where_str = 'rp_active = 1';
                }
                $select->where($where_str);
            }


            $select->join(array('c' => 'companies'), 'rp_c_id = c_id', array('_client_name' => 'c_name'), 'left');
            $select->join(array('adr' => 'addresses'), new \Zend\Db\Sql\Expression('adr_id = rp_adr_id'), array('_location_name' => new \Zend\Db\Sql\Expression('adr_name')), 'left');
            $select->join(array('as' => 'assessments'), 'rp_a_id = a_id', array('a_security_a_id', 'a_version_index', 'a_version_index_item'), 'left');
            $select->join(array('u' => 'users'), 'rp_approver_u_id = u_id', array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)')), 'left');
            $select->columns(array( '*'
                                  , '_status' => new \Zend\Db\Sql\Expression('CASE remediation_plans.rp_status 
                                                                                   WHEN ' . Remediationplan::STATUS_NEW . ' THEN "' . Remediationplan::$statusesNames[Remediationplan::STATUS_NEW] . '"
                                                                                   WHEN ' . Remediationplan::STATUS_OPEN . ' THEN "' . Remediationplan::$statusesNames[Remediationplan::STATUS_OPEN] . '"
                                                                                   WHEN ' . Remediationplan::STATUS_SIGNED_OFF . ' THEN "' . Remediationplan::$statusesNames[Remediationplan::STATUS_SIGNED_OFF] . '"
                                                                                   WHEN ' . Remediationplan::STATUS_CLOSED . ' THEN "' . Remediationplan::$statusesNames[Remediationplan::STATUS_CLOSED] . '"
                                                                                   END')
                                  , '_type' => new \Zend\Db\Sql\Expression('IF(remediation_plans.rp_type = ' . Assessment::TYPE_SECURITY_RISK . ', "' . Assessment::$typesNames[Assessment::TYPE_SECURITY_RISK] . '", "' . Assessment::$typesNames[Assessment::TYPE_PRIVACY_RISK] . '")')
                                  )
                            );

            $select->where('c_active = 1');

            $order = $order ? $order : 'ASC';

            if ($orderBy) {
                $orders[] = $orderBy . ' ' . $order;
            }

            $orders[] = 'rp_version_index ' . $order;
            $orders[] = 'rp_id ASC';

            $select->order($orders);

            $paginator = new Paginator($paginatorAdapter);
            
            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getRpLocs($a_id)
    {
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('rp_a_id =' . $a_id);
        
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getSearchResultsSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('c_name LIKE "%' . $searchValue . '%"');

        $select->columns(array('_id' => new \Zend\Db\Sql\Expression('rp_id'),
                               '_name' => new \Zend\Db\Sql\Expression('c_name'),
                               '_item_type' => new \Zend\Db\Sql\Expression('rp_type'),
                               '_type' => new \Zend\Db\Sql\Expression('CONCAT("remediationplan")'),
                               '_status' => new \Zend\Db\Sql\Expression('rp_status'),
                               '_date' => new \Zend\Db\Sql\Expression('rp_create_date'),
                               )
                        );
        $select->join(array('c' => 'companies'), 'rp_c_id = c_id', array(), 'left');

        $where_str = '';
        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $where_str .= 'rp_consultant_u_id = ' . $identity['u_id'];
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $where_str .= 'rp_consultant_u_id IN (' . implode(',', $ids) . ')';
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
            $companies = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompanies($identity['u_id']);
            $companies[] = $identity['u_company_id'];

            $where_str .= 'rp_c_id IN (' . implode(',', $companies) . ') ';
        }

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $companies_ids = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getCompaniesIdsForConsultant($identity['u_id']);
            if ($companies_ids) {
                if ($where_str) {
                    $where_str = '(' . $where_str . ' OR rp_c_id IN (' . implode(',', $companies_ids) . '))';
                  } else {
                    $where_str = 'rp_c_id IN (' . implode(',', $companies_ids) . ')';
                  }
            }
            if ($where_str) {
                $where_str .= ' AND rp_active = 1';
            } else {
                $where_str = 'rp_active = 1';
            }
        }
        if ($where_str) {
            $select->where($where_str);
        }

        $select->where('c_active = 1');

        return $select;
    }

    public function getForReport($conditionNum = 0, $status = 1, $uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_active = 1');
        $condition = isset(\Admin\Model\UserTable::$reportCondition[$conditionNum]) ? \Admin\Model\UserTable::$reportCondition[$conditionNum] : null;

        if ($condition != '') {
            $condition = str_replace('?', 'rp_create_date', $condition);
            $select->where($condition);
        }

        $select->columns(array('_client_name' => new \Zend\Db\Sql\Expression('COUNT(rp_id)')));

        if ($status == 1) {
            $select->where("rp_status IN (10, 20)");
        } else {
            $select->where("rp_status IN (30, 40)");
        }

        if ($uId) {
            $identity = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($uId);

            if ($identity->u_role_id == User::ROLE_CONSULTANT) {
                $select->where('rp_consultant_u_id = ' . $identity->u_id);
            } elseif ($identity->u_role_id == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity->u_id);
                $ids[] = $identity->u_id;
                $select->where('rp_consultant_u_id IN (' . implode(',', $ids) . ')');
            }
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return ($row->_client_name);
    }

    public function getRemediationPlansForPlanProgress($company = null, $type = null, $status = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $selectRP = new Select('remediation_plans');

        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $selectRP->where('rp_consultant_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $selectRP->where('rp_consultant_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $selectRP->where('rp_c_id = ' . $identity['u_company_id']);
        }

        if ($identity['u_role_id'] != \Admin\Model\User::ROLE_ADMIN) {
            $selectRP->where('rp_active = 1');
        }
        

        if($company) {
            $selectRP->where('rp_c_id = ' . $company);
        }

        if($type) {
            if($type == 'imported') {
                $selectRP->where('rp_a_id IS NULL');
            } else if($type == 'breach') {
                $selectRP->where('(1 = 0)');
            } else {
                $selectRP->where('(rp_type = ' . $type . ' AND rp_a_id IS NOT NULL)');
            }
        }

        if($status) {
            $selectRP->where('rp_status = ' . $status);
        }
        
        $selectRP->where('rp_status != ' . \Assessment\Model\Remediationplan::STATUS_CLOSED);

        $selectRP->columns(array('_id'       => 'rp_id',
                                '_c_name'    => new \Zend\Db\Sql\Expression('c_name'),
                                '_type'      => new \Zend\Db\Sql\Expression('IF(true , "remediation" ,0)'),
                                '_status'    => 'rp_status',
                                '_item_type' => 'rp_type',
                                '_assesment' => 'rp_a_id',
                                '_date'      => new \Zend\Db\Sql\Expression('IF(rp_a_id , rp_incident_date , rp_remediation_date)')
                                )
                            );

        $selectRP->join(array('c' => 'companies'), 'rp_c_id = c_id', array(), 'left');

        $selectRP->group(array('rp_id'));

        $selectBRP = new Select('breach_remediation_plans');

        $selectBRP->where('brp_active = 1');

        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $selectBRP->where('brp_consultant_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $selectBRP->where('brp_consultant_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $selectBRP->where('brp_c_id = ' . $identity['u_company_id']);
        }

        if($company) {
            $selectBRP->where('brp_c_id = ' . $company);
        }

        if($type && $type != 'breach') {
            $selectBRP->where('1 = 0');
        }

        if($status) {
            $selectBRP->where('brp_status = ' . $status);
        }

        $selectBRP->where('brp_status != ' . \Breachlog\Model\Breachremediationplan::STATUS_CLOSED);

        $selectBRP->columns(array('_id'        => 'brp_id',
                                  '_c_name'    => new \Zend\Db\Sql\Expression('c_name'),
                                  '_type'      => new \Zend\Db\Sql\Expression('IF(true , "breach" ,0)'),
                                  '_status'    => 'brp_status',
                                  '_item_type' => new \Zend\Db\Sql\Expression('IF(true , -1 ,0)'),
                                  '_assesment' => new \Zend\Db\Sql\Expression('IF(true , -1 ,0)'),
                                  '_date'      => 'brp_create_date'
                                )
                            );
        $selectBRP->join(array('c' => 'companies'), 'brp_c_id = c_id', array(), 'left');

        $selectBRP->group(array('brp_id'));
        $selectBRP->combine($selectRP);

        $selectBRP->order('_c_name, _type, _status, _date DESC');

        $paginatorAdapter = new DbSelect(
            $selectBRP,
            $this->tableGateway->getAdapter(),
            new ResultSet()
        );

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public static function getTypeForReporting($item)
    {
        $result = '';

        if($item->_type == 'breach') {
            $result = 'Breach Remediation Plan';
        } else {
            if(!$item->_assesment) {
                $result = 'Imported Remediation Plans';
            } else {
                $result = \Assessment\Model\Assessment::$typesNames[$item->_item_type];
            }
        }

        return $result;
    }

    public function getRemediationplan($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_id = ' . $id);
        //$select->where('rp_active = 1');
        $select->join(array('c' => 'companies'), 'rp_c_id = c_id', array('_client_name' => 'c_name'), 'left');
        $select->join(array('u' => 'users'), 'rp_approver_u_id = u_id', array('_approver_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_rp_approved_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rp_approved_date, "%m/%d/%Y")'), '_rp_accepted_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rp_accepted_date, "%m/%d/%Y")'), '_rp_incident_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rp_incident_date, "%m/%d/%Y")'), '_rp_remediation_date_formatted' => new \Zend\Db\Sql\Expression('DATE_FORMAT(rp_remediation_date, "%m/%d/%Y")')), 'left');
        $select->join(array('u2' => 'users'), 'rp_consultant_u_id = u2.u_id', array('_consultant_name' => new \Zend\Db\Sql\Expression('CONCAT(u2.u_firstname, " ", u2.u_lastname)')), 'left');
        $select->join(array('u3' => 'users'), 'rp_performed_u_id = u3.u_id', array('_performed_name' => new \Zend\Db\Sql\Expression('CONCAT(u3.u_firstname, " ", u3.u_lastname)')), 'left');
        $select->join(array('u4' => 'users'), 'rp_accepter_u_id = u4.u_id', array('_accepter_name' => new \Zend\Db\Sql\Expression('CONCAT(u4.u_firstname, " ", u4.u_lastname)')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getRemediationplanByAId($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_a_id = ' . $id);
        //$select->where('rp_active = 1');
        $select->where('rp_type = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getRemediationplanByAIdAdrId($id, $adr_id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_a_id = ' . $id);
        if ($adr_id) {
            $select->where('rp_adr_id IS NULL OR rp_adr_id = ' . $adr_id);
        }        

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveRemediationplan(Remediationplan $rp)
    {
        $data = array(
            'rp_a_id' => $rp->rp_a_id,
            'rp_c_id' => $rp->rp_c_id,
            'rp_type' => $rp->rp_type,
            'rp_consultant_u_id' => $rp->rp_consultant_u_id,
            'rp_performed_u_id' => $rp->rp_performed_u_id,
            'rp_approver_u_id' => $rp->rp_approver_u_id,
            'rp_parent_rp_id' => $rp->rp_parent_rp_id,
            'rp_version_index' => $rp->rp_version_index,
            'rp_initials' => $rp->rp_initials,
            'rp_initials_approver' => $rp->rp_initials_approver,
            'rp_remediation_date' => $rp->rp_remediation_date,
            'rp_incident_date' => $rp->rp_incident_date,
            'rp_status' => $rp->rp_status,
            'rp_is_version' => $rp->rp_is_version,
            'rp_approved_date' => $rp->rp_approved_date,
            'rp_accepted_date' => $rp->rp_accepted_date,
            'rp_accepter_u_id' => $rp->rp_accepter_u_id,
            'rp_adr_id' => $rp->rp_adr_id,
        );

        if ($rp->rp_security_rp_id) {
            $data['rp_security_rp_id'] = $rp->rp_security_rp_id;
        }
        if (!$data['rp_status']) {
            $data['rp_status'] = \Assessment\Model\Remediationplan::STATUS_NEW;
        }

        $id = (int) $rp->rp_id;

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (!$id) {
            $data['rp_consultant_u_id'] = $rp->rp_consultant_u_id;

            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
                $data['rp_consultant_u_id'] = $rp->rp_consultant_u_id;
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $data['rp_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
                $data['rp_c_id'] = $identity['u_company_id'];
            }

            $data['rp_create_u_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            if (($data['rp_status'] == \Assessment\Model\Remediationplan::STATUS_NEW) && ((int) $rp->rp_parent_rp_id)) {
                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);
            }

            if (!(int) $rp->rp_version_index) {
                $this->tableGateway->update(array('rp_version_index' => $id), array('rp_id' => $id));
            }
        } else {
            if ($this->getRemediationplan($id)) {

                $this->tableGateway->update($data, array('rp_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        if ((int) $data['rp_parent_rp_id']) {
            $this->reindexVersion($rp->rp_version_index);
        }

        return $id;
    }

    public function importRemediationplan($data)
    {
        $rpData = array(
            'rp_c_id' => $data['rp_c_id'],
            'rp_type' => 1,
            'rp_performed_u_id' => $data['rp_performed_u_id'],
            'rp_remediation_date' => $data['rp_remediation_date'],
            'rp_incident_date' => $data['rp_incident_date'],
            'rp_status' => Remediationplan::STATUS_OPEN,
        );

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
            $rpData['rp_consultant_u_id'] = $identity['u_id'];
        } else if ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $data['rp_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
        }

        $data['rp_create_u_id'] = $identity['u_id'];

        $this->tableGateway->insert($rpData);
        $rpId = $this->tableGateway->lastInsertValue;

        if($rpId) {
            $this->tableGateway->update(array('rp_version_index' => $rpId), array('rp_id' => $rpId));
            if(isset($data['actions']) && !empty($data['actions'])) {
                $rpaDb = $this->getServiceLocator()->get('Assessment\Model\RemediationplanactionTable');
                foreach ($data['actions'] as $action) {
                    $action->rpa_threat = utf8_encode($action->rpa_threat);
                    $action->rpa_rp_id = $rpId; 
                    $rpaDb->saveRemediationplanaction($action);
                }
            }
        }

        return $rpId;
    }

    public function reindexVersion($versionIndex)
    {
        $versionIndex  = (int) $versionIndex;

        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_version_index = ' . $versionIndex);
        $select->where('rp_active = 1');
        $select->where('rp_parent_rp_id IS NOT NULL');

        $select->order('rp_id ASC');

        $resultSet = $this->tableGateway->selectWith($select);

        $indexItem = 1;
        foreach ($resultSet as $rs) {
            $data = array(
                'rp_id' => $rs->rp_id,
                'rp_version_index_item' => $indexItem,
            );

            $this->tableGateway->update(array('rp_version_index_item' => $indexItem), array('rp_id' => $rs->rp_id));
            $indexItem++;

        }
    }

    public function setStatus($id, $status)
    {
        $data = array(
            'rp_id' => $id,
            'rp_status' => $status,
        );

        if ($rp = $this->getRemediationplan($id)) {
            $currentStatus = $rp->rp_status;
            if ($status < $currentStatus) {
                return;
            }
            if ($status == \Assessment\Model\Remediationplan::STATUS_SIGNED_OFF) {
                $data['rp_writable'] = 0;
            }
            $this->tableGateway->update($data, array('rp_id' => $id));
        }

        return $id;
    }

    public function setFieldValues($id, $data)
    {
        $data['rp_id'] = $id;
        
        if ($this->getRemediationplan($id)) {
            $this->tableGateway->update($data, array('rp_id' => $id));
        }

        return $id;
    }

    public function deleteRemediationplan($id)
    {
        $rp = $this->getRemediationplan($id);

        $data['rp_id'] = $id;
        $data['rp_active'] = 0;
        $this->tableGateway->update($data, array('rp_id' => $id));

       $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);

        $this->reindexVersion($rp->rp_version_index);

        return true;
    }

    public function unarchiveRemediationplan($id)
    {
        $data['rp_id'] = $id;
        $data['rp_active'] = 1;
        $this->tableGateway->update($data, array('rp_id' => $id));

        return true;
    }

    public function deleteRemediationplansByCompanyId($cId, $value = 0)
    {
        $data['rp_active'] = $value;
        $this->tableGateway->update($data, array('rp_c_id' => $cId));

        return true;
    }

    public function reopenRemediationplan($id)
    {
        $rp = $this->getRemediationplan($id);

        $data['rp_id'] = $id;
        $data['rp_status'] = 20;
        $data['rp_writable'] = 1;
        $this->tableGateway->update($data, array('rp_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_REOPEN, \Application\Model\LogsTable::ITEM_TYPE_RP, $id);

        $this->reindexVersion($rp->rp_version_index);

        return true;
    }

    public function clonePlan($id, $post = false, $signedOffCopy = false)
    {
        $rp = $this->getRemediationplan($id);
        
        if(!$signedOffCopy) {
            // writable to false
            $this->tableGateway->update(array('rp_writable' => 0, 'rp_status' =>  \Assessment\Model\Remediationplan::STATUS_CLOSED), array('rp_id' => $id));
        }
        
        // create new row
        $rp->rp_id = 0;
        $rp->rp_parent_rp_id = $id;
        $rp->rp_status = \Assessment\Model\Remediationplan::STATUS_NEW;
        if($signedOffCopy) {
            $ymd3 = \DateTime::createFromFormat('m/d/Y', date('m/d/Y'));
            if (is_object($ymd3)) {
                if ($ymd3->format('Y') > date("Y")) {
                    $ymd3->setDate('2014', $ymd3->format('m'), $ymd3->format('d'));
                }
                $ymd3 = $ymd3->format('Y-m-d');
            } else {
                $ymd3 = '';
            }
            $rp->rp_remediation_date = $ymd3;
        }
        
        $newId = $this->saveRemediationplan($rp);

        // copy notes with files
        $noteDb = $this->getServiceLocator()->get('Note\Model\NoteTable');
        $notes = $noteDb->getNotes($id, \Note\Model\Note::NOTE_RP);
        foreach ($notes as $note) {
            $oldNoteId = $note->note_id;
            $note->note_id = 0;
            $note->note_item_id = $newId;
            $noteId = $noteDb->saveNote($note, array(), true);

            // copy files to notes
            $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
            $files = $noteFilesDb->getFilesByNoteId($oldNoteId);

            foreach ($files as $file) {
                $dataFile = array();
                $dataFile['nf_f_id'] = $file['nf_f_id'];
                $dataFile['nf_note_id'] = $noteId;

                $noteFilesDb->saveFile($dataFile);
            }
        }

        // copy actions
        $rpaDb = $this->getServiceLocator()->get('Assessment\Model\RemediationplanactionTable');
        $actions = $rpaDb->getRemediationplanactions($id);
        foreach ($actions as $action) {
            $oldActionId = $action->rpa_id;
            $action->rpa_id = 0;
            $action->rpa_rp_id = $newId;
            $newActionId = $rpaDb->saveRemediationplanaction($action);

            // copy notes with files to actions
            $noteDb = $this->getServiceLocator()->get('Note\Model\NoteTable');
            $notes = $noteDb->getNotes($oldActionId, \Note\Model\Note::NOTE_RPA);
            foreach ($notes as $note) {
                $oldNoteId = $note->note_id;
                $note->note_id = 0;
                $note->note_item_id = $newActionId;
                $noteId = $noteDb->saveNote($note, array(), true);

                // copy files to notes
                $noteFilesDb = $this->getServiceLocator()->get('Note\Model\NotesFilesTable');
                $files = $noteFilesDb->getFilesByNoteId($oldNoteId);

                foreach ($files as $file) {
                    $dataFile = array();
                    $dataFile['nf_f_id'] = $file['nf_f_id'];
                    $dataFile['nf_note_id'] = $noteId;

                    $noteFilesDb->saveFile($dataFile);
                }
            }
        }

        return $newId;
    }

    public function getApproverAccepter($id)
    {
        $id     = (int) $id;
        $result = array();

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array(new \Zend\Db\Sql\Expression('DISTINCT(u1.u_id) as u_id')));
        $select->where('rp_id = ' . $id);
        $select->join(array('arlc' => 'assessments_roles_locations_contacts'), 'arlc.arlc_a_id = rp_a_id and arlc.arlc_adr_id = rp_adr_id', array(), 'inner');
        $select->join(array('ar' => 'assessments_roles'), 'arlc.arlc_ar_id = ar.ar_id', array('_ar_id' => 'ar_id'), 'inner');
        $select->join(array('u1' => 'users'), 'arlc.arlc_u_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');
        $select->where('ar.ar_id IN(8,9,10)');
        
        $resultSet = $this->tableGateway->selectWith($select);

        if(!$resultSet->count()) {
            $select = $this->tableGateway->getSql()->select();
            $select->where('rp_id = ' . $id);
            $select->join(array('cr' => 'company_roles'), 'cr.cr_c_id = rp_c_id', array('_ar_id' => 'cr_ar_id'), 'inner');
            $select->join(array('u1' => 'users'), 'cr.cr_u_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');
            $select->where('cr.cr_ar_id IN(8,9,10)');

            $resultSet = $this->tableGateway->selectWith($select);
        }
        
        if($resultSet->count()) {
            foreach ($resultSet as $key => $rs) {
                if(!isset($result[$rs->_u_id])) {
                    $result[$rs->_u_id] = $rs;
                    $result[$rs->_u_id]->_ar_id = array($rs->_ar_id);
                } else {
                    $result[$rs->_u_id]->_ar_id[] = $rs->_ar_id;
                }
            }
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where('rp_id = ' . $id);
        $select->join(array('c' => 'companies'), 'c.c_id = rp_c_id', array(), 'inner');
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c.c_id', array(), 'inner');
        $select->join(array('u1' => 'users'), 'cc.cc_consultant_id = u1.u_id', array('_u_id' => 'u_id', '_u_name' => new \Zend\Db\Sql\Expression('CONCAT(u1.u_firstname, " ", u1.u_lastname)')), 'inner');

        $resultSet = $this->tableGateway->selectWith($select);

        if($resultSet) {
            foreach ($resultSet as $rs) {
                if(!isset($result[$rs->_u_id])) {
                    $result[$rs->_u_id] = $rs;
                    $result[$rs->_u_id]->_ar_id = array();
                }
            }
        }
        
        return $result;
    }

}