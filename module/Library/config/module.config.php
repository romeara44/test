<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Library\Controller\Library' => 'Library\Controller\LibraryController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'library' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/library[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Library\Controller\Library',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Library' => __DIR__ . '/../view',
        ),
    ),
);