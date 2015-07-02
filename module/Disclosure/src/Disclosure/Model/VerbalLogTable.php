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

class VerbalLogTable implements ServiceLocatorAwareInterface
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

    public function getVerbalLogs($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $identity = $this->_getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();

            $select->columns(array('vl_id'                    => 'vl_id',
                                   'vl_date_of_request'       => $this->_decryptField('vl_date_of_request'),
                                   'vl_medical_record_number' => $this->_decryptField('vl_medical_record_number'),
                                   'vl_name'                  => $this->_decryptField('vl_name'),
                                   'vl_date_of_birth'         => $this->_decryptField('vl_date_of_birth'),
                                   'vl_address'               => $this->_decryptField('vl_address'),
                                   'vl_disclosure_address'    => $this->_decryptField('vl_disclosure_address'),
                                   'vl_date_requested_from'   => $this->_decryptField('vl_date_requested_from'),
                                   'vl_date_requested_to'     => $this->_decryptField('vl_date_requested_to'),
                                   'vl_is_fees'               => $this->_decryptField('vl_is_fees'),
                                   'vl_fees_charge'           => $this->_decryptField('vl_fees_charge'),
                                   'vl_date_request_received' => $this->_decryptField('vl_date_request_received'),
                                   'vl_date_account_sent'     => $this->_decryptField('vl_date_account_sent'),
                                   'vl_is_extensions'         => $this->_decryptField('vl_is_extensions'),
                                   'vl_extension_reason'      => $this->_decryptField('vl_extension_reason'),
                                   'vl_date_patient_notified' => $this->_decryptField('vl_date_patient_notified'),
                                   'vl_staff_member'          => $this->_decryptField('vl_staff_member'),
                                   'vl_create_u_id'           => 'vl_create_u_id',
                                   'vl_active'                => 'vl_active'
                                  )
                                );

            if ($identity['u_role_id'] != User::ROLE_ADMIN) {
                $select->where('vl_active = 1');
            }
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new VerbalLog());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($roleFilter !== null) {
                $select->where('vl_active = ' . $roleFilter);
            }

            if ($orderBy) {
                $order = $order ? $order : 'ASC';
                $select->order($orderBy . ' ' . $order);
            }

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('vl_id'                    => 'vl_id',
                               'vl_date_of_request'       => $this->_decryptField('vl_date_of_request'),
                               'vl_medical_record_number' => $this->_decryptField('vl_medical_record_number'),
                               'vl_name'                  => $this->_decryptField('vl_name'),
                               'vl_date_of_birth'         => $this->_decryptField('vl_date_of_birth'),
                               'vl_address'               => $this->_decryptField('vl_address'),
                               'vl_disclosure_address'    => $this->_decryptField('vl_disclosure_address'),
                               'vl_date_requested_from'   => $this->_decryptField('vl_date_requested_from'),
                               'vl_date_requested_to'     => $this->_decryptField('vl_date_requested_to'),
                               'vl_is_fees'               => $this->_decryptField('vl_is_fees'),
                               'vl_fees_charge'           => $this->_decryptField('vl_fees_charge'),
                               'vl_date_request_received' => $this->_decryptField('vl_date_request_received'),
                               'vl_date_account_sent'     => $this->_decryptField('vl_date_account_sent'),
                               'vl_is_extensions'         => $this->_decryptField('vl_is_extensions'),
                               'vl_extension_reason'      => $this->_decryptField('vl_extension_reason'),
                               'vl_date_patient_notified' => $this->_decryptField('vl_date_patient_notified'),
                               'vl_staff_member'          => $this->_decryptField('vl_staff_member'),
                               'vl_create_u_id'           => 'vl_create_u_id',
                               'vl_active'                => 'vl_active'
                              )
                            );

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('vl_active = 1');
        }

        return $this->tableGateway->selectWith($select);
    }

    public function getVerbalLog($id)
    {
        $identity = $this->_getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('vl_id'                    => 'vl_id',
                               'vl_date_of_request'       => $this->_decryptField('vl_date_of_request'),
                               'vl_medical_record_number' => $this->_decryptField('vl_medical_record_number'),
                               'vl_name'                  => $this->_decryptField('vl_name'),
                               'vl_date_of_birth'         => $this->_decryptField('vl_date_of_birth'),
                               'vl_address'               => $this->_decryptField('vl_address'),
                               'vl_disclosure_address'    => $this->_decryptField('vl_disclosure_address'),
                               'vl_date_requested_from'   => $this->_decryptField('vl_date_requested_from'),
                               'vl_date_requested_to'     => $this->_decryptField('vl_date_requested_to'),
                               'vl_is_fees'               => $this->_decryptField('vl_is_fees'),
                               'vl_fees_charge'           => $this->_decryptField('vl_fees_charge'),
                               'vl_date_request_received' => $this->_decryptField('vl_date_request_received'),
                               'vl_date_account_sent'     => $this->_decryptField('vl_date_account_sent'),
                               'vl_is_extensions'         => $this->_decryptField('vl_is_extensions'),
                               'vl_extension_reason'      => $this->_decryptField('vl_extension_reason'),
                               'vl_date_patient_notified' => $this->_decryptField('vl_date_patient_notified'),
                               'vl_staff_member'          => $this->_decryptField('vl_staff_member'),
                               'vl_create_u_id'           => 'vl_create_u_id',
                               'vl_active'                => 'vl_active'
                              )
                            );

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('vl_active = 1');
        }

        $row = $this->tableGateway->selectWith($select)->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveVerbalLog(VerbalLog $verballog)
    {
        $identity = $this->_getIdentity();

        $data = array(
            'vl_date_of_request'       => $this->_encryptValue($verballog->vl_date_of_request),
            'vl_medical_record_number' => $this->_encryptValue($verballog->vl_medical_record_number),
            'vl_name'                  => $this->_encryptValue($verballog->vl_name),
            'vl_date_of_birth'         => $this->_encryptValue($verballog->vl_date_of_birth),
            'vl_address'               => $this->_encryptValue($verballog->vl_address),
            'vl_disclosure_address'    => $this->_encryptValue($verballog->vl_disclosure_address),
            'vl_date_requested_from'   => $this->_encryptValue($verballog->vl_date_requested_from),
            'vl_date_requested_to'     => $this->_encryptValue($verballog->vl_date_requested_to),
            'vl_is_fees'               => $this->_encryptValue($verballog->vl_is_fees),
            'vl_fees_charge'           => $this->_encryptValue($verballog->vl_fees_charge),
            'vl_date_request_received' => $this->_encryptValue($verballog->vl_date_request_received),
            'vl_date_account_sent'     => $this->_encryptValue($verballog->vl_date_account_sent),
            'vl_is_extensions'         => $this->_encryptValue($verballog->vl_is_extensions),
            'vl_extension_reason'      => $this->_encryptValue($verballog->vl_extension_reason),
            'vl_date_patient_notified' => $this->_encryptValue($verballog->vl_date_patient_notified),
            'vl_staff_member'          => $this->_encryptValue($verballog->vl_staff_member)
        );

        $id = (int) $verballog->vl_id;

        if ($id == 0) {
            $data['vl_create_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;

            $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_ADD, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);
        } else {
            if ($this->getVerbalLog($id)) {
                $this->tableGateway->update($data, array('vl_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteVerbalLog($id)
    {
        $data['vl_id']     = $id;
        $data['vl_active'] = 0;

        $this->tableGateway->update($data, array('vl_id' => $id));

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_VL, $id);

        return true;
    }

    public function deleteVerbalLogsByCompanyId($cId, $value = 0)
    {
        $users = $this->getServiceLocator()->get('Admin\Model\UserTable')->getUsersByCompany($cId);
        
        if($users) {
            foreach ($users as $user) {
                $data['vl_active'] = $value;
                $this->tableGateway->update($data, array('vl_create_u_id' => $user->u_id));
            }
        }

        return true;
    }

    public function unarchiveVerbalLog($id)
    {
        $data['vl_id']     = $id;
        $data['vl_active'] = 1;

        $this->tableGateway->update($data, array('vl_id' => $id));

        return true;
    }


}