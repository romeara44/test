<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Physicalsecuritychange\Controller\Physicalsecuritychange' => 'Physicalsecuritychange\Controller\PhysicalsecuritychangeController'
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'physicalsecuritychange' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/physicalsecuritychange[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
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
                        'controller' => 'Physicalsecuritychange\Controller\Physicalsecuritychange',
                        'action'     => 'index',
                    ),
                ),
            )

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'physicalsecuritychange' => __DIR__ . '/../view'
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);