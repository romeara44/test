<?php
namespace Disclosure\Model;

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

class DisclosureRequestTable implements ServiceLocatorAwareInterface
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

    public function getDisclosureRequests($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();
            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('dr_active = 1');
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new DisclosureRequest());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('dr_active = ' . $roleFilter);
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

    public function getDisclosureRequest($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('dr_id' => $id));
        $row = $rowset->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveDisclosureRequest(DisclosureRequest $disclosurerequest)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'dr_reference_number'         => $disclosurerequest->dr_reference_number,
            'dr_requested_by' => $disclosurerequest->dr_requested_by,
            'dr_date_requested'      => $disclosurerequest->dr_date_requested,
            'dr_date_range_requested'      => $disclosurerequest->dr_date_range_requested,
            'dr_staff_member'      => $disclosurerequest->dr_staff_member,
            'dr_completing_request'      => $disclosurerequest->dr_completing_request,
            'dr_date_provided'      => $disclosurerequest->dr_date_provided
        );

        $id = (int) $disclosurerequest->dr_id;

        if ($id == 0) {
            $data['dr_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
        } else {
            if ($this->getDisclosureRequest($id)) {
                $this->tableGateway->update($data, array('dr_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteDisclosureRequest($id)
    {
        $data['dr_id'] = $id;
        $data['dr_active'] = 0;
        $this->tableGateway->update($data, array('dr_id' => $id));
        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
        return true;
    }

    public function deleteDisclosureRequestsByCompanyId($cId, $value = 0)
    {
        $users = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($cId);
        
        if($users) {
            foreach ($users as $user) {
                $data['dr_active'] = $value;
                $this->tableGateway->update($data, array('dr_create_u_id' => $user->u_id));
            }
        }

        return true;
    }

    public function unarchiveDisclosureRequest($id)
    {
        $data['dr_id'] = $id;
        $data['dr_active'] = 1;
        $this->tableGateway->update($data, array('dr_id' => $id));

        return true;
    }


}