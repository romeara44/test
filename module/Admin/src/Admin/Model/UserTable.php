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

class UserTable implements ServiceLocatorAwareInterface
{
    public static $reportCondition = array(
        0 => 'DATE_FORMAT(?, "%c") = MONTH(NOW()) AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        1 => 'DATE_FORMAT(?, "%c") = (MONTH(NOW()) - 1) AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        2 => 'DATE_FORMAT(?, "%c") > (MONTH(NOW()) - 3) AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        3 => 'DATE_FORMAT(?, "%c") >= 1 AND DATE_FORMAT(?, "%c") <= 3 AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        4 => 'DATE_FORMAT(?, "%c") >= 4 AND DATE_FORMAT(?, "%c") <= 6 AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        5 => 'DATE_FORMAT(?, "%c") >= 7 AND DATE_FORMAT(?, "%c") <= 9 AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        6 => 'DATE_FORMAT(?, "%c") >= 10 AND DATE_FORMAT(?, "%c") <= 12 AND DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        7 => 'DATE_FORMAT(?, "%Y") = YEAR(NOW())',
        8 => 'DATE_FORMAT(?, "%Y") = YEAR(NOW()) - 1',
    );

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

    /*public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }*/

    public function fetchAll($paginated = false, $orderBy = null, $order = null, $roleFilter = 0, $isConsultant = false)
    {
        if ($paginated) {
            $select = new Select('users');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new User());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            $select->join(array('r' => 'roles'), 'role_id = u_role_id', array('_rolename' => 'role_name'), 'left');
            $select->columns(array( '*'
                                  , '_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')
                                  , '_active' => new \Zend\Db\Sql\Expression('IF(u_active = ' . User::STATUS_ACTIVE . ', "Active", "Archived")')
                                  )
                            );
            /*if ($identity['u_role_id'] == User::ROLE_ADMIN) {
                $select->where('u_active = 1');
            }
           
*/
            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            if ((int) $roleFilter) {
                $select->where('u_role_id = ' . $roleFilter);
            }

            if(!$isConsultant) {
                $select->where('u_role_id <> ' . 1); // without admin
            } else {
                $select->where('u_role_id IN (5, 6, 7)');
            }

            $paginator = new Paginator($paginatorAdapter);


            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getSearchResultsUsersSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where('u_active = 1');
        $select->where('(u_firstname LIKE "%' . $searchValue . '%" OR u_lastname LIKE "%' . $searchValue . '%")');

