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

class DisclosureRequestTable implements ServiceLocatorAwareInterface
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

    public function getDisclosureRequests($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        $identity = $this->_getIdentity();

        if ($paginated) {
            $select = $this->tableGateway->getSql()->select();

            $select->columns(array('dr_id'                   => 'dr_id',
                                   'dr_reference_number'     => $this->_decryptField('dr_reference_number'),
                                   'dr_requested_by'         => $this->_decryptField('dr_requested_by'),
                                   'dr_date_requested'       => $this->_decryptField('dr_date_requested'),
                                   'dr_date_range_requested' => $this->_decryptField('dr_date_range_requested'),
                                   'dr_staff_member'         => $this->_decryptField('dr_staff_member'),
                                   'dr_completing_request'   => $this->_decryptField('dr_completing_request'),
                                   'dr_date_provided'        => $this->_decryptField('dr_date_provided'),
                                   'dr_create_u_id'          => 'dr_create_u_id',
                                   'dr_active'               => 'dr_active'
                                  )
                                );

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

            return $paginator;
        }

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('dr_id'                   => 'dr_id',
                               'dr_reference_number'     => $this->_decryptField('dr_reference_number'),
                               'dr_requested_by'         => $this->_decryptField('dr_requested_by'),
                               'dr_date_requested'       => $this->_decryptField('dr_date_requested'),
                               'dr_date_range_requested' => $this->_decryptField('dr_date_range_requested'),
                               'dr_staff_member'         => $this->_decryptField('dr_staff_member'),
                               'dr_completing_request'   => $this->_decryptField('dr_completing_request'),
                               'dr_date_provided'        => $this->_decryptField('dr_date_provided'),
                               'dr_create_u_id'          => 'dr_create_u_id',
                               'dr_active'               => 'dr_active'
                              )
                            );

        if ($identity['u_role_id'] != User::ROLE_ADMIN) {
            $select->where('dr_active = 1');
        }

        return $this->tableGateway->selectWith($select);
    }

    public function getDisclosureRequest($id)
    {
        $identity = $this->_getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('dr_id'                   => 'dr_id',
                               'dr_reference_number'     => $this->_decryptField('dr_reference_number'),
                               'dr_requested_by'         => $this->_decryptField('dr_requested_by'),
                               'dr_date_requested'       => $this->_decryptField('dr_date_requested'),
                               'dr_date_range_requested' => $this->_decryptField('dr_date_range_requested'),
                               'dr_staff_member'         => $this->_decryptField('dr_staff_member'),
                               'dr_completing_request'   => $this->_decryptField('dr_completing_request'),
                               'dr_date_provided'        => $this->_decryptField('dr_date_provided'),
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

    public function saveDisclosureRequest(DisclosureRequest $disclosurerequest)
    {
        $identity = $this->_getIdentity();

        $data = array(
            'dr_reference_number'     => $this->_encryptValue($disclosurerequest->dr_reference_number),
            'dr_requested_by'         => $this->_encryptValue($disclosurerequest->dr_requested_by),
            'dr_date_requested'       => $this->_encryptValue($disclosurerequest->dr_date_requested),
            'dr_date_range_requested' => $this->_encryptValue($disclosurerequest->dr_date_range_requested),
            'dr_staff_member'         => $this->_encryptValue($disclosurerequest->dr_staff_member),
            'dr_completing_request'   => $this->_encryptValue($disclosurerequest->dr_completing_request),
            'dr_date_provided'        => $this->_encryptValue($disclosurerequest->dr_date_provided)
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

    public function unarchiveDisclosureRequest($id)
    {
        $data['dr_id']     = $id;
        $data['dr_active'] = 1;

        $this->tableGateway->update($data, array('dr_id' => $id));

        return true;
    }
}