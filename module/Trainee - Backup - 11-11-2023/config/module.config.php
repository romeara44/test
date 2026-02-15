<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Trainee\Controller\Index' => 'Trainee\Controller\IndexController'
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'trainee' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/trainee[/:action]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Trainee\Controller',
                        'controller' => 'Index',
                        'action'     => 'index',
                    ),
                ),
                
            )

        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'trainee' => __DIR__ . '/../view'
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);