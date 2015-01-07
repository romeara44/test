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

class AddressItemTable implements ServiceLocatorAwareInterface
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

    public function fetchAll($paginated = false, $orderBy = null, $order = null, $searchValue = null, $params = array())
    {
        if ($paginated) {
            $select = new Select('companies');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Company());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            $select->where('c_active = 1');

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

    public function getAddressItem($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('cadr_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveAddressItem(AddressItem $addressItem)
    {
        $data = array(
            'cadr_type' => $addressItem->cadr_type,
            'cadr_c_id' => $addressItem->cadr_c_id,
            'cadr_adr_id' => $addressItem->cadr_adr_id,
        );

        $id = (int) $addressItem->cadr_id;
        
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getAddress($id)) {
                $this->tableGateway->update($data, array('cadr_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteAddressItem($id)
    {
        $data['cadr_id'] = $id;
        $data['cadr_active'] = 0;
        $this->tableGateway->update($data, array('cadr_id' => $id));

        return true;
    }
}