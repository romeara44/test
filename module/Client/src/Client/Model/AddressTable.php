<?php
namespace Client\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;

class AddressTable implements ServiceLocatorAwareInterface
{
    protected $tableGateway;
    protected $serviceLocator;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getAddresses($itemId = 0, $itemType = 1)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->join(array('ai' => 'addresses_items'), 'cadr_adr_id = adr_id', array('*'));

        $select->where('cadr_type = ' . $itemType);
        $select->where('cadr_c_id = ' . $itemId);
        $select->where('adr_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAddressesWithoutType($companyId = 0)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->join(array('ai' => 'addresses_items'), 'cadr_adr_id = adr_id', array('*'));

        $select->where('cadr_c_id = ' . $companyId);
        $select->where('adr_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getAddress($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->join(array('st' => 'states'), 'adr_state_id = state_id', array('_state_name' => 'state_name', '_state_code' => 'state_code'), 'left');
        $select->where('adr_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getLocationNameById($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('adr_id = ' . $id);

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();

        if (!$row) {
            return '';
        }

        return $row->adr_name;
    }



    public function saveAddress(Address $address)
    {
        $data = array(
            'adr_name' => $address->adr_name,
            'adr_address1' => $address->adr_address1,
            'adr_address2' => $address->adr_address2,
            'adr_city' => $address->adr_city,
            'adr_state_id' => $address->adr_state_id,
            'adr_zip' => $address->adr_zip,

            'department' => $address->department,
            'adr_phone' => $address->adr_phone,
            'adr_phone_inner' => $address->adr_phone_inner,
            'adr_other_phone' => $address->adr_other_phone,
            'adr_other_phone_inner' => $address->adr_other_phone_inner,
            'adr_fax' => $address->adr_fax,
            'adr_email' => $address->adr_email
        );

        $id = (int) $address->adr_id;
        
        if ($id == 0) {
            $data['adr_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {

            if ($this->getAddress($id)) {
                $data['adr_update_date'] = new \Zend\Db\Sql\Expression('NOW()');
                $this->tableGateway->update($data, array('adr_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteAddress($id)
    {
        $data['adr_id'] = $id;
        $data['adr_active'] = 0;
        $this->tableGateway->update($data, array('adr_id' => $id));

        return true;
    }
}