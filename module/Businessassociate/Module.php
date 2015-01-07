<?php
namespace Businessassociate;

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
                'Businessassociate\Model\BusinessassociateTable' =>  function($sm) {
                    $tableGateway = $sm->get('BusinessassociateTableGateway');
                    $table = new \Businessassociate\Model\BusinessassociateTable($tableGateway);
                    return $table;
                },
                'BusinessassociateTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Businessassociate\Model\Businessassociate());
                    return new TableGateway('business_associates', $dbAdapter, null, $resultSetPrototype);
                },

                'Businessassociate\Model\BusinessassociatequestionTable' =>  function($sm) {
                    $tableGateway = $sm->get('BusinessassociatequestionTableGateway');
                    $table = new \Businessassociate\Model\BusinessassociatequestionTable($tableGateway);
                    return $table;
                },
                'BusinessassociatequestionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('business_associates_questions', $dbAdapter, null, $resultSetPrototype);
                },

                'Businessassociate\Model\BusinessassociateanswerTable' =>  function($sm) {
                        $tableGateway = $sm->get('BusinessassociateanswerTableGateway');
                        $table = new \Businessassociate\Model\BusinessassociateanswerTable($tableGateway);
                        return $table;
                    },
                'BusinessassociateanswerTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Businessassociate\Model\Businessassociateanswer());
                        return new TableGateway('business_associates_answers', $dbAdapter, null, $resultSetPrototype);
                    },
            ),
        );
    }
}
