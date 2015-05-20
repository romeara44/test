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

class DisclosureTrackingLogTable implements ServiceLocatorAwareInterface
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

    public function getDisclosureTrackingLogs($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();
            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('dtl_active = 1');
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new DisclosureTrackingLog());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('dtl_active = ' . $roleFilter);
            }

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $paginator = new Paginator($paginatorAdapter);
// print_r($select->getSqlString());exit;
            return $paginator;
        }

        $resultSet = $this->tableGateway->select();

        return $resultSet;
    }

    public function getDisclosureTrackingLog($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('dtl_id' => $id));
        $row = $rowset->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveDisclosureTrackingLog(DisclosureTrackingLog $disclosureTrackingLog)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'dtl_reference_number'         => $disclosureTrackingLog->dtl_reference_number,
            'dtl_patient_name' => $disclosureTrackingLog->dtl_patient_name,
            'dtl_medical_record_number'      => $disclosureTrackingLog->dtl_medical_record_number,
            'dtl_date_received'      => $disclosureTrackingLog->dtl_date_received,
            'dtl_name_of_requestor'      => $disclosureTrackingLog->dtl_name_of_requestor,
            'dtl_address'      => $disclosureTrackingLog->dtl_address,
            'dtl_auth_type'      => $disclosureTrackingLog->dtl_auth_type,
            'dtl_purpose_of_disclosure'      => $disclosureTrackingLog->dtl_purpose_of_disclosure,
            'dtl_phi_information_disclosed'      => $disclosureTrackingLog->dtl_phi_information_disclosed,
            'dtl_date_disclosed'      => $disclosureTrackingLog->dtl_date_disclosed,
            'dtl_disclosed_by'      => $disclosureTrackingLog->dtl_disclosed_by,
            'dtl_extension_notification'      => $disclosureTrackingLog->dtl_extension_notification,
            'dtl_copy_of_request'      => $disclosureTrackingLog->dtl_copy_of_request
        );

        $id = (int) $disclosureTrackingLog->dtl_id;
// var_dump($data);exit;
        if ($id == 0) {
            $data['dtl_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_DTL, $id);
        } else {
            if ($this->getDisclosureTrackingLog($id)) {
                $this->tableGateway->update($data, array('dtl_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_DTL, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteDisclosureTrackingLog($id)
    {
        $data['dtl_id'] = $id;
        $data['dtl_active'] = 0;
        $this->tableGateway->update($data, array('dtl_id' => $id));
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_DTL, $id);
        return true;
    }

    public function deleteDisclosureTrackingLogsByCompanyId($cId, $value = 0)
    {
        $users = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($cId);
        
        if($users) {
            foreach ($users as $user) {
                $data['dtl_active'] = $value;
                $this->tableGateway->update($data, array('dtl_create_u_id' => $user->u_id));
            }
        }

        return true;
    }

    public function unarchiveDisclosureTrackingLog($id)
    {
        $data['dtl_id'] = $id;
        $data['dtl_active'] = 1;
        $this->tableGateway->update($data, array('dtl_id' => $id));

        return true;
    }


}