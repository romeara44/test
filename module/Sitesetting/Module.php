<?php
namespace Sitesetting;

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
                'Sitesetting\Model\SitesettingTable' =>  function($sm) {
                        $tableGateway = $sm->get('SitesettingTableGateway');
                        $table = new \Sitesetting\Model\SitesettingTable($tableGateway);
                        return $table;
                },
                'SitesettingTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Sitesetting\Model\Sitesetting());
                        return new TableGateway('site_settings', $dbAdapter, null, $resultSetPrototype);
                }
            ),
        );
    }
}
