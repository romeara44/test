<?php
namespace Note;

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
                    'DataCrypt' => __DIR__ . '/../../vendor/mylib/library/DataCrypt',
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Note\Model\NotesFilesTable' =>  function($sm) {
                        $tableGateway = $sm->get('NotesFilesTableGateway');
                        $table = new \Note\Model\NotesFilesTable($tableGateway);
                        return $table;
                    },
                'NotesFilesTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('notes_files', $dbAdapter, null, $resultSetPrototype);
                    },
            ),
        );
    }
}
