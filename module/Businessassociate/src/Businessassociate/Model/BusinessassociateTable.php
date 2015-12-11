<?php
namespace Businessassociate\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class BusinessassociateTable implements ServiceLocatorAwareInterface
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

    public function getBusinessassociates($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('business_associates');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Businessassociate());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($identity['u_role_id'] == User::ROLE_ADMIN) {
            } else {
                $select->where('ba_active = 1');
                if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
                    $whereStr = '(ba_consultant_u_id = ' . $identity['u_id'];
                    $companies_ids = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getCompaniesIdsForConsultant($identity['u_id']);
                    if ($companies_ids) {
                        $whereStr .= ' OR ba_c_id IN(' . implode(',', $companies_ids) . ')';
                    }
                    
                    $select->where($whereStr . ')');
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $select->where('ba_consultant_u_id IN (' . implode(',', $ids) . ')');
                } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
                    $select->where('ba_c_id = ' . $identity['u_company_id']);
                }
            }

            $select->join(array('u' => 'users'), 'ba_contact_u_id = u_id', array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')), 'left');
            $select->join(array('c' => 'companies'), 'ba_c_id = c_id', array('_client_name' => 'c_name'), 'left');
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

    public function getBusinessassociatesPairs($baCId = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $select = $this->tableGateway->getSql()->select();
        $select->where('ba_active = 1');

        if ($identity['u_role_id'] == User::ROLE_CONSULTANT) {
            $select->where('ba_consultant_u_id = ' . $identity['u_id']);
        } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
            $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
            $ids[] = $identity['u_id'];
            $select->where('ba_consultant_u_id IN (' . implode(',', $ids) . ')');
        } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
            $select->where('ba_c_id = ' . $identity['u_company_id']);
        }

        if ((int) $baCId) {
            $select->where('ba_c_id = ' . $baCId);
        }

        $select->join(array('u' => 'users'), 'ba_contact_u_id = u_id', array('_contact_name' => new \Zend\Db\Sql\Expression('CONCAT(u_firstname, " ", u_lastname)')), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $bas = array();
        foreach ($resultSet as $rs) {
            $bas[$rs->ba_id] = $rs->ba_name;
        }

        return $bas;
    }

    public function getBusinessassociate($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('ba_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getBusinessassociateByContactId($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('ba_contact_u_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveBusinessassociate(Businessassociate $ba)
    {
        $data = array(
            'ba_name' => $ba->ba_name,
            'ba_office_phone' => $ba->ba_office_phone,
            'ba_office_phone_inner' => $ba->ba_office_phone_inner,
            'ba_direct_phone' => $ba->ba_direct_phone,
            'ba_direct_phone_inner' => $ba->ba_direct_phone_inner,
            'ba_other_phone' => $ba->ba_other_phone,
            'ba_other_phone_inner' => $ba->ba_other_phone_inner,
            'ba_fax' => $ba->ba_fax,
            'ba_email' => $ba->ba_email,
            'ba_website' => $ba->ba_website,
            'ba_address1' => $ba->ba_address1,
            'ba_address2' => $ba->ba_address2,
            'ba_state_id' => $ba->ba_state_id,
            'ba_city' => $ba->ba_city,
            'ba_zip' => $ba->ba_zip,
            'ba_phone_call_status' => $ba->ba_phone_call_status,
            'ba_phone_call_invited_status' => $ba->ba_phone_call_invited_status,
            'ba_assessment_invited_status' => $ba->ba_assessment_invited_status,
            'ba_assessment_completed_status' => $ba->ba_assessment_completed_status,
            'ba_agreement_status' => $ba->ba_agreement_status,
            'ba_hipaa_compliant_status' => $ba->ba_hipaa_compliant_status,
            'ba_status' => $ba->ba_status,
        );

        $id = (int) $ba->ba_id;

        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if ($ba->ba_c_id) {
            $data['ba_c_id'] = $ba->ba_c_id;
        }

        if (!$id) {
            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
                $data['ba_consultant_u_id'] = $ba->ba_consultant_u_id;
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $data['ba_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
                $data['ba_c_id'] = $identity['u_company_id'];
            }

            $data['ba_create_u_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_BA, $id);
        } else {
            if ($this->getBusinessassociate($id)) {
                $data['ba_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                $data['ba_update_u_id'] = $identity['u_id'];

                $this->tableGateway->update($data, array('ba_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_BA, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function setContactId($baId, $uId)
    {
        $data['ba_contact_u_id'] = $uId;
        $this->tableGateway->update($data, array('ba_id' => $baId));
    }

    public function setInviteDate($baId)
    {
        $data['ba_assessment_invited_status'] = 1;
        $data['ba_assessment_invited_date'] = new \Zend\Db\Sql\Expression('NOW()');
        $this->tableGateway->update($data, array('ba_id' => $baId));
    }

    public function setCallDate($baId)
    {
        $data['ba_phone_call_invited_status'] = 1;
        $data['ba_phone_call_date'] = new \Zend\Db\Sql\Expression('NOW()');;
        $this->tableGateway->update($data, array('ba_id' => $baId));
    }

    public function signoffBusinessassociate($baId)
    {
        $data['ba_status'] = 1;
        $data['ba_sign_off_date'] = new \Zend\Db\Sql\Expression('NOW()');
        $data['ba_assessment_completed_status'] = 1;

        $this->tableGateway->update($data, array('ba_id' => $baId));
    }

    public function deleteBusinessassociate($id)
    {
        $data['ba_id'] = $id;
        $data['ba_active'] = 0;
        $this->tableGateway->update($data, array('ba_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_BA, $id);
        return true;
    }


    public function unarchiveBusinessassociate($id)
    {
        $data['ba_id'] = $id;
        $data['ba_active'] = 1;
        $this->tableGateway->update($data, array('ba_id' => $id));
    }

    public function deleteBaByCompanyId($cId = 0, $value = 0)
    {
        $data['ba_active'] = $value;
        $this->tableGateway->update($data, array('ba_c_id' => $cId));
        return true;
    }

}