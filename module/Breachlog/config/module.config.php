<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Breachlog\Controller\Breachlog' => 'Breachlog\Controller\BreachlogController',
            'Breachlog\Controller\Breachremediationplan' => 'Breachlog\Controller\BreachremediationplanController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'breachlog' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/breachlog[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Breachlog\Controller\Breachlog',
                        'action'     => 'index',
                    ),
                ),
            ),
            'breachremediationplan' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/breachremediationplan[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/brpId/:brpId][/type/:type]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'brpId' => '[0-9]+',
                        'type' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Breachlog\Controller\Breachremediationplan',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'breachlog' => __DIR__ . '/../view',
            'breachremediationplan' => __DIR__ . '/../view',
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);