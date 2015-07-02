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

    public function getDisclosureTrackingLogs($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $identity = $this->_getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();

            $select->columns(array('dtl_id'                        => 'dtl_id',
                                   'dtl_reference_number'          => $this->_decryptField('dtl_reference_number'),
                                   'dtl_patient_name'              => $this->_decryptField('dtl_patient_name'),
                                   'dtl_medical_record_number'     => $this->_decryptField('dtl_medical_record_number'),
                                   'dtl_date_received'             => $this->_decryptField('dtl_date_received'),
                                   'dtl_name_of_requestor'         => $this->_decryptField('dtl_name_of_requestor'),
                                   'dtl_address'                   => $this->_decryptField('dtl_address'),
                                   'dtl_auth_type'                 => $this->_decryptField('dtl_auth_type'),
                                   'dtl_purpose_of_disclosure'     => $this->_decryptField('dtl_purpose_of_disclosure'),
                                   'dtl_phi_information_disclosed' => $this->_decryptField('dtl_phi_information_disclosed'),
                                   'dtl_date_disclosed'            => $this->_decryptField('dtl_date_disclosed'),
                                   'dtl_disclosed_by'              => $this->_decryptField('dtl_disclosed_by'),
                                   'dtl_extension_notification'    => $this->_decryptField('dtl_extension_notification'),
                                   'dtl_copy_of_request'           => $this->_decryptField('dtl_copy_of_request'),
                                   'dtl_create_u_id'               => 'dtl_create_u_id',
                                   'dtl_active'                    => 'dtl_active'
                                  )
                                );

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

            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('dtl_id'                        => 'dtl_id',
                               'dtl_reference_number'          => $this->_decryptField('dtl_reference_number'),
                               'dtl_patient_name'              => $this->_decryptField('dtl_patient_name'),
                               'dtl_medical_record_number'     => $this->_decryptField('dtl_medical_record_number'),
                               'dtl_date_received'             => $this->_decryptField('dtl_date_received'),
                               'dtl_name_of_requestor'         => $this->_decryptField('dtl_name_of_requestor'),
                               'dtl_address'                   => $this->_decryptField('dtl_address'),
                               'dtl_auth_type'                 => $this->_decryptField('dtl_auth_type'),
                               'dtl_purpose_of_disclosure'     => $this->_decryptField('dtl_purpose_of_disclosure'),
                               'dtl_phi_information_disclosed' => $this->_decryptField('dtl_phi_information_disclosed'),
                               'dtl_date_disclosed'            => $this->_decryptField('dtl_date_disclosed'),
                               'dtl_disclosed_by'              => $this->_decryptField('dtl_disclosed_by'),
                               'dtl_extension_notification'    => $this->_decryptField('dtl_extension_notification'),
                               'dtl_copy_of_request'           => $this->_decryptField('dtl_copy_of_request'),
                               'dtl_create_u_id'               => 'dtl_create_u_id',
                               'dtl_active'                    => 'dtl_active'
                              )
                            );

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('dtl_active = 1');
        }

        return $this->tableGateway->selectWith($select);
    }

    public function getDisclosureTrackingLog($id)
    {
        $identity = $this->_getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('dtl_id'                        => 'dtl_id',
                               'dtl_reference_number'          => $this->_decryptField('dtl_reference_number'),
                               'dtl_patient_name'              => $this->_decryptField('dtl_patient_name'),
                               'dtl_medical_record_number'     => $this->_decryptField('dtl_medical_record_number'),
                               'dtl_date_received'             => $this->_decryptField('dtl_date_received'),
                               'dtl_name_of_requestor'         => $this->_decryptField('dtl_name_of_requestor'),
                               'dtl_address'                   => $this->_decryptField('dtl_address'),
                               'dtl_auth_type'                 => $this->_decryptField('dtl_auth_type'),
                               'dtl_purpose_of_disclosure'     => $this->_decryptField('dtl_purpose_of_disclosure'),
                               'dtl_phi_information_disclosed' => $this->_decryptField('dtl_phi_information_disclosed'),
                               'dtl_date_disclosed'            => $this->_decryptField('dtl_date_disclosed'),
                               'dtl_disclosed_by'              => $this->_decryptField('dtl_disclosed_by'),
                               'dtl_extension_notification'    => $this->_decryptField('dtl_extension_notification'),
                               'dtl_copy_of_request'           => $this->_decryptField('dtl_copy_of_request'),
                               'dtl_create_u_id'               => 'dtl_create_u_id',
                               'dtl_active'                    => 'dtl_active'
                              )
                            );

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('dtl_active = 1');
        }

        $select->where('dtl_id = ' . $id);

        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveDisclosureTrackingLog(DisclosureTrackingLog $disclosureTrackingLog)
    {
        $identity = $this->_getIdentity();

        $data = array(
            'dtl_reference_number'          => $this->_encryptValue($disclosureTrackingLog->dtl_reference_number),
            'dtl_patient_name'              => $this->_encryptValue($disclosureTrackingLog->dtl_patient_name),
            'dtl_medical_record_number'     => $this->_encryptValue($disclosureTrackingLog->dtl_medical_record_number),
            'dtl_date_received'             => $this->_encryptValue($disclosureTrackingLog->dtl_date_received),
            'dtl_name_of_requestor'         => $this->_encryptValue($disclosureTrackingLog->dtl_name_of_requestor),
            'dtl_address'                   => $this->_encryptValue($disclosureTrackingLog->dtl_address),
            'dtl_auth_type'                 => $this->_encryptValue($disclosureTrackingLog->dtl_auth_type),
            'dtl_purpose_of_disclosure'     => $this->_encryptValue($disclosureTrackingLog->dtl_purpose_of_disclosure),
            'dtl_phi_information_disclosed' => $this->_encryptValue($disclosureTrackingLog->dtl_phi_information_disclosed),
            'dtl_date_disclosed'            => $this->_encryptValue($disclosureTrackingLog->dtl_date_disclosed),
            'dtl_disclosed_by'              => $this->_encryptValue($disclosureTrackingLog->dtl_disclosed_by),
            'dtl_extension_notification'    => $this->_encryptValue($disclosureTrackingLog->dtl_extension_notification),
            'dtl_copy_of_request'           => $this->_encryptValue($disclosureTrackingLog->dtl_copy_of_request)
        );

        $id = (int) $disclosureTrackingLog->dtl_id;

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
        $data['dtl_id']     = $id;
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
        $data['dtl_id']     = $id;
        $data['dtl_active'] = 1;

        $this->tableGateway->update($data, array('dtl_id' => $id));

        return true;
    }


}