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

class DisclosureRecordTable implements ServiceLocatorAwareInterface
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

    public function getDisclosureRecords($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $identity = $this->_getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();

            $select->join(array('c' => 'companies'), 'dr_c_id = c_id', array('c_name'), 'left');
            $select->join(array('a' => 'addresses'), 'dr_adr_id = adr_id', array('adr_name'), 'left');
            $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = dr_c_id', array(), 'left');

            $select->columns(array('dr_id'                   => 'dr_id',
                                   '_dr_location'     => new \Zend\Db\Sql\Expression('IF(adr_name IS NOT NULL, CONCAT(c_name, " / ", adr_name), c_name)'),
                                   'dr_patient_name'         => 'dr_patient_name',
                                   'dr_date_received'       => 'dr_date_received',
                                   'dr_disclosed_by' => 'dr_disclosed_by',
                                   'dr_date_disclosed'         => 'dr_date_disclosed',
                                   'dr_create_u_id'          => 'dr_create_u_id',
                                   'dr_active'               => 'dr_active'
                                  )
                                );

            if ($searchValue !== null) {
              $select->where('dr_patient_name LIKE "%' . $searchValue . '%"');
            }

            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('dr_active = 1');
            }

            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
              $select->where('cc.cc_consultant_id = ' . $identity['u_id']);                
            } elseif (in_array($identity['u_role_id'], array(User::ROLE_CLIENT))) {
              $select->where('dr_c_id = ' . $identity['u_company_id']);
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new DisclosureRecord());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('dr_active = ' . $roleFilter);
            }

            $select->group('dr_id');

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->join(array('cc' => 'company_consultants'), 'cc.cc_company_id = dr_c_id', array(), 'left');

        $select->columns(array('dr_id'                   => 'dr_id',
                               '_dr_location'     => new \Zend\Db\Sql\Expression('CONCAT(c_name, "/", adr_name)'),
                               'dr_patient_name'         => 'dr_patient_name',
                               'dr_date_received'       => 'dr_date_received',
                               'dr_disclosed_by' => 'dr_disclosed_by',
                               'dr_date_disclosed'         => 'dr_date_disclosed',
                               'dr_create_u_id'          => 'dr_create_u_id',
                               'dr_active'               => 'dr_active'
                              )
                            );

        if ($searchValue !== null) {
          $select->where('dr_patient_name LIKE "%' . $searchValue . '%"');
        }

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('dr_active = 1');
        }

        if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
            $select->where('c_owner_u_id = ' . $identity['u_id']);
        } elseif (in_array($identity['u_role_id'], array(User::ROLE_CLIENT))) {
          $select->where('cc.cc_consultant_id = ' . $identity['u_id']);
        }

        $select->group('dr_id');

        return $this->tableGateway->selectWith($select);
    }

    public function getDisclosureRecord($id)
    {
        $identity = $this->_getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('dr_id'                   => 'dr_id',
                               'dr_c_id'     => 'dr_c_id',
                               'dr_adr_id'     => 'dr_adr_id',
                               'dr_patient_name'         => 'dr_patient_name',
                               'dr_date_received'       => 'dr_date_received',
                               'dr_disclosed_by' => 'dr_disclosed_by',
                               'dr_date_disclosed'         => 'dr_date_disclosed',
                               'dr_date_disclosure'     => 'dr_date_disclosure',
                               'dr_medical_record_number'         => 'dr_medical_record_number',
                               'dr_date_of_birth'       => 'dr_date_of_birth',
                               'dr_phi_information_disclosed' => 'dr_phi_information_disclosed',
                               'dr_purpose_of_disclosure'         => 'dr_purpose_of_disclosure',
                               'dr_name_of_requestor'     => 'dr_name_of_requestor',
                               'dr_address'         => 'dr_address',
                               'dr_is_verbal'       => 'dr_is_verbal',
                               'dr_is_authorized' => 'dr_is_authorized',
                               'dr_entered_by'         => 'dr_entered_by',
                               'dr_date_entered'       => 'dr_date_entered',
                               'dr_reviewed_by' => 'dr_reviewed_by',
                               'dr_date_reviewed'         => 'dr_date_reviewed',                               
                               'dr_create_u_id'          => 'dr_create_u_id',
                               'dr_active'               => 'dr_active'
                              )
                         );

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('dr_active = 1');
        }

        $select->where('dr_id = ' . $id);

        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveDisclosureRecord(DisclosureRecord $disclosurerecord)
    {
        $identity = $this->_getIdentity();

        $data = array(
            'dr_c_id'     => $disclosurerecord->dr_c_id,
            'dr_adr_id'     => $disclosurerecord->dr_adr_id,
            'dr_patient_name'         => $disclosurerecord->dr_patient_name,
            'dr_date_received'       => $disclosurerecord->dr_date_received,
            'dr_disclosed_by' => $disclosurerecord->dr_disclosed_by,
            'dr_date_disclosed'         => $disclosurerecord->dr_date_disclosed,
            'dr_date_disclosure'   => $disclosurerecord->dr_date_disclosure,
            'dr_medical_record_number'        => $disclosurerecord->dr_medical_record_number,
            'dr_date_of_birth'       => $disclosurerecord->dr_date_of_birth,
            'dr_phi_information_disclosed' => $disclosurerecord->dr_phi_information_disclosed,
            'dr_purpose_of_disclosure'         => $disclosurerecord->dr_purpose_of_disclosure,
            'dr_name_of_requestor'     => $disclosurerecord->dr_name_of_requestor,
            'dr_address'         => $disclosurerecord->dr_address,
            'dr_is_verbal'       => $disclosurerecord->dr_is_verbal,
            'dr_is_authorized' => $disclosurerecord->dr_is_authorized,
            'dr_entered_by'         => $disclosurerecord->dr_entered_by,
            'dr_date_entered'       => $disclosurerecord->dr_date_entered,
            'dr_reviewed_by' => $disclosurerecord->dr_reviewed_by,
            'dr_date_reviewed'         => $disclosurerecord->dr_date_reviewed,
            'dr_reviewed_by' => $disclosurerecord->dr_reviewed_by,
            'dr_date_reviewed'         => $disclosurerecord->dr_date_reviewed,
        );

        $id = (int) $disclosurerecord->dr_id;

        if ($id == 0) {
            $data['dr_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
        } else {
            if ($this->getDisclosureRecord($id)) {
                $this->tableGateway->update($data, array('dr_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_DR, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteDisclosureRecord($id)
    {
        $data['dr_id']     = $id;
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

    public function unarchiveDisclosureRecord($id)
    {
        $data['dr_id']     = $id;
        $data['dr_active'] = 1;

        $this->tableGateway->update($data, array('dr_id' => $id));

        return true;
    }
}