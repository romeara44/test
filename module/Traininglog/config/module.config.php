<?php
// Module/Traininglog/config/module.config.php

return array(
    'controllers' => array(
        // 'invokables' => array(
        //     'Traininglog\Controller\Traininglog' => 'Traininglog\Controller\TraininglogController'
        // ),
        'factories' => array(
            'Traininglog\Controller\Traininglog' => 'Traininglog\Factory\TraininglogControllerFactory'
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'Traininglog\Service\TovutiServiceInterface' => 'Traininglog\Service\TovutiService'
        )
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'traininglog' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/traininglog[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'company' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'search' => '.*',
                    ),
                    'defaults' => array(
                        'controller' => 'Traininglog\Controller\Traininglog',
                        'action'     => 'index',
                    ),
                ),
            )

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'traininglog' => __DIR__ . '/../view'
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);