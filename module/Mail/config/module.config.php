<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Mail\Controller\Mail' => 'Mail\Controller\MailController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'mail' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/mail[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/step/:step][/location/:location][/adrId/:adrId]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'step' => '[0-9]+',
                        'location' => '[0-9]+',
                        'adrId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Mail\Controller\Mail',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'mail' => __DIR__ . '/../view',
        ),
    ),

);