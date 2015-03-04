<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Sitesetting\Controller\Sitesetting' => 'Sitesetting\Controller\SitesettingController'
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'sitesetting' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/sitesetting[/:action]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Sitesetting\Controller\Sitesetting',
                        'action'     => 'index',
                    ),
                ),
            )

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'sitesetting' => __DIR__ . '/../view'
        )
    ),

);