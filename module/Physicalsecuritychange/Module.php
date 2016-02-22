<?php
namespace Physicalsecuritychange;

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
                'Physicalsecuritychange\Model\PhysicalsecuritychangeTable' =>  function($sm) {
                        $tableGateway = $sm->get('PhysicalsecuritychangeTableGateway');
                        $table = new \Physicalsecuritychange\Model\PhysicalsecuritychangeTable($tableGateway);
                        return $table;
                },
                'PhysicalsecuritychangeTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Physicalsecuritychange\Model\Physicalsecuritychange());
                        return new TableGateway('physical_security_changes', $dbAdapter, null, $resultSetPrototype);
                },
                'Physicalsecuritychange\Model\PhysicalsecuritychangeitemTable' =>  function($sm) {
                        $tableGateway = $sm->get('PhysicalsecuritychangeitemTableGateway');
                        $table = new \Physicalsecuritychange\Model\PhysicalsecuritychangeitemTable($tableGateway);
                        return $table;
                },
                'PhysicalsecuritychangeitemTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Physicalsecuritychange\Model\Physicalsecuritychangeitem());
                        return new TableGateway('physical_security_changes_items', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