        $select->columns(array('_id' => 'u_id',
                               '_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'),
                               '_item_type' => new \Zend\Db\Sql\Expression('NULL'),
                               '_type' => new \Zend\Db\Sql\Expression('CONCAT("user")'),
                               new \Zend\Db\Sql\Expression('NULL'),
                               new \Zend\Db\Sql\Expression('NULL')
                               )
                        );

        $select->where('u_role_id <> 1'); // without admin

        return $select;
    }

    public function getSearchResultsSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->join(array('compa' => 'companies'), 'u_company_id = compa.c_id', array(), 'left');
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = compa.c_id', array(), 'left');

        $select->where('u_id != ' . $identity['u_id']);

        if ($identity['u_role_id'] == User::ROLE_SALES_REP) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT))) {
            $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR cc.cc_consultant_id = ' . $identity['u_id'] . ' OR u_senior_consultant_u_id = ' . $identity['u_id'] . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('(c_owner_u_id IN (' . implode(',', $ids) . ') OR cc.cc_consultant_id IN (' . implode(',', $ids) . ') OR u_senior_consultant_u_id IN (' . implode(',', $ids) . '))');
        } else if($identity['u_role_id'] == User::ROLE_CLIENT || $identity['u_role_id'] == User::ROLE_PARTIAL) {
            if($identity['u_company_id_admin']) {
                $select->where('(c_owner_u_id = ' . $identity['u_id'] . ' OR c_id = ' . $identity['u_company_id_admin'] . ' OR u_senior_consultant_u_id = ' . $identity['u_id'] . ' OR u_company_id = ' . $identity['u_company_id_admin'] . ')');
            } else {
                $select->where('(c_owner_u_id = ' . $identity['u_id'] . 'OR u_senior_consultant_u_id = ' . $identity['u_id'] . ')');
            }
        }
        
        $select->where('(u_firstname LIKE "%' . $searchValue . '%" OR u_lastname LIKE "%' . $searchValue . '%" OR compa.c_name LIKE "%' . $searchValue . '%")');

        $select->columns(array('_id' => 'u_id',
                               '_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'),
                               '_item_type' => new \Zend\Db\Sql\Expression('NULL'),
                               '_type' => new \Zend\Db\Sql\Expression('CONCAT("contact")'), 
                               '_status' => new \Zend\Db\Sql\Expression('NULL'),
                               '_date' => new \Zend\Db\Sql\Expression('NULL')
                               )
                        );

        //$select->where('u_role_id = 5'); // without admin

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('u_active = 1');
        }

        $select->group('u_id');

        return $select;
    }

    public function getUsersByCompany($companyId = null, $primaryContactId = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_active = 1');
        if($companyId) {
            $select->where('u_company_id = ' . $companyId);
        } else {
            $select->where('u_company_id IS NULL');
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $users = array();
        foreach ($resultSet as $rs) {
            if ($rs->u_id == $primaryContactId) {
                $users[] = $rs;
                break;
            }
        }

        $resultSet = $this->tableGateway->selectWith($select);
        foreach ($resultSet as $rs) {
            if ($primaryContactId && ($rs->u_id == $primaryContactId)) {
                continue;
            } else {
                $users[] = $rs;
            }
        }

        return $users;
    }

    public function checkCompanyLimitClient($companyId)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if (!in_array($identity['u_role_id'], array(User::ROLE_CLIENT, User::ROLE_PARTIAL))) {
            return true;
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where('u_active = 1');
        $select->where('u_company_id = ' . $companyId);

        $resultSet = $this->tableGateway->selectWith($select)->count();

        $value = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getUsersLimit($companyId);

        return (bool)($value < 1 || $resultSet < $value);
    }

    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('u_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getUserByEmail($email, $company_id = null)
    {
        $params = array('u_email' => $email);
        if ($company_id) {
            $params['u_company_id'] = $company_id;
        }
        $rowset = $this->tableGateway->select($params);
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        return $row;
    }

    public function saveUser(User $user)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'u_role_id' => $user->u_role_id,
            'u_senior_consultant_u_id' => $user->u_senior_consultant_u_id,
            'u_company_id' => $user->u_company_id,
            'u_company_id_admin' => $user->u_company_id_admin,
            'u_firstname' => $user->u_firstname,
            'u_lastname' => $user->u_lastname,
            'u_email' => $user->u_email,
            'u_title' => $user->u_title,
            'u_company' => $user->u_company,
            'u_office_phone' => $user->u_office_phone,
            'u_office_phone_inner' => $user->u_office_phone_inner,
            'u_direct_phone' => $user->u_direct_phone,
            'u_direct_phone_inner' => $user->u_direct_phone_inner,
            'u_cell_phone' => $user->u_cell_phone,
            'u_other_phone' => $user->u_other_phone,
            'u_other_phone_inner' => $user->u_other_phone_inner,
            'u_fax' => $user->u_fax,
            'u_address1' => $user->u_address1,
            'u_address2' => $user->u_address2,
            'u_city' => $user->u_city,
            'u_zip' => $user->u_zip,
            'u_state_id' => $user->u_state_id,
            'u_confirmed' => isset($user->u_confirmed) ? $user->u_confirmed : 1,
        );

        if (!(int) $user->u_senior_consultant_u_id) {
            unset($data['u_senior_consultant_u_id']);
        }

        if($identity['u_role_id'] == User::ROLE_ADMIN || $identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $data['u_grant_to_disclosures'] = $user->u_grant_to_disclosures ? $user->u_grant_to_disclosures : 0;
            $data['u_grant_to_breach'] = $user->u_grant_to_breach ? $user->u_grant_to_breach : 0;
        }

        if (!(int) $user->u_state_id) {
            unset($data['u_state_id']);
        }

        $id = (int) $user->u_id;

        if ($id == 0) {
            $data['u_first_login'] = 1;
            $data['u_hash'] = sha1($user->u_email . time());
            if (isset($user->u_sent_password) && ($user->u_sent_password == 0)) {
                $data['u_sent_password'] = 0;
            } else {
                $password = $this->generatePassword();
                $data['u_password'] = sha1($password);
            }

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            if (isset($user->u_sent_password) && ($user->u_sent_password == 0)) {
            } else {
                $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'createuser', 'uId' => $id, 'password' => htmlspecialchars($password)));
            }
        } else {
            if ($this->getUser($id)) {
                $this->tableGateway->update($data, array('u_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function registerUser(User $user)
    {
        $data = array(
            'u_role_id' => $user->u_role_id,
            'u_senior_consultant_u_id' => $user->u_senior_consultant_u_id,
            'u_company_id' => $user->u_company_id,
            'u_firstname' => $user->u_firstname,
            'u_lastname' => $user->u_lastname,
            'u_email' => $user->u_email,
            'u_title' => $user->u_title,
            'u_company' => $user->u_company,
            'u_office_phone' => $user->u_office_phone,
            'u_office_phone_inner' => $user->u_office_phone_inner,
            'u_direct_phone' => $user->u_direct_phone,
            'u_direct_phone_inner' => $user->u_direct_phone_inner,
            'u_cell_phone' => $user->u_cell_phone,
            'u_other_phone' => $user->u_other_phone,
            'u_other_phone_inner' => $user->u_other_phone_inner,
            'u_fax' => $user->u_fax,
            'u_address1' => $user->u_address1,
            'u_address2' => $user->u_address2,
            'u_city' => $user->u_city,
            'u_zip' => $user->u_zip,
            'u_first_login' => $user->u_first_login,
            'u_register' => $user->u_register,
            'u_zip' => $user->u_zip,
            'u_state_id' => $user->u_state_id,
            'u_confirmed' => $user->u_confirmed,
        );
    
        $data['u_hash'] = sha1($user->u_email . time());
        if (isset($user->u_sent_password) && ($user->u_sent_password == 0)) {
            $data['u_sent_password'] = 0;
        } else {
            $password = $this->generatePassword();
            $data['u_password'] = sha1($password);
        }

        $this->tableGateway->insert($data);
        $id = $this->tableGateway->lastInsertValue;

        if (isset($user->u_sent_password) && ($user->u_sent_password == 0)) {
        } else {
            $link = "http://" . $_SERVER['HTTP_HOST'] . '/user/confirm/' . $id . '/'. $data['u_hash'];
            $link = '<a href="' . $link . '">' . $link . '</a>';
            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'registeruser', 'uId' => $id, 'password' => htmlspecialchars($password), 'link' => $link));
        }
        

        return $id;
    }

    public function sendPasswordForgotRequest($email)
    {
        if (!$this->checkIfUserExists($email)) {
            return false;
        }

        $adminId = null;
        $user    = $this->getUserByEmail($email);

        //if ($user->u_role_id == \Admin\Model\User::ROLE_ADMIN) {
            return $this->resetPassword($user->u_id);
        //}
/*
        if($user->u_company_id && $user->u_company_id != $user->u_company_id_admin) {
            $select = $this->tableGateway->getSql()->select();
            $select->where('u_company_id_admin = ' . $user->u_company_id);
            $select->where('u_active = 1');

            $resultSet = $this->tableGateway->selectWith($select);
            $row       = $resultSet->current();
            if ($row) {
                $adminId = $row->u_id;
            }
        }

        if(!$adminId) {
            $select = $this->tableGateway->getSql()->select();
            $select->where('u_role_id = ' . User::ROLE_ADMIN);
            $select->where('u_active = 1');

            $resultSet = $this->tableGateway->selectWith($select);
            $row       = $resultSet->current();
            if ($row) {
                $adminId = $row->u_id;
            }
        }

        if($adminId) {
            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'forgot_password_request', 'uId' => $adminId, 'forgot_password_user' => $user));
            //$this->tableGateway->update(array('u_forgot_password' => 1), array('u_id' => $user->u_id));
            return true;
        }

        return false;*/
    }

    public function setConfirmed($uId, $hash)
    {
        $user = $this->getUser($uId);
        if  ($user->u_hash == $hash) {
            $data['u_id'] = $uId;
            $data['u_confirmed'] = 1;
            $this->tableGateway->update($data, array('u_id' => $uId));

            return true;
        } else {
            return false;
        }
    }

    public function unSetFirstLogin($uid)
    {
        $data['u_id'] = $uid;
        $data['u_first_login'] = 0;
        $this->tableGateway->update($data, array('u_id' => $uid));

        return true;
    }

    public function checkIfUserExists($email, $uId = 0, $u_company_id = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_email = "' . $email . '"');
        $select->where('u_active = 1');

        if ($u_company_id) {
            $select->where('u_company_id = ' . $u_company_id);
        }

        if ($uId) {
            $select->where('u_id <> ' . $uId);
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return true;
    }

    public function checkIfUserExistsByIdAndHash($uid, $hash)
    {
        $user = $this->getUser($uid);
        if  (is_object($user) && ($user->u_hash == $hash)) {
            return true;
        } else {
            return false;
        }
    }

    public function setNewPassword($uid, $password)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_id = ' . $uid);

        $user = $this->tableGateway->selectWith($select)->current();

        $data['u_password'] = sha1($password);
        $data['u_sent_password'] = 1;
        $this->tableGateway->update($data, array('u_email' => $user->u_email));

        return true;
    }

    public function getAll($paginated = false, $orderBy = null, $order = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('users');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new User());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }
            if (isset($params['userRole'])) {
                $select->where('u_role_id = '.$params['userRole']);
            }
            if (isset($params['userStatus'])) {
                $select->where('u_active = '.$params['userStatus']);
            }

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        $resultSet = $this->tableGateway->select();

        return $resultSet;
    }

    public function getSeniorConsultants()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_role_id = ' . User::ROLE_SENIOR_CONSULTANT);
        $select->where('u_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $users = array();
        foreach ($resultSet as $rs) {
            $users[$rs->u_id] = $rs->u_firstname . ' ' . $rs->u_lastname;
        }

        return $users;
    }

    public function getUsersByRole($roles = array(0))
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_role_id IN (' . implode(',', $roles) . ')');
        $select->where('u_active = 1');
        $select->order('u_role_id');

        $resultSet = $this->tableGateway->selectWith($select);

        $users = array();
        foreach ($resultSet as $rs) {
            $users[$rs->u_id] = $rs->u_firstname . ' ' . $rs->u_lastname;
        }

        return $users;
    }

    public function getUsersByIds($uIds = array(0))
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_id IN (' . implode(',', $uIds) . ')');
        $select->where('u_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $users = array();
        foreach ($resultSet as $rs) {
            $users[$rs->u_id] = $rs->u_firstname . ' ' . $rs->u_lastname;
        }

        return $users;
    }

    public function getContactsByCompanyId($cId = 0, $withConsultants = false)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where('u_active = 1');

        if ($withConsultants) {

            $authService = new \Zend\Authentication\AuthenticationService();
            $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
            $identity = $authService->getIdentity();

            $ids = array(0);
            if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                $ids[] = $identity['u_id'];
            } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                $ids = $this->getConsultantIdsForSenior($identity['u_id']);
                $ids[] = $identity['u_id'];
            }
            $select->where('u_company_id = ' . (int) $cId . ' OR u_id IN (' . implode(',', $ids) . ')');
        } else {
            $select->where('u_company_id = ' . (int) $cId);
        }

        $resultSet = $this->tableGateway->selectWith($select);

        $users = array();
        foreach ($resultSet as $rs) {
            $users[$rs->u_id] = $rs->u_firstname . ' ' . $rs->u_lastname;
        }

        return $users;
    }

    public function deleteUser($id)
    {
        $data['u_id'] = $id;
        $data['u_active'] = 0;
        
        $this->tableGateway->update($data, array('u_id' => $id));

        $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->deleteTrainingManager($id);

        return true;
    }

    public function unarchiveUser($id)
    {
        $data['u_id'] = $id;
        $data['u_active'] = 1;
        $this->tableGateway->update($data, array('u_id' => $id));

        return true;
    }

    public function deleteUsersByCompanyId($cId, $value = 0)
    {
        $data['u_active'] = $value;
        $this->tableGateway->update($data, array('u_company_id' => $cId));

        return true;
    }


    public function getConsultantIdsForSenior($uId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_senior_consultant_u_id = ' . $uId);
        $select->where('u_role_id = 3');
        $select->where('u_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $users = array(0);
        foreach ($resultSet as $rs) {
            $users[] = $rs->u_id;
        }

        return $users;
    }

    public function checkHasPartial($cId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_company_id = ' . $cId);
        $select->where('u_role_id = ' . User::ROLE_PARTIAL);

        return $this->tableGateway->selectWith($select)->count();
    }

    public function activatePartialsByCompany($cId)
    {
        $data = array();
        $data['u_role_id'] = User::ROLE_CLIENT;

        if($this->tableGateway->update($data, array('u_company_id' => $cId, 'u_role_id' => User::ROLE_PARTIAL))) {
            return true;
        } else {
            return false;
        }
    }

    public function agreeTermsUser($id)
    {
        $data['u_id'] = $id;
        $data['u_first_login'] = 0;
        $this->tableGateway->update($data, array('u_id' => $id));

        return true;
    }

    public function checkPassword($password)
    {
        if (strlen($password) < 8 || !preg_match("/[A-Z]+/", $password) || !preg_match("/[a-z]+/", $password) || !preg_match("/\d+/", $password) || !preg_match("/[~!@#$%?^&*()_+`\-={}[\]:;<>.,\/\\\\]+/", $password)) {
            return false;
        } else {
            return true;
        }
    }

    public function generatePassword($password_length = 8)
    {
        $str = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $chars_length = 25;
        $up_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $up_chars_length = 25;
        $digits = '0123456789';
        $digits_length = 9;
        $spec_chars = '~!@#$%?^&*()_+`-={}[]:;<>.,/\\';
        $spec_chars_length = strlen($spec_chars) - 1;

        $chars_count = rand(1, $password_length - 3);
        $up_chars_count = rand(1, $password_length - $chars_count - 2);
        $digits_count = rand(1, $password_length - $chars_count - $up_chars_count - 1);
        $spec_chars_count = rand(1, $password_length - $chars_count - $up_chars_count - $digits_count);

        for ($i=0; $i < $chars_count; $i++) { 
            $ind = rand(0, $chars_length);
            $str .= $chars[$ind];
        }
        for ($i=0; $i < $up_chars_count; $i++) { 
            $ind = rand(0, $up_chars_length);
            $str .= $up_chars[$ind];
        }
        for ($i=0; $i < $digits_count; $i++) { 
            $ind = rand(0, $digits_length);
            $str .= $digits[$ind];
        }
        for ($i=0; $i < $spec_chars_count; $i++) { 
            $ind = rand(0, $spec_chars_length);
            $str .= $spec_chars[$ind];
        }

        return str_shuffle($str);
    }

    public function resetPassword($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_id = ' . $id);

        $user = $this->tableGateway->selectWith($select)->current();

        if($user/* && ($user->u_role_id == \Admin\Model\User::ROLE_ADMIN || $this->checkClientAdministrationAccess($user))*/) {
            $password = $this->generatePassword();
            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'reset_password', 'uId' => $user->u_id, 'password' => htmlspecialchars($password)));
            $this->setNewPassword($user->u_id, $password);
            //$this->tableGateway->update(array('u_forgot_password' => 0), array('u_id' => $user->u_id));
            
            return true;
        }

        return false;
    }
    
    public function checkClientAdministrationAccess($user = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return isset($user->u_id) &&
              ( $identity['u_role_id'] == User::ROLE_ADMIN ||
                ($identity['u_company_id_admin'] && $user->u_company_id == $identity['u_company_id_admin']) ||
                ($user->u_senior_consultant_u_id && $user->u_senior_consultant_u_id == $identity['u_id'])
              );
    }

    public function checkCompanyAdministrationAccess($company = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return isset($company->c_id) &&
              ( $identity['u_role_id'] == User::ROLE_ADMIN ||
                ($identity['u_company_id_admin'] && $company->c_id == $identity['u_company_id_admin']) ||
                ($company->c_consultant_u_id && $company->c_consultant_u_id == $identity['u_id'])
              );
    }

    public function updateFailedLoginCount($user, $reset = false)
    {
        $locked = 0;
        if($user->u_role_id != User::ROLE_ADMIN) {
            if($reset) {
                $data['u_locked'] = 0;
                $data['u_failed_logins_count'] = $locked;
                $data['u_locked_unlocked_date'] = date('Y-m-d H:i:s');
            } else {
                $data['u_failed_logins_count'] = ++$user->u_failed_logins_count;
                $locked = (int) $user->u_failed_logins_count >= $this->getServiceLocator()->get('Sitesetting\Model\SitesettingTable')->getValueByName(\Sitesetting\Model\Sitesetting::FAILED_USER_LOGINS_LIMIT);
                if($locked) {
                    $select = $this->tableGateway->getSql()->select();
                    $select->columns(array('u_id'));
                    $select->quantifier('DISTINCT');
                    $whereStr = '(u_role_id = ' . User::ROLE_ADMIN;
                    if($user->u_company_id) {
                        $whereStr .= ' OR u_company_id_admin = ' . $user->u_company_id;
                    }
                    if($user->u_senior_consultant_u_id) {
                        $whereStr .= ' OR u_id = ' . $user->u_senior_consultant_u_id;
                    }
                    $select->where($whereStr . ')');
                    $select->where('u_active = 1');

                    $resultSet = $this->tableGateway->selectWith($select);

                    foreach ($resultSet as $rs) {
                        $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'user_locked', 'uId' => $rs->u_id, 'locked_user' => $user));
                    }

                    $data['u_locked'] = $locked;
                    $data['u_locked_unlocked_date'] = date('Y-m-d H:i:s');
                }
            }

            $this->tableGateway->update($data, array('u_email' => $user->u_email));
        }

        return $locked;
    }

    public function getLockedCompanyUsers($companyId)
    {
        $select = new Select('users');
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new User());
        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            $resultSetPrototype
        );

        $select->columns(array('u_id',
                               'u_locked_unlocked_date',
                               'u_locked',
                               '_username' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')
                               )
                        );

        $select->where('u_company_id = ' . $companyId);
        $select->where('u_active = 1');
        $select->where('u_locked = 1');

        $select->order('u_locked_unlocked_date DESC');

        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function lockUser($id, $lock)
    {
        $user = $this->getUser($id);

        if($this->checkClientAdministrationAccess($user)) {

            $data['u_locked']               = $lock;
            $data['u_failed_logins_count']  = 0;
            $data['u_locked_unlocked_date'] = date('Y-m-d H:i:s');

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveUserFileLog(($lock ? 'Lock' : 'Unlock') . ' client "' . $id . '"');

            return $this->tableGateway->update($data, array('u_id' => $id));
        } else {
            return false;
        }
    }

    public function getModulesAccessCode($id)
    {
        $user = $this->getUser($id);

        $application_vars = new \Zend\Session\Container('application_vars');
        $expiration = $application_vars->storage['module_access_code_expiration'];

        if((in_array($user->u_role_id, array(\Admin\Model\User::ROLE_ADMIN, \Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT))
            || ($user->u_grant_to_breach || $user->u_grant_to_disclosures))
            && $user->u_modules_access_code 
            && $user->u_modules_access_code_created + $expiration > time()) {

            return $user->u_modules_access_code;
        } else {
            $data['u_modules_access_code']         = null;
            $data['u_modules_access_code_created'] = null;

             $this->tableGateway->update($data, array('u_id' => $id));

             return false;
        }
    }

    public function sendModulesAccessCode($id)
    {
        $user = $this->getUser($id);

        if($user && (in_array($user->u_role_id, array(\Admin\Model\User::ROLE_ADMIN, \Admin\Model\User::ROLE_CONSULTANT, \Admin\Model\User::ROLE_SENIOR_CONSULTANT)) 
                    || ($user->u_grant_to_breach || $user->u_grant_to_disclosures))
            ) {
            $modulesAccessCode = substr(sha1($user->u_email . time()), 0, 8);

            $data['u_modules_access_code']         = $modulesAccessCode;
            $data['u_modules_access_code_created'] = time();

            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey'         => 'send_modules_access_code',
                                                                                                                        'addto'               => $user->u_email,
                                                                                                                        'addToName'           => $user->u_firstname . ' ' . $user->u_lastname,
                                                                                                                        'modules_access_code' => $modulesAccessCode
                                                                                                                         ));

            return $this->tableGateway->update($data, array('u_id' => $id));
        } else {
            return false;
        }
    }

    public function getModulesAccess($id, $code)
    {
        $existsCode = $this->getModulesAccessCode($id);

        if($existsCode && $existsCode == $code) {
            $data['u_modules_access_code']         = null;
            $data['u_modules_access_code_created'] = null;

            $this->tableGateway->update($data, array('u_id' => $id));

            return true;
        } else {
            return false;
        }
    }

    public function checkModulesAccess($module)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        switch ($module) {
            case 'breach':
                $moduleAccessName = 'u_grant_to_breach';
                break;
            case 'disclosures':
                $moduleAccessName = 'u_grant_to_disclosures';
                break;
            default:
                return false;
                break;
        }

        if($identity
            && ($identity['has_modules_access'] == 1
                || $identity[$moduleAccessName] == 1)
            ) {
            return true;
        }

        return false;
    }

    public function getUsersForTrainersList($cId)
    {
        $company = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($cId);

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('cc' => 'company_consultants'), 'cc.cc_consultant_id = u_id', array(), 'left');

        $uIds = array();

        if($company->c_consultant_u_id) {
            $uIds[] = $company->c_consultant_u_id;
        }
        if($training_managers = $this->getServiceLocator()->get('Client\Model\CompanyTrainingManagersTable')->getTrainingManagersIdsForCompany($cId)) {
            $uIds = array_merge($uIds, $training_managers);
        }

        if($uIds) {
            $select->where('(cc.cc_company_id = ' . $company->c_id . ' OR u_id IN (' . implode(',', $uIds) . '))');
        } else {
            $select->where('cc.cc_company_id = ' . $company->c_id);
        }

        $select->where('u_active = 1');

        return $this->tableGateway->selectWith($select);
    }

}