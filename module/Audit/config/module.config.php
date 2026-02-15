<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Audit\Controller\Audit' => 'Audit\Controller\AuditController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'audit' => array(
                'type'    => 'segment',
                'options' => array(
                    //'route'    => '/audit[/][/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter]',
                    'route'    => '/audit[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/location/:location][/adrId/:adrId][/ailiId/:ailiId][/abalId/:abalId][/locationRole/:locationRole][/companyId/:companyId][/rpId/:rpId]',
                    //'route'    => '/audit[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/location/:location][/adrId/:adrId][/locationRole/:locationRole][/companyId/:companyId][/rpId/:rpId]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Audit\Controller\Audit',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'audit' => __DIR__ . '/../view',
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);