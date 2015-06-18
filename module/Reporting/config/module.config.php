<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Reporting\Controller\Reporting' => 'Reporting\Controller\ReportingController'
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'reporting' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/reporting[/:action][/:type][/:id][/page/:page][/search/:search]',
                    'constraints' => array(
                        'type'   => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'search' => '.*',
                        'id'     => '[0-9]+',
                        'page'   => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Reporting\Controller\Reporting',
                        'action'     => 'index',
                    ),
                ),
            )

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'Reporting' => __DIR__ . '/../view'
        )
    ),

);