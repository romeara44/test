<?php
namespace Traininglog;

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
                'Traininglog\Model\TraininglogRegulationTable' =>  function($sm) {
                        $tableGateway = $sm->get('TraininglogRegulationTableGateway');
                        $table = new \Traininglog\Model\TraininglogRegulationTable($tableGateway);
                        return $table;
                },
                'TraininglogRegulationTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\TraininglogRegulation());
                        return new TableGateway('training_logs_regulations', $dbAdapter, null, $resultSetPrototype);
                },
                'Traininglog\Model\TrainerTable' =>  function($sm) {
                    $tableGateway = $sm->get('TrainerTableGateway');
                    $table = new \Traininglog\Model\TrainerTable($tableGateway);
                    return $table;
                },
                'TrainerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\Trainer());
                    return new TableGateway('trainers', $dbAdapter, null, $resultSetPrototype);
                },'Traininglog\Model\RegulationTable' =>  function($sm) {
                        $tableGateway = $sm->get('RegulationTableGateway');
                        $table = new \Traininglog\Model\RegulationTable($tableGateway);
                        return $table;
                },
                'RegulationTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\Regulation());
                        return new TableGateway('regulations', $dbAdapter, null, $resultSetPrototype);
                },'Traininglog\Model\TraininglogtypeTable' =>  function($sm) {
                        $tableGateway = $sm->get('TraininglogtypeTableGateway');
                        $table = new \Traininglog\Model\TraininglogtypeTable($tableGateway);
                        return $table;
                },
                'TraininglogtypeTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\Traininglogtype());
                        return new TableGateway('training_log_types', $dbAdapter, null, $resultSetPrototype);
                },
                'Traininglog\Model\TraininglogTable' =>  function($sm) {
                        $tableGateway = $sm->get('TraininglogTableGateway');
                        $table = new \Traininglog\Model\TraininglogTable($tableGateway);
                        return $table;
                },
                'TraininglogTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\Traininglog());
                        return new TableGateway('training_logs', $dbAdapter, null, $resultSetPrototype);
                }
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                // the array key is the name of the invoke function that is called from view
                'textHelper' => function($text, $length, $options) {
                    return new \Traininglog\View\Helper\textHelper($text, $length, $options);
                },
            ),
        );
    }
}
