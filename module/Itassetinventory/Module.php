<?php
namespace Itassetinventory;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Itassetinventory\Model\ItAssetInventoryTable' =>  function($sm) {
                        $tableGateway = $sm->get('ItAssetInventoryTableGateway');
                        $table = new \Itassetinventory\Model\ItAssetInventoryTable($tableGateway);
                        return $table;
                },
                'ItassetinventoryTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Itassetinventory\Model\ItAssetInventory());
                        return new TableGateway('it_asset_inventories', $dbAdapter, null, $resultSetPrototype);
                },
                'Itassetinventory\Model\ItAssetInventoryTypeTable' =>  function($sm) {
                        $tableGateway = $sm->get('ItAssetInventoryTypeTableGateway');
                        $table = new \Itassetinventory\Model\ItAssetInventoryTypeTable($tableGateway);
                        return $table;
                },
                'ItAssetInventoryTypeTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Itassetinventory\Model\ItAssetInventoryType());
                        return new TableGateway('it_asset_inventory_types', $dbAdapter, null, $resultSetPrototype);
                },
                'Itassetinventory\Model\ItAssetInventoryItemTypeTable' => function($sm) {
                        $tableGateway = $sm->get('ItAssetInventoryItemTypeTableGateway');
                        $table = new \Itassetinventory\Model\ItAssetInventoryItemTypeTable($tableGateway);
                        return $table;
                },
                'ItAssetInventoryItemTypeTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('it_asset_inventory_item_types', $dbAdapter, null, $resultSetPrototype);
                },
                'Itassetinventory\Model\ItAssetInventoryItemTable' => function($sm) {
                        $tableGateway = $sm->get('ItAssetInventoryItemTableGateway');
                        $table = new \Itassetinventory\Model\ItAssetInventoryItemTable($tableGateway);
                        return $table;
                },
                'ItAssetInventoryItemTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('it_asset_inventory_items', $dbAdapter, null, $resultSetPrototype);
                },
                'Itassetinventory\Model\ItAssetInventoryReportTable' =>  function($sm) {
                        $tableGateway = $sm->get('ItAssetInventoryReportTableGateway');
                        $table = new \Itassetinventory\Model\ItAssetInventoryReportTable($tableGateway);
                        return $table;
                },
                'ItAssetInventoryReportTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Itassetinventory\Model\ItAssetInventoryReport());
                        return new TableGateway('it_asset_inventory_reports', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
