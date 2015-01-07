<?php
namespace Breachlog;

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
                'Breachlog\Model\BreachlogTable' =>  function($sm) {
                    $tableGateway = $sm->get('BreachlogTableGateway');
                    $table = new \Breachlog\Model\BreachlogTable($tableGateway);
                    return $table;
                },
                'BreachlogTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Breachlog\Model\Breachlog());
                    return new TableGateway('breach_logs', $dbAdapter, null, $resultSetPrototype);
                },

                'Breachlog\Model\BreachlogquestionTable' =>  function($sm) {
                    $tableGateway = $sm->get('BreachlogquestionTableGateway');
                    $table = new \Breachlog\Model\BreachlogquestionTable($tableGateway);
                    return $table;
                },
                'BreachlogquestionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('breach_logs_questions', $dbAdapter, null, $resultSetPrototype);
                },

                'Breachlog\Model\BreachloganswerTable' =>  function($sm) {
                    $tableGateway = $sm->get('BreachloganswerTableGateway');
                    $table = new \Breachlog\Model\BreachloganswerTable($tableGateway);
                    return $table;
                },
                'BreachloganswerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Breachlog\Model\Breachloganswer());
                    return new TableGateway('breach_logs_answers', $dbAdapter, null, $resultSetPrototype);
                },

                'Breachlog\Model\BreachremediationplanTable' =>  function($sm) {
                        $tableGateway = $sm->get('BreachremediationplanTableGateway');
                        $table = new \Breachlog\Model\BreachremediationplanTable($tableGateway);
                        return $table;
                    },
                'BreachremediationplanTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Breachlog\Model\Breachremediationplan());
                        return new TableGateway('breach_remediation_plans', $dbAdapter, null, $resultSetPrototype);
                    },

                'Breachlog\Model\BreachremediationplanactionTable' =>  function($sm) {
                    $tableGateway = $sm->get('BreachremediationplanactionTableGateway');
                    $table = new \Breachlog\Model\BreachremediationplanactionTable($tableGateway);
                    return $table;
                },
                'BreachremediationplanactionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Breachlog\Model\Breachremediationplanaction());
                    return new TableGateway('breach_remediation_plans_actions', $dbAdapter, null, $resultSetPrototype);
                },

            ),
        );
    }
}
