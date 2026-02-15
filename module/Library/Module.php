<?php
namespace Library;

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
                'Library\Model\LibraryTable' =>  function($sm) {
                    $tableGateway = $sm->get('LibraryTableGateway');
                    $table = new \Library\Model\LibraryTable($tableGateway);
                    //$table = new \Library\Model\LibraryTable();
                    return $table;
                },
                'LibraryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
