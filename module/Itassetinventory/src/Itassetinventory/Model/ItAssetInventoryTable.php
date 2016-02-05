<?php
namespace Itassetinventory\Model;

use Admin\Model\User;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Note\Model\Note;
use Itassetinventory\Model\ItAssetInventoryItem;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\PredicateSet;

class ItAssetInventoryTable implements ServiceLocatorAwareInterface
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

    public function getItAssetInventories($paginated = false, $orderBy = null, $order = null, $identity = null, $searchValue = null, $roleFilter = null)
    {
        if ($paginated) {
            $select = new Select('it_asset_inventories');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Itassetinventory());
            $paginatorAdapter = new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $resultSetPrototype
            );

            if ($identity['u_role_id'] == \Admin\Model\User::ROLE_ADMIN) {
            } else {
                $where_str = '';
                if ($identity['u_role_id'] == \Admin\Model\User::ROLE_CONSULTANT) {
                    $where_str .= 'iai_owner_u_id = ' . $identity['u_id'];
                } elseif ($identity['u_role_id'] == User::ROLE_SENIOR_CONSULTANT) {
                    $ids = $this->getServiceLocator()->get('Admin\Model\UserTable')->getConsultantIdsForSenior($identity['u_id']);
                    $ids[] = $identity['u_id'];
                    $where_str .= 'iai_consultant_u_id IN (' . implode(',', $ids) . ')';
                } elseif ($identity['u_role_id'] == User::ROLE_CLIENT && $identity['u_company_id']) {
                    $where_str .= 'iai_c_id = ' . $identity['u_company_id'];
                }

                $companies_ids = $this->getServiceLocator()->get('Client\Model\CompanyConsultantsTable')->getCompaniesIdsForConsultant($identity['u_id']);
                if ($companies_ids) {
                    if ($where_str) {
                        $where_str = '(' . $where_str . ' OR iai_c_id IN (' . implode(',', $companies_ids) . '))';
                      } else {
                        $where_str = 'iai_c_id IN (' . implode(',', $companies_ids) . ')';
                      }
                }
                if ($where_str) {
                    $where_str .= ' AND iai_active = 1';
                } else {
                    $where_str = 'iai_active = 1';
                }
                $select->where($where_str);
            }

            $select->join(array('c' => 'companies'), 'iai_c_id = c_id', array('_c_name' => 'c_name'), 'left');
            $select->join(array('ai' => 'addresses'), 'iai_location_id = adr_id', array('_location' => 'adr_name'), 'left');
            $select->join(array('iait' => 'it_asset_inventory_types'), 'iai_type_id = iait_id', array('_type' => 'iait_name'), 'left');

            if($searchValue) {
                $select->where('(c_name LIKE "%' . $searchValue . '%" OR adr_name LIKE "%' . $searchValue . '%" OR iait_name LIKE "%' . $searchValue . '%")');
            }

            if ($roleFilter !== null) {
                $select->where('iai_active = ' . $roleFilter);
            }

            $order = $order ? $order : 'ASC';

            if ($orderBy) {
                $orders[] = $orderBy . ' ' . $order;
            }

            $orders[] = 'iai_id ASC';

            $select->order($orders);

            $paginator = new Paginator($paginatorAdapter);


            return $paginator;
        }
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }


    public function getItassetinventory($id)
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $id  = (int) $id;

        $select = $this->tableGateway->getSql()->select();
        $select->where('iai_id = ' . $id);

        if ($identity['u_role_id'] != \Admin\Model\User::ROLE_ADMIN) {
            $select->where('iai_active = 1');
        }

        $select->join(array('c' => 'companies'), 'iai_c_id = c_id', array('_client_name' => 'c_name'), 'left');

        $resultSet = $this->tableGateway->selectWith($select);

        $row = $resultSet->current();
        if (!$row) {
            return false;
        }

        return $row;
    }

    public function saveItassetinventory(Itassetinventory $iai, $files = [])
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        $data = array(
            'iai_c_id'        => $iai->iai_c_id,
            'iai_location_id' => $iai->iai_location_id,
            'iai_type_id'     => $iai->iai_type_id,
        );

        $id = (int) $iai->iai_id;

        if (!$id) {
            if (in_array($identity['u_role_id'], array(User::ROLE_CONSULTANT, User::ROLE_SENIOR_CONSULTANT))) {
                $data['iai_consultant_u_id'] = $identity['u_id'];
            } elseif ($identity['u_role_id'] == User::ROLE_CLIENT) {
                $data['iai_consultant_u_id'] = $identity['u_senior_consultant_u_id'];
                $data['iai_c_id'] = $identity['u_company_id'];
            }

            $data['iai_owner_u_id'] = $identity['u_id'];

            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {

            $data['iai_update_u_id'] = $identity['u_id'];
            $data['iai_update_date'] = new \Zend\Db\Sql\Expression('NOW()');

            if ($this->getItassetinventory($id)) {
                $this->tableGateway->update($data, array('iai_id' => $id));

                $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_EDIT, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);
            } else {
                throw new \Exception('Form id does not exist');
            }
        }

        if($id) {
            if($iai->iai_type_id != \Itassetinventory\Model\ItAssetInventoryItemTypeTable::TYPE_MANUAL_ENTRY) {
                $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->deleteIaiiByInventory($id);
                $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryReportTable')->deleteIairByInventory($id);
            } else {
                if($iai->_items) {
                    $existsItems = [];
                    $existsItemIds = [];
                    foreach ($iai->_items as $itemTypeId => $itemsType) {
                        $newItems[$itemTypeId] = [];
                        if(isset($itemsType['new']) && $itemsType['new']) {
                            foreach ($itemsType['new'] as $attrName => $itemsAttrs) {
                                foreach ($itemsAttrs as $i => $itemsAttr) {
                                    $newItems[$itemTypeId][$i]['iaii_iai_id'] = $id;
                                    $newItems[$itemTypeId][$i][$attrName] = $itemsAttr;
                                }
                            }
                        }
                        if(isset($itemsType['exists'])) {
                            $existsItemIds = array_merge($existsItemIds, array_keys($itemsType['exists']));

                            foreach ($itemsType['exists'] as $existsItem) {
                                $iaii = new ItAssetInventoryItem();
                                $iaii->exchangeArray($existsItem);
                                $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->saveIaii($iaii);
                            }
                        }
                        
                        if(isset($files['items' . $itemTypeId])) {
                            $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryReportTable')->saveIair($id, $itemTypeId, $files['items' . $itemTypeId]);
                        }
                    }

                    $curItemIds = $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->getItemsIdsByInventory($id);
                    $delItemIds = array_diff($curItemIds, $existsItemIds);

                    foreach ($delItemIds as $delItemId) {
                        $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->deleteIaii($delItemId);
                    }
                    foreach ($newItems as $newItemsType) {
                        foreach ($newItemsType as $item) {
                            $iaii = new ItAssetInventoryItem();
                            $iaii->exchangeArray($item);
                            $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->saveIaii($iaii);
                        }
                    }
                }
            }
        }

        return $id;
    }

    public function deleteItassetinventory($id)
    {
        $data['iai_active'] = 0;
        $this->tableGateway->update($data, array('iai_id' => $id));
        $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->deleteIaiiByInventory($id);
        $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryReportTable')->deleteIairByInventory($id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_DELETE, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);

        return true;
    }

    public function unarchiveItassetinventory($id)
    {
        $data['iai_active'] = 1;
        $this->tableGateway->update($data, array('iai_id' => $id));
        $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryItemTable')->unarchiveIaiiByInventory($id);
        $this->getServiceLocator()->get('Itassetinventory\Model\ItAssetInventoryReportTable')->unarchiveIairByInventory($id);

        $this->getServiceLocator()->get('Application\Model\LogsTable')->saveLog(\Application\Model\LogsTable::TYPE_UNARCHIVE, \Application\Model\LogsTable::ITEM_TYPE_IAI, $id);

        return true;
    }

}