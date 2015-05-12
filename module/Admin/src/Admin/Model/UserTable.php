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

    public function fetchAll($paginated = false, $orderBy = null, $order = null, $roleFilter = 0, $searchValue = null, $params = array())
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

           // $select->where('u_active = 1');

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            if ((int) $roleFilter) {
                $select->where('u_role_id = ' . $roleFilter);
            }

            $select->where('u_role_id <> ' . 1); // without admin

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

        $select->columns(array('_id' => 'u_id', '_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'), '_type' => new \Zend\Db\Sql\Expression('CONCAT("user")')));

        $select->where('u_role_id <> 1'); // without admin

        return $select;
    }

    public function getSearchResultsSelect($searchValue, $identity)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_active = 1');
        $select->where('(u_firstname LIKE "%' . $searchValue . '%" OR u_lastname LIKE "%' . $searchValue . '%")');

        $select->columns(array('_id' => 'u_id', '_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)'), '_type' => new \Zend\Db\Sql\Expression('CONCAT("contact")')));

        $select->where('u_role_id = 5'); // without admin

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

    public function getUserByEmail($email)
    {
        $rowset = $this->tableGateway->select(array('u_email' => $email));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        return $row;
    }

    public function saveUser(User $user)
    {
        $data = array(
            'u_role_id' => $user->u_role_id,
            'u_senior_consultant_u_id' => $user->u_senior_consultant_u_id,
            'u_company_id' => $user->u_company_id,
            'u_company_id_admin' => $user->u_company_id_admin,
            'u_grant_to_disclosures' => $user->u_grant_to_disclosures,
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
                $password = sha1($user->u_email . time());
                $password = substr($password, 0, 6);
                $data['u_password'] = sha1($password);
            }

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            if (isset($user->u_sent_password) && ($user->u_sent_password == 0)) {
            } else {
                $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'createuser', 'uId' => $id, 'password' => $password));
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
            $password = sha1($user->u_email . time());
            $password = substr($password, 0, 6);
            $data['u_password'] = sha1($password);
        }

        $this->tableGateway->insert($data);
        $id = $this->tableGateway->lastInsertValue;

        if (isset($user->u_sent_password) && ($user->u_sent_password == 0)) {
        } else {
            $link = "http://" . $_SERVER['HTTP_HOST'] . '/user/confirm/' . $id . '/'. $data['u_hash'];
            $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'registeruser', 'uId' => $id, 'password' => $password, 'link' => $link));
        }
        

        return $id;
    }

    public function sendPasswordReminder($email)
    {
        if (!$this->checkIfUserExists($email)) {
            return false;
        }

        $user = $this->getUserByEmail($email);

        $this->getServiceLocator()->get('Mail\Model\MailtemplateTable')->sendMail($this->getServiceLocator(), array('templateKey' => 'forgotpassword', 'uId' => $user->u_id, 'link' => '<a style="color: #15c" href="http://' . $_SERVER['HTTP_HOST']  . '/auth/newpassword/' . $user->u_id . '/' . $user->u_hash . '">link</a>'));

        return true;

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

    public function checkIfUserExists($email, $uId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('u_email = "' . $email . '"');
        $select->where('u_active = 1');

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
        $data['u_password'] = sha1($password);
        $data['u_sent_password'] = 1;
        $this->tableGateway->update($data, array('u_id' => $uid));

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

        $this->getServiceLocator()->get('Client\Model\CompanyTable')->unsetTrainingManager($id);

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
}