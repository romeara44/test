<?php
namespace Traininglog;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    // public function onBootstrap(MvcEvent $e) {
    //     $eventManager = $e->getApplication()->getEventManager();
    //     $moduleRouteListener = new ModuleRouteListener();
    //     $moduleRouteListener->attach($eventManager);
    // }

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
        //return include __DIR__ . '/config/service.config.php';
        return array(
            //'invokables' => array(
            //    'Traininglog\Service\ITraininglogService' => 'Traininglog\Service\TraininglogService'
        	//
            //),
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
                },
                'Traininglog\Model\RegulationCategoryTable' =>  function($sm) {
                        $tableGateway = $sm->get('RegulationCategoryTableGateway');
                        $table = new \Traininglog\Model\RegulationCategoryTable($tableGateway);
                        return $table;
                },
                'RegulationCategoryTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        return new TableGateway('regulation_categories', $dbAdapter, null, $resultSetPrototype);
                },
                'Traininglog\Model\RegulationTable' =>  function($sm) {
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
                },
                'Traininglog\Model\EmployeemasterlistTable' =>  function($sm) {
                        $tableGateway = $sm->get('EmployeemasterlistTableGateway');
                        $table = new \Traininglog\Model\EmployeemasterlistTable($tableGateway);
                        return $table;
                },
                'EmployeemasterlistTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\Employeemasterlist());
                        return new TableGateway('employee_master_list', $dbAdapter, null, $resultSetPrototype);
                },

                'Traininglog\Model\TrainingReportTable' =>  function($sm) {
                    $tableGateway = $sm->get('TrainingReportTableGateway');
                    $table = new \Traininglog\Model\TrainingReportTable($tableGateway);
                    return $table;
                },
                'TrainingReportTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\TrainingReport());
                        return new TableGateway('training_report', $dbAdapter, null, $resultSetPrototype);
                },
                // 'Traininglog\Repository\IPost1Repository' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
                //     $post1Repository = new \Traininglog\Repository\Post1Repository();
                //     $post1Repository->setDbAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'));
                //     return $post1Repository;
                // },
                // 'Traininglog\Service\ITraininglogService' => function(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
                //     $traininglogService = new \Traininglog\Service\TraininglogService();
                //     $traininglogService->setPost1Repository($serviceLocator->get('Traininglog\Repository\Post1Repository'));

                //     return $traininglogService;
                // }
                //'Traininglog\Model\TrainingUserTestTable' =>  function($sm) {
                //    $tableGateway = $sm->get('TrainingUserTestTableGateway');
                //    $table = new \Traininglog\Model\TrainingUserTestTable($tableGateway);
                //    return $table;
                //},
                //'TrainingUserTestTableGateway' => function ($sm) {
                //        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                //        $resultSetPrototype = new ResultSet();
                //        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\TrainingUserTest());
                //        return new TableGateway('training_user_test', $dbAdapter, null, $resultSetPrototype);
                //},


                'Traininglog\Model\TraineeUserTable' =>  function($sm) {
                    $tableGateway = $sm->get('TraineeUserTableGateway');
                    $table = new \Traininglog\Model\TraineeUserTable($tableGateway);
                    return $table;
                },
                'TraineeUserTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\TraineeUser());
                        return new TableGateway('trainee_user', $dbAdapter, null, $resultSetPrototype);
                },

                'Traininglog\Model\TovutiUserTable' =>  function($sm) {
                    $tableGateway = $sm->get('TovutiUserTableGateway');
                    $table = new \Traininglog\Model\TovutiUserTable($tableGateway);
                    return $table;
                },
                'TovutiUserTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\TovutiUser());
                        return new TableGateway('tovuti_user', $dbAdapter, null, $resultSetPrototype);
                },


                'Traininglog\Model\TovutiUserGroupTable' =>  function($sm) {
                    $tableGateway = $sm->get('TovutiUserGroupTableGateway');
                    $table = new \Traininglog\Model\TovutiUserGroupTable($tableGateway);
                    return $table;
                },
                'TovutiUserGroupTableGateway' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype(new \Traininglog\Model\TovutiUserGroup());
                        return new TableGateway('tovuti_user_group', $dbAdapter, null, $resultSetPrototype);
                },

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
