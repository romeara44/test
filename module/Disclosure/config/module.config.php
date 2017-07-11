<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Disclosure\Controller\DisclosureRequest' => 'Disclosure\Controller\DisclosureRequestController',
            'Disclosure\Controller\DisclosureTrackingLog' => 'Disclosure\Controller\DisclosureTrackingLogController',
            'Disclosure\Controller\VerbalLog' => 'Disclosure\Controller\VerbalLogController',
            'Disclosure\Controller\DisclosureRecord' => 'Disclosure\Controller\DisclosureRecordController',
            'Disclosure\Controller\AccountingRequest' => 'Disclosure\Controller\AccountingRequestController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'disclosurerequest' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/disclosurerequest[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
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
                        'controller' => 'Disclosure\Controller\DisclosureRequest',
                        'action'     => 'index',
                    ),
                ),
            ),
            'disclosuretrackinglog' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/disclosuretrackinglog[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
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
                        'controller' => 'Disclosure\Controller\DisclosureTrackingLog',
                        'action'     => 'index',
                    ),
                ),
            ),
            'verballog' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/verballog[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
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
                        'controller' => 'Disclosure\Controller\VerbalLog',
                        'action'     => 'index',
                    ),
                ),
            ),
            'disclosurerecord' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/disclosurerecord[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
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
                        'controller' => 'Disclosure\Controller\DisclosureRecord',
                        'action'     => 'index',
                    ),
                ),
            ),
            'accountingrequest' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/accountingrequest[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/search/:search]',
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
                        'controller' => 'Disclosure\Controller\AccountingRequest',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'disclosurerequest' => __DIR__ . '/../view',
            'disclosuretrackinglog' => __DIR__ . '/../view',
            'verballog' => __DIR__ . '/../view',
            'disclosurerecord' => __DIR__ . '/../view',
            'accountingrequest' => __DIR__ . '/../view',
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);