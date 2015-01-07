<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Businessassociate\Controller\Businessassociate' => 'Businessassociate\Controller\BusinessassociateController',
            'Businessassociate\Controller\Businessassociateuser' => 'Businessassociate\Controller\BusinessassociateuserController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'businessassociate' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/businessassociate[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Businessassociate\Controller\Businessassociate',
                        'action'     => 'index',
                    ),
                ),
            ),
            'businessassociateuser' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/businessassociateuser[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Businessassociate\Controller\Businessassociateuser',
                        'action'     => 'index',
                    ),
                ),
            ),

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'businessassociate' => __DIR__ . '/../view',
            'businessassociateuser' => __DIR__ . '/../view',
        ),
    ),

);