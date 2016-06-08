<?php
namespace Disclosure\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use Zend\Db\Sql\Expression;
use DataCrypt\DbCrypt;

class AccountingRequestTable implements ServiceLocatorAwareInterface
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

    private function _getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));

        return $authService->getIdentity();
    }

    public function getAccountingRequests($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $identity = $this->_getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();

            $select->join(array('c' => 'companies'), 'ar_c_id = c_id', array('c_name'), 'left');
            $select->join(array('a' => 'addresses'), 'ar_adr_id = adr_id', array('adr_name'), 'left');

            $select->columns(array('ar_id'                   => 'ar_id',
                                   '_ar_location'     => new \Zend\Db\Sql\Expression('CONCAT(c_name, "/", adr_name)'),
                                   'ar_patient_name'         => 'ar_patient_name',
                                   'ar_date_requested'       => 'ar_date_requested',
                                   'ar_is_finalized' => 'ar_is_finalized',
                                   'ar_date_sent'         => 'ar_date_sent',
                                   'ar_create_u_id'          => 'ar_create_u_id',
                                   'ar_active'               => 'ar_active'
                                  )
                                );

            if ($searchValue !== null) {
              $select->where('ar_patient_name LIKE "%' . $searchValue . '%"');
            }

            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('ar_active = 1');
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new AccountingRequest());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('ar_active = ' . $roleFilter);
            }

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('ar_id'                   => 'ar_id',
                               '_ar_location'     => new \Zend\Db\Sql\Expression('CONCAT(c_name, "/", adr_name)'),
                               'ar_patient_name'         => 'ar_patient_name',
                               'ar_date_requested'       => 'ar_date_requested',
                               'ar_is_finalized' => 'ar_is_finalized',
                               'ar_date_sent'         => 'ar_date_sent',
                               'ar_create_u_id'          => 'ar_create_u_id',
                               'ar_active'               => 'ar_active'
                              )
                            );

        if ($searchValue !== null) {
          $select->where('ar_patient_name LIKE "%' . $searchValue . '%"');
        }

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('ar_active = 1');
        }

        return $this->tableGateway->selectWith($select);
    }

    public function getAccountingRequest($id)
    {
        $identity = $this->_getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('ar_active = 1');
        }

        $select->where('ar_id = ' . $id);

        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveAccountingRequest(AccountingRequest $accountingrequest)
    {
        $identity = $this->_getIdentity();

        $data = array(
            'ar_c_id'     => $accountingrequest->ar_c_id,
            'ar_adr_id'     => $accountingrequest->ar_adr_id,
            'ar_requested_by'         => $accountingrequest->ar_requested_by,
            'ar_date_requested'       => $accountingrequest->ar_date_requested,
            'ar_disclosure_address' => $accountingrequest->ar_disclosure_address,
            'ar_patient_name'         => $accountingrequest->ar_patient_name,
            'ar_medical_record_number'   => $accountingrequest->ar_medical_record_number,
            'ar_date_of_birth'        => $accountingrequest->ar_date_of_birth,
            'ar_patient_address'       => $accountingrequest->ar_patient_address,
            'ar_date_requested_from' => $accountingrequest->ar_date_requested_from,
            'ar_date_requested_to'         => $accountingrequest->ar_date_requested_to,
            'ar_fees_charge'     => $accountingrequest->ar_fees_charge,
            'ar_is_fees'         => $accountingrequest->ar_is_fees,
            'ar_is_extensions'       => $accountingrequest->ar_is_extensions,
            'ar_extension_reason' => $accountingrequest->ar_extension_reason,
            'ar_is_finalized'         => $accountingrequest->ar_is_finalized,
            'ar_date_sent'       => $accountingrequest->ar_date_sent,
            'ar_date_patient_notified' => $accountingrequest->ar_date_patient_notified,
            'ar_staff_member'         => $accountingrequest->ar_staff_member,
        );

        $id = (int) $accountingrequest->ar_id;

        if ($id == 0) {
            $data['ar_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);
        } else {
            if ($this->getAccountingRequest($id)) {
                $this->tableGateway->update($data, array('ar_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteAccountingRequest($id)
    {
        $data['ar_id']     = $id;
        $data['ar_active'] = 0;

        $this->tableGateway->update($data, array('ar_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);

        return true;
    }

    public function deleteVerbalLogsByCompanyId($cId, $value = 0)
    {
        $users = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($cId);
        
        if($users) {
            foreach ($users as $user) {
                $data['ar_active'] = $value;
                $this->tableGateway->update($data, array('ar_create_u_id' => $user->u_id));
            }
        }

        return true;
    }

    public function unarchiveAccountingRequest($id)
    {
        $data['ar_id']     = $id;
        $data['ar_active'] = 1;

        $this->tableGateway->update($data, array('ar_id' => $id));

        return true;
    }
}