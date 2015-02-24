<?php
namespace Securityreminder;

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
                'Securityreminder\Model\DistributiontypeTable' =>  function($sm) {
                        $tableGateway = $sm->get('DistributiontypeTableGateway');
                        $table = new \Securityreminder\Model\DistributiontypeTable($tableGateway);
                        return $table;
                },
                'DistributiontypeTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Securityreminder\Model\Distributiontype());
                        return new TableGateway('distribution_types', $dbAdapter, null, $resultSetPrototype);
                },
                'Securityreminder\Model\SecurityreminderTable' =>  function($sm) {
                        $tableGateway = $sm->get('SecurityreminderTableGateway');
                        $table = new \Securityreminder\Model\SecurityreminderTable($tableGateway);
                        return $table;
                },
                'SecurityreminderTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Securityreminder\Model\Securityreminder());
                        return new TableGateway('security_reminders', $dbAdapter, null, $resultSetPrototype);
                }
            ),
        );
    }
}
