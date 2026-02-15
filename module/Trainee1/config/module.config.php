<?php
// Filename: /module/Trainee/config/module.config.php
return array(
    'service_manager' => array(
        'factories' => array(
            'Trainee\Mapper\PostMapperInterface'   => 'Trainee\Factory\ZendDbSqlMapperFactory',
            'Trainee\Service\PostServiceInterface' => 'Trainee\Factory\PostServiceFactory',
            'Zend\Db\Adapter\Adapter'           => 'Zend\Db\Adapter\AdapterServiceFactory'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'controllers'  => array(
        'factories' => array(
            'Trainee\Controller\List'  => 'Trainee\Factory\ListControllerFactory',
            'Trainee\Controller\Write' => 'Trainee\Factory\WriteControllerFactory'
        )
    ),
    'router'          => array(
        'routes' => array(
            'trainee' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/trainee',
                    'defaults' => array(
                        'controller' => 'Trainee\Controller\List',
                        'action'     => 'index',
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'detail' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/:id',
                            'defaults' => array(
                                'action' => 'detail'
                            ),
                            'constraints' => array(
                                'id' => '\d+'
                            )
                        )
                    ),
                    'add' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route'    => '/add',
                            'defaults' => array(
                                'controller' => 'Trainee\Controller\Write',
                                'action'     => 'add'
                            )
                        )
                    ),
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/edit/:id',
                            'defaults' => array(
                                'controller' => 'Trainee\Controller\Write',
                                'action'     => 'edit'
                            ),
                            'constraints' => array(
                                'id' => '\d+'
                            )
                        )
                    ),
                    'delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/delete/:id',
                            'defaults' => array(
                                'controller' => 'Trainee\Controller\Delete',
                                'action'     => 'delete'
                            ),
                            'constraints' => array(
                                'id' => '\d+'
                            )
                        )
                    ),
                )
            )
        )
    )
);