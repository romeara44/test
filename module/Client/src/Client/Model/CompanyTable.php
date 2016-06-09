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

class CompanyTable implements ServiceLocatorAwareInterface
{
    const DEFAULT_USERS_LIMUT = 3;

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

    /*public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }*/

    public function getCompanies($paginated = false, $orderBy = null, $order = null, $typeItems = 'all', $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select(array('c' => 'companies'));
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Company());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c_id', array('cc_consultant_id'), 'left');
            if (in_array($typeItems, array('all', 'contacts'))) {
                $select->join(array('u' => 'users'), 'u_company_id = c_id', array('u_id', 'u_firstname', 'u_lastname', 'u_office_phone', '_name' => new \Zend\Db\Sql\Expression('CONCAT(u.u_firstname, " ", u.u_lastname)'), '_id' => 'u_id', '_c_active' => new \Zend\Db\Sql\Expression('u.u_active')), 'left');
                if ($identity['u_role_id'] == User::ROLE_ADMIN) {
                    $select->where('u_active IN (0, 1)');
                } else {
                    $select->where('u_active = 1');
                }

                $select->where('u_id != ' . $identity['u_id']);

                if ($identity['u_role_id'] == User::ROLE_SALES_REP) {
                    $select->where('c_owner_u_id = ' . $identity['u_id']);
                } elseif (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
                    $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR cc.cc_consultant_id = ' . $identity['u_id'] . ' OR u_senior_consultant_u_id = ' . $identity['u_id'] . ')');
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc.cc_consultant_id IN (' . implode(',', $ids) . ') OR u_senior_consultant_u_id IN (' . implode(',', $ids) . '))');
                } elseif($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
                    if($identity['u_company_id_admin']) {
                        $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ' OR u_senior_consultant_u_id = ' . $identity['u_id'] . ' OR u_company_id = ' . $identity['u_company_id_admin'] . ')');
                    } else {
                        $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR u_senior_consultant_u_id = ' . $identity['u_id'] . ')');
                    }
                } elseif($identity['u_role_id'] == User::ROLE_TRAIL) {
                    $select->where('u_company_id = ' . $identity['u_company_id']);                
                }
            } else {
                if ($identity['u_role_id'] == User::ROLE_SALES_REP) {
                    $select->where('c_owner_u_id = ' . $identity['u_id']);
                } elseif (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
                    $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR cc.cc_consultant_id = ' . $identity['u_id'] . ')');
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc.cc_consultant_id IN (' . implode(',', $ids) . '))');
                } elseif($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
                    if($identity['u_company_id_admin']) {
                        $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ')');
                    } else {
                        $select->where('c_owner_u_id = ' . $identity['u_id']);
                    }
                } elseif($identity['u_role_id'] == User::ROLE_TRAIL) {
                    $select->where('c_id = ' . $identity['u_company_id']);
                }
            }            

            if ($typeItems == 'companies') {
                $select->columns(array('*', '_name' => 'c_name', '_id' => 'c_id', '_c_active' => new \Zend\Db\Sql\Expression('c.c_active')));
                if ($identity['u_role_id'] == User::ROLE_ADMIN) {
                    $select->where('c_active IN (0, 1)');
                } else {
                    $select->where('c_active = 1');
                }
            } else {
                if ($identity['u_role_id'] == User::ROLE_ADMIN) {
                    $select->where('u_active IN (0, 1)');
                } else {
                    $select->where('u_active = 1');
                }
            }

            ///////////////

            if ($typeItems == 'all') {
                $selectCom = new Select(array('c2' => 'companies'));
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Company());
                $selectCom->columns(array('*', new \Zend\Db\Sql\Expression('NULL'), new \Zend\Db\Sql\Expression('NULL'), new \Zend\Db\Sql\Expression('NULL'), new \Zend\Db\Sql\Expression('NULL'), '_name' => 'c_name', '_id' => 'c_id', '_c_active' => 'c_active'));
                $selectCom->join(array('cc2' => 'company_consultants'), 'cc2.cc_company_id = c_id', array('cc_consultant_id'), 'left');
                if ($identity['u_role_id'] == User::ROLE_ADMIN) {
                    $select->where('c_active IN (0, 1)');
                } else {
                    $selectCom->where('c_active = 1');
                }
                if ($identity) {
                    if ($identity['u_role_id'] == \Admin\Model\User::ROLE_SALES_REP) {
                        $selectCom->where('c_owner_u_id = ' . $identity['u_id']);
                    } elseif (in_array($identity['u_role_id'], array(\Admin\Model\User::ROLE_CONSULTANT))) {
                        $selectCom->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR cc2.cc_consultant_id = ' . $identity['u_id'] . ')');
                    } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                        $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                        $ids[] = $identity['u_id'];
                        $selectCom->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc2.cc_consultant_id IN (' . implode(',', $ids) . '))');
                    } elseif($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
                        if($identity['u_company_id_admin']) {
                            $selectCom->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ')');
                        } else {
                            $selectCom->where('c_owner_u_id = ' . $identity['u_id']);
                        }
                    } elseif($identity['u_role_id'] == User::ROLE_TRAIL) {
                        $selectCom->where('c_id = ' . $identity['u_company_id']);                
                    }
                }
                $select->group('u.u_id');
                $selectCom->group('c_id');
                $select->combine($selectCom, 'union', 'all');
            }

            if($typeItems == 'contacts') {
                $select->group('u.u_id');
            } else if($typeItems == 'companies') {
                $select->group('c_id');
            }

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            //echo $select->getSqlString($this->tableGateway->getAdapter()->getPlatform());
            //die;

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getSearchResultsSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where('c_name LIKE "%' . $searchValue . '%"');

        $select->columns(array('_id' => 'c_id',
                               '_name' => 'c_name',
                               '_type' => new \Zend\Db\Sql\Expression('CONCAT("company")'),
                               new \Zend\Db\Sql\Expression('NULL'),
                               new \Zend\Db\Sql\Expression('NULL')
                               )
                        );
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c_id', array(), 'left');

        if ($identity['u_role_id'] == User::ROLE_SALES_REP) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
            $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR cc.cc_consultant_id = ' . $identity['u_id'] . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc.cc_consultant_id IN (' . implode(',', $ids) . '))');
        } else if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            if($identity['u_company_id_admin']) {
                $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ')');
            } else {
                $select->where('c_owner_u_id = ' . $identity['u_id']);
            }
        }

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('c_active = 1');
        }

        $select->group('c_id');

        return $select;
    }

    public function getAssessmentsToDo($identity, $lastId)
    {
        $select = $this->tableGateway->getSql()->select();

        if ($lastId) {
            $select->where('(DATE_FORMAT(a_create_date, "%Y") <> DATE_FORMAT(NOW(), "%Y") OR a_create_date IS NULL) AND c_id < ' . $lastId);
        } else {
            $select->where('DATE_FORMAT(a_create_date, "%Y") <> DATE_FORMAT(NOW(), "%Y") OR a_create_date IS NULL');
        }

        $select->columns(array('lo_id' => new Expression('c_id'), 'lo_type' => new Expression('100'), new Expression('NULL'), new Expression('NULL'), new Expression('NULL'), new Expression('NULL'), new Expression('NULL')));
        $select->join(array('a' => 'assessments'), 'a_c_id = c_id', array('_rpc_name' => new Expression('c_name')), 'left');
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c_id', array('cc_consultant_id'), 'left');

        if ($identity['u_role_id'] == \Admin\Model\User::ROLE_SALES_REP) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(\Admin\Model\User::ROLE_CONSULTANT))) {
            $select->where('c_owner_u_id = ' . $identity['u_id'] . ' OR cc_consultant_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc_consultant_id IN (' . implode(',', $ids) . '))');
        } else if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        }

        $select->group('c_id');

        return $select;
    }


    public function getCompaniesPairs()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c_id', array('cc_consultant_id'), 'left');
        $select->where('c_active = 1');
        if ($identity['u_role_id'] == \Admin\Model\User::ROLE_SALES_REP) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(\Admin\Model\User::ROLE_CONSULTANT))) {
            $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR cc_consultant_id = ' . $identity['u_id'] . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc_consultant_id IN (' . implode(',', $ids) . '))');
        } else if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            $where_str = '(c_owner_u_id = ' . $identity['u_id'];
            if($identity['u_company_id_admin']) {
                $where_str .= ' OR c_id = ' . $identity['u_company_id_admin'];
            }
            if($identity['u_company_id']) {
                $where_str .= ' OR c_id = ' . $identity['u_company_id'];
            }
            $where_str .= ')';
            $select->where($where_str);
        }

        $select->group('c_id');

        $resultSet = $this->tableGateway->selectWith($select);

        $companies = array();
        foreach ($resultSet as $rs) {
            $companies[$rs->c_id] = $rs->c_name;
        }

        return $companies;
    }

    public function getClientCompanies($cId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('c_active = 1');
        $select->where('c_owner_u_id = ' . $cId);

        $resultSet = $this->tableGateway->selectWith($select);

        $result = array();

        foreach ($resultSet as $rs) {
            $result[] = $rs->c_id;
        }

        return $result;
    }

    public function getCompany($id)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $id  = (int) $id;
        $select = $this->tableGateway->getSql()->select();

        if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            if($identity['u_company_id_admin']) {
                $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ')');
            } else {
                $select->where('c_owner_u_id = ' . $identity['u_id']);
            }
        }

        $select->where('c_id = ' . $id);

        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('cc' => 'company_consultants'), new \Zend\Db\Sql\Expression('cc.cc_company_id = c_id'), array('_c_consultant_id' => new \Zend\Db\Sql\Expression('cc.cc_consultant_id')), 'inner');
        $select->where("c_id =" . $id);

        $consultants = $this->tableGateway->selectWith($select);

        foreach ($consultants as $consultant) {
            $row->_c_cur_consultants[$consultant->_c_consultant_id] = $consultant->_c_consultant_id;
        }
        $companyConsultantsTable = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable');

        if ($row->c_rel_type == \Client\Model\Company::RELATION_TYPE_PARENT) {
            $row->c_parent_type = $row->c_type;
            $row->_c_child_c_ids = $this->getChildCompaniesIds($id);
        } elseif ($row->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD) {
            $row->c_child_type = $row->c_type;
        }        

        return $row;
    }

    public function saveCompany(Company $company)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'c_name' => $company->c_name,
            'c_email' => $company->c_email,
            'c_phone' => $company->c_phone,
            'c_other_phone' => $company->c_other_phone,
            'c_other_phone_inner' => $company->c_other_phone_inner,
            'c_fax' => $company->c_fax,
            'c_website' => $company->c_website,
            'c_primary_adr_id' => $company->c_primary_adr_id,
            'c_update_u_id' => $company->c_update_u_id,
            'c_rel_type'    => 0,
            'c_type'    => 0,
            'c_parent_c_id'    => 0,
        );

        if (in_array($identity['u_role_id'], array(User::ROLE_SALES_REP, User::ROLE_SENIOR_CONSULTANT, User::ROLE_ADMIN))) {
            $data['c_rel_type'] = $company->c_rel_type;
            if ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_PARENT) {
                $data['c_type'] = $company->c_parent_type;        
                $data['c_parent_c_id'] = 0;    
            } elseif ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD) {
                $data['c_type'] = $company->c_child_type;
                $data['c_parent_c_id'] = $company->c_parent_c_id;
            }
        }

        $id = (int) $company->c_id;

        if (!$id) {
            $data['c_owner_u_id'] = $company->c_owner_u_id;
            $ownerContact         = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUser($company->c_owner_u_id);
            if($ownerContact && in_array($ownerContact->u_role_id, array(User::ROLE_CLIENT, User::ROLE_PARTIAL))) {
                $data['c_users_limit'] = self::DEFAULT_USERS_LIMUT;
            } else {
                $data['c_users_limit'] = 0;
            }
        }

        if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
            $company->_c_cur_consultants[] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $id);
        } else {
            if ($this->getCompany($id)) {
                if($identity['u_role_id'] == User::ROLE_ADMIN) {
                    $data['c_users_limit'] = $company->c_users_limit;
                }
                $data['c_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                $this->tableGateway->update($data, array('c_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        if (in_array($identity['u_role_id'], array(User::ROLE_SALES_REP, User::ROLE_SENIOR_CONSULTANT, User::ROLE_ADMIN))) {
            $companyConsultantsTable = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable');

            $companyConsultantsTable->deleteByCompanyId($id);

            if($company->_c_cur_consultants) {
                $company->_c_cur_consultants = array_unique($company->_c_cur_consultants);
                foreach ($company->_c_cur_consultants as $_c_cur_consultant) {
                    if($_c_cur_consultant) {
                        $companyConsultantsTable->saveCompanyConsultants(array('cc_company_id' => $id, 'cc_consultant_id' => $_c_cur_consultant));
                    }
                }
            }
        }

        if (in_array($identity['u_role_id'], array(User::ROLE_SALES_REP, User::ROLE_SENIOR_CONSULTANT, User::ROLE_ADMIN))) {
            $data = array(
                'c_parent_c_id' => 0,
                'c_rel_type' => 0,
            );
            $data_where = array(
                'c_parent_c_id' => $id,
                'c_rel_type' => \Client\Model\Company::RELATION_TYPE_CHILD,
            );
            $this->tableGateway->update($data, $data_where);

            if ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_PARENT) {
                if($company->_c_child_c_ids) {
                    $company->_c_child_c_ids = array_unique($company->_c_child_c_ids);
                    $data = array(
                        'c_parent_c_id' => $id,
                        'c_rel_type' => \Client\Model\Company::RELATION_TYPE_CHILD,
                    );
                    foreach ($company->_c_child_c_ids as $_c_child_c_id) {
                        if($_c_child_c_id) {
                            $this->tableGateway->update($data, array('c_id' => $_c_child_c_id));
                        }
                    }
                }
            } elseif ($company->c_rel_type == \Client\Model\Company::RELATION_TYPE_CHILD) {
                if ($company->c_parent_c_id) {
                    $data['c_rel_type'] = \Client\Model\Company::RELATION_TYPE_PARENT;
                    $data['c_parent_c_id'] = 0;
                    $this->tableGateway->update($data, array('c_id' => $company->c_parent_c_id));
                }
            }
        }

        return $id;
    }

    public function addClientCompany(Company $company)
    {
        $data = array(
            'c_name' => $company->c_name,
            'c_email' => $company->c_email,
            'c_phone' => $company->c_phone,
            'c_other_phone' => $company->c_other_phone,
            'c_other_phone_inner' => $company->c_other_phone_inner,
            'c_owner_u_id' => $company->c_owner_u_id,
            'c_primary_contact_u_id' => $company->c_primary_contact_u_id,
            'c_primary_contact_u_id' => $company->c_primary_contact_u_id,
            'c_users_limit' => self::DEFAULT_USERS_LIMUT,
            'c_active' => 1
        );

        $this->tableGateway->insert($data);
        $id = $this->tableGateway->lastInsertValue;

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $id);
        
        return $id;
    }

    public function checkClientLimitCompany()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (!in_array($identity['u_role_id'], array(User::ROLE_CLIENT, User::ROLE_PARTIAL))) {
            return true;
        }
        $select = $this->tableGateway->getSql()->select();

        $select->where('c_owner_u_id = ' . $identity['u_id']);
        $select->where('c_active = 1');

        $resultSet = $this->tableGateway->selectWith($select)->count();
        
        return (bool)($resultSet < 1);
    }

    public function setPrimaryAddressId($companyId, $addressId)
    {
        $data['c_primary_adr_id'] = $addressId;
        $this->tableGateway->update($data, array('c_id' => $companyId));
    }

    public function setPrimaryContactId($companyId, $uId)
    {
        $data['c_primary_contact_u_id'] = $uId;
        $this->tableGateway->update($data, array('c_id' => $companyId));
    }

    public function unsetPrimaryContactId($companyId)
    {
        $data['c_primary_contact_u_id'] = null;
        $this->tableGateway->update($data, array('c_id' => $companyId));
    }

    public function getUsersLimit($companyId)
    {
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('c_id =' . $companyId);

        $resultSet = $this->tableGateway->selectWith($select)->current();
        
        return is_object($resultSet) ? $resultSet->c_users_limit : null;
    }

    public function getUsersLimitsArray()
    {
        return array( '3' => 3
                    , '6' => 6
                    , '9' => 9
                    , '0' => 'unlimited'
                    );
    }

    public function saveAddresses($companyId, $post)
    {
        // save new addresses
        $counterAdr = 0;
        if (isset($post['adr_address1'])) {
            foreach ($post['adr_address1'] as $keyAdr => $adr) {
                if (trim($post['adr_address1'][$keyAdr]) == '') continue;

                $addressData['adr_name'] = $post['adr_name'][$keyAdr];
                $addressData['adr_address1'] = $post['adr_address1'][$keyAdr];
                $addressData['adr_address2'] = $post['adr_address2'][$keyAdr];
                $addressData['adr_city'] = $post['adr_city'][$keyAdr];
                $addressData['adr_state_id'] = $post['adr_state_id'][$keyAdr];
                $addressData['adr_zip'] = $post['adr_zip'][$keyAdr];

                // save to address table
                $sm = $this->getServiceLocator();
                $addressTable = $sm->get('Client\Model\AddressTable');

                $address = new Address();
                $address->exchangeArray($addressData);

                $addressId = $addressTable->saveAddress($address);

                // save to address item table
                $addressItem = new AddressItem();

                $addressItemData['cadr_type'] = $addressItem::COMPANY_TYPE;
                $addressItemData['cadr_c_id'] = $companyId;
                $addressItemData['cadr_adr_id'] = $addressId;

                $sm = $this->getServiceLocator();
                $addressItemTable = $sm->get('Client\Model\AddressItemTable');

                $addressItem->exchangeArray($addressItemData);

                $addressItemId = $addressItemTable->saveAddressItem($addressItem);

                if (($counterAdr == 0) && (!isset($post['exists_adr_address1']))) {
                    $this->setPrimaryAddressId($companyId, $addressId);
                }

                $counterAdr++;
            }
        }

        // save edit addresses
        $counterExistsAdr = 0;
        if (isset($post['exists_adr_address1'])) {
            foreach ($post['exists_adr_address1'] as $keyAdr => $adr) {
                if (trim($post['exists_adr_address1'][$keyAdr]) == '') continue;

                if ($counterExistsAdr == 0) {
                    $this->setPrimaryAddressId($companyId, $keyAdr);
                }

                $addressData['adr_name'] = $post['exists_adr_name'][$keyAdr];
                $addressData['adr_address1'] = $post['exists_adr_address1'][$keyAdr];
                $addressData['adr_address2'] = $post['exists_adr_address2'][$keyAdr];
                $addressData['adr_city'] = $post['exists_adr_city'][$keyAdr];
                $addressData['adr_state_id'] = $post['exists_adr_state_id'][$keyAdr];
                $addressData['adr_zip'] = $post['exists_adr_zip'][$keyAdr];
                $addressData['adr_id'] = $keyAdr;

                // save to address table
                $sm = $this->getServiceLocator();
                $addressTable = $sm->get('Client\Model\AddressTable');

                $address = new Address();
                $address->exchangeArray($addressData);

                $addressId = $addressTable->saveAddress($address);

                $counterExistsAdr++;
            }
        }
    }

    public function deleteCompany($id)
    {
        $data = array();
        $data['c_id'] = $id;
        $data['c_active'] = 0;
        $this->tableGateway->update($data, array('c_id' => $id));

        $this->getServiceLocator()->get('Admin\Model\UserTable')->deleteUsersByCompanyId($id);
        $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociateTable')->deleteBaByCompanyId($id);
        $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->deleteBlByCompanyId($id);
        $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->deleteAssessmentsByCompanyId($id);
        $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable')->deleteBreachremediationplansByCompanyId($id);
        $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->deleteRemediationplansByCompanyId($id);
        $this->getServiceLocator()->get('Traininglog\Model\TraininglogTable')->deleteTraininglogsByCompanyId($id);
        $this->getServiceLocator()->get('Securityreminder\Model\SecurityreminderTable')->deleteSecurityremindersByCompanyId($id);
        $this->getServiceLocator()->get('Disclosure\Model\DisclosureRequestTable')->deleteDisclosureRequestsByCompanyId($id);
        $this->getServiceLocator()->get('Disclosure\Model\DisclosureTrackingLogTable')->deleteDisclosureTrackingLogsByCompanyId($id);
        $this->getServiceLocator()->get('Disclosure\Model\VerbalLogTable')->deleteVerbalLogsByCompanyId($id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_COMPANY, $id);

        return true;
    }

    public function unarchiveCompany($id)
    {
        $data = array();
        $data['c_id'] = $id;
        $data['c_active'] = 1;
        $this->tableGateway->update($data, array('c_id' => $id));

        $this->getServiceLocator()->get('Admin\Model\UserTable')->deleteUsersByCompanyId($id, 1);
        $this->getServiceLocator()->get('Businessassociate\Model\BusinessassociateTable')->deleteBaByCompanyId($id, 1);
        $this->getServiceLocator()->get('Breachlog\Model\BreachlogTable')->deleteBlByCompanyId($id, 1);
        $this->getServiceLocator()->get('Assessment\Model\AssessmentTable')->deleteAssessmentsByCompanyId($id, 1);
        $this->getServiceLocator()->get('Breachlog\Model\BreachremediationplanTable')->deleteBreachremediationplansByCompanyId($id, 1);
        $this->getServiceLocator()->get('Assessment\Model\RemediationplanTable')->deleteRemediationplansByCompanyId($id, 1);
        $this->getServiceLocator()->get('Traininglog\Model\TraininglogTable')->deleteTraininglogsByCompanyId($id, 1);
        $this->getServiceLocator()->get('Securityreminder\Model\SecurityreminderTable')->deleteSecurityremindersByCompanyId($id, 1);
        //$this->getServiceLocator()->get('Disclosure\Model\DisclosureRequestTable')->deleteDisclosureTrackingLogsByCompanyId($id, 1);
        //$this->getServiceLocator()->get('Disclosure\Model\DisclosureTrackingLogTable')->deleteDisclosureTrackingLogsByCompanyId($id, 1);
        $this->getServiceLocator()->get('Disclosure\Model\VerbalLogTable')->deleteVerbalLogsByCompanyId($id, 1);

        return true;
    }

    public function getCompaniesForParent($identity)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c_id', array('cc_consultant_id'), 'left');
        
        if ($identity['u_role_id'] == User::ROLE_SALES_REP) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
            $select->where('c_owner_u_id = ' . $identity['u_id'] . ' OR cc.cc_consultant_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc.cc_consultant_id IN (' . implode(',', $ids) . '))');
        } else if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            if($identity['u_company_id_admin']) {
                $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ')');
            } else {
                $select->where('c_owner_u_id = ' . $identity['u_id']);
            }
        }

        $select->columns(array('c_id', 'c_name'));
        if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            $select->where('c_active IN (0, 1)');
        } else {
            $select->where('c_active = 1');
        }

        $select->group('c_id');

        $select->where('c_rel_type != ' . \Client\Model\Company::RELATION_TYPE_CHILD);

        $resultSet = $this->tableGateway->selectWith($select);

        $result = array();

        foreach ($resultSet as $rs) {
            $result[$rs->c_id] = $rs->c_name;
        }

        return $result;
    }

    public function getCompaniesForChild($identity)
    {
        $select = $this->tableGateway->getSql()->select();
        
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = c_id', array('cc_consultant_id'), 'left');
        
        if ($identity['u_role_id'] == User::ROLE_SALES_REP) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
            $select->where('c_owner_u_id = ' . $identity['u_id'] . ' OR cc.cc_consultant_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc.cc_consultant_id IN (' . implode(',', $ids) . '))');
        } else if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            if($identity['u_company_id_admin']) {
                $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ')');
            } else {
                $select->where('c_owner_u_id = ' . $identity['u_id']);
            }
        }

        $select->columns(array('c_id', 'c_name'));
        if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            $select->where('c_active IN (0, 1)');
        } else {
            $select->where('c_active = 1');
        }

        $select->group('c_id');

        $select->where('c_rel_type != ' . \Client\Model\Company::RELATION_TYPE_PARENT);

        $resultSet = $this->tableGateway->selectWith($select);

        $result = array();

        foreach ($resultSet as $rs) {
            $result[$rs->c_id] = $rs->c_name;
        }

        return $result;
    }

    public function getChildCompaniesIds($c_id = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('c_active = 1');        
        $select->where('c_rel_type = ' . \Client\Model\Company::RELATION_TYPE_CHILD);
        if ($c_id) {
            $select->where('c_parent_c_id = ' . $c_id);
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $result = array();

        foreach ($resultSet as $rs) {
            $result[] = $rs->c_id;
        }

        return $result;
    }

    public function getParentCompaniesIds()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('c_active = 1');        
        $select->where('c_rel_type = ' . \Client\Model\Company::RELATION_TYPE_PARENT);

        $resultSet = $this->tableGateway->selectWith($select);

        $result = array();

        foreach ($resultSet as $rs) {
            $result[] = $rs->c_id;
        }

        return $result;
    }

    public function getCompaniesByUserEmail($email)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->join(array('u' => 'users'), 'u.u_company_id = c_id', array(), 'right');
        $select->where('u_email = "' . $email . '"');
        return $this->tableGateway->selectWith($select);
    }

    public function getClientCompany($id)
    {
        $id  = (int) $id;
        $select = $this->tableGateway->getSql()->select();
        $select->where('c_id = ' . $id);
        $select->where('c_active = 1');
        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }
        return $row;
    }

    public function getCompanyLocations($id)
    {
        $locations = [];

        $id  = (int) $id;
        $addresses = $this->getServiceLocator()->get('Client\Model\AddressTable')->getAddresses($id);

        foreach ($addresses as $r) {
            $locations[$r->adr_id] = $r->adr_name;
        }
        return $locations;
    }
}