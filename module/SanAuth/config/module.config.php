<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'SanAuth\Controller\Auth' => 'SanAuth\Controller\AuthController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'auth' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/auth[/:action][/:id][/:hash]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'hash'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'SanAuth\Controller\Auth',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'SanAuth' => __DIR__ . '/../view',
        ),
    ),
);
