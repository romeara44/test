<?php
namespace Search;

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
                'Search\Model\SearchTable' =>  function($sm) {
                    $tableGateway = $sm->get('SearchTableGateway');
                    $table = new \Search\Model\SearchTable($tableGateway);
                    return $table;
                },
                'SearchTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('notes_files', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
