<?php
namespace Admin;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Admin\Model\User;
use Admin\Model\UserTable;


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
                'Admin\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new \Admin\Model\UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Admin\Model\User());
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                },
                'Admin\Model\RoleTable' =>  function($sm) {
                    $tableGateway = $sm->get('RoleTableGateway');
                    $table = new \Admin\Model\RoleTable($tableGateway);
                    return $table;
                },
                'RoleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('roles', $dbAdapter, null, $resultSetPrototype);
                },
                'Admin\Model\StateTable' =>  function($sm) {
                    $tableGateway = $sm->get('StateTableGateway');
                    $table = new \Admin\Model\StateTable($tableGateway);
                    return $table;
                },
                'StateTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('states', $dbAdapter, null, $resultSetPrototype);
                },

            ),
        );
    }

}
