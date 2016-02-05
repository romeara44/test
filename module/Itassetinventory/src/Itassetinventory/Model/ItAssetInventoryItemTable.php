<?php
namespace Itassetinventory\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ItAssetInventoryItemTable implements ServiceLocatorAwareInterface
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

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getItem($id)
    {
        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('iaii_id = ' . $id);
        $select->where('iaii_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function getItemsByInventory($iaId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('iaii_iai_id = ' . (int) $iaId);
        $select->where('iaii_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[$rs->iaii_iaiit_id][] = $rs;
        }

        return $ret;
    }

    public function getItemsIdsByInventory($aId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('iaii_iai_id = ' . (int) $aId);
        $select->where('iaii_active = 1');

        $resultSet = $this->tableGateway->selectWith($select);

        $ret = array();
        foreach ($resultSet as $rs) {
            $ret[] = $rs->iaii_id;
        }

        return $ret;
    }

    public function saveIaii(ItassetInventoryItem $iaii)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array();

        if ($iaii->iaii_iai_id) {
            $data['iaii_iai_id'] = $iaii->iaii_iai_id;
        }
        if ($iaii->iaii_iaiit_id) {
            $data['iaii_iaiit_id'] = $iaii->iaii_iaiit_id;
        }
        $data['iaii_name'] = $iaii->iaii_name;
        $data['iaii_model'] = $iaii->iaii_model;
        $data['iaii_description'] = $iaii->iaii_description;

        $id = (int) $iaii->iaii_id;

        if (!$id) {
            $data['iaii_create_u_id'] = $identity['u_id'];
        } else {
            $data['iaii_update_u_id'] = $identity['u_id'];
        }

        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getItem($id)) {
                $data['iaii_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

                $this->tableGateway->update($data, array('iaii_id' => $id));
            } else { echo $id;exit;
                throw new \Exception('Form id does not exist');
            }
        }

        return $id;
    }

    public function deleteIaii($id)
    {
        $data['iaii_active'] = 0;
        $this->tableGateway->update($data, array('iaii_id' => $id));

        return true;
    }

    public function deleteIaiiByInventory($id)
    {
        $data['iaii_active'] = 0;
        $this->tableGateway->update($data, array('iaii_iai_id' => $id));

        return true;
    }

    public function unarchiveIaiiByInventory($id)
    {
        $data['iaii_active'] = 1;
        $this->tableGateway->update($data, array('iaii_iai_id' => $id));

        return true;
    }
}