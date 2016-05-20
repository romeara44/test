<?php
namespace Disclosure;

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
                'Disclosure\Model\DisclosureRequestTable' =>  function($sm) {
                        $tableGateway = $sm->get('DisclosureRequestTableGateway');
                        $table = new \Disclosure\Model\DisclosureRequestTable($tableGateway);
                        return $table;
                },
                'DisclosureRequestTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Disclosure\Model\DisclosureRequest());
                        return new TableGateway('disclosure_requests', $dbAdapter, null, $resultSetPrototype);
                },
                'Disclosure\Model\DisclosureTrackingLogTable' =>  function($sm) {
                    $tableGateway = $sm->get('DisclosureTrackingLogTableGateway');
                    $table = new \Disclosure\Model\DisclosureTrackingLogTable($tableGateway);
                    return $table;
                },
                'DisclosureTrackingLogTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Disclosure\Model\DisclosureTrackingLog());
                    return new TableGateway('disclosure_tracking_logs', $dbAdapter, null, $resultSetPrototype);
                },
                'Disclosure\Model\VerbalLogTable' =>  function($sm) {
                    $tableGateway = $sm->get('VerbalLogTableGateway');
                    $table = new \Disclosure\Model\VerbalLogTable($tableGateway);
                    return $table;
                },
                'VerbalLogTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Disclosure\Model\VerbalLog());
                    return new TableGateway('verbal_logs', $dbAdapter, null, $resultSetPrototype);
                },
                'Disclosure\Model\DisclosureRecordTable' =>  function($sm) {
                        $tableGateway = $sm->get('DisclosureRecordTableGateway');
                        $table = new \Disclosure\Model\DisclosureRecordTable($tableGateway);
                        return $table;
                },
                'DisclosureRecordTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Disclosure\Model\DisclosureRecord());
                        return new TableGateway('disclosure_records', $dbAdapter, null, $resultSetPrototype);
                },
                'Disclosure\Model\AccountingRequestTable' =>  function($sm) {
                        $tableGateway = $sm->get('AccountingRequestTableGateway');
                        $table = new \Disclosure\Model\AccountingRequestTable($tableGateway);
                        return $table;
                },
                'AccountingRequestTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Disclosure\Model\AccountingRequest());
                        return new TableGateway('accounting_requests', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
