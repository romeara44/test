<?php

namespace Client;
// Filename: /module/Client/config/module.config.php

use Zend\ServiceManager\Factory\InvokableFactory;

return array(
    'service_manager' => array(
        'invokables' => array(
            //'Client\Service\InitializeClientTableServiceInterface' => 'Client\Service\InitializeClientTableService'
        )
    ),
    'controllers' => array(
        'factories' => array(
            //Controller\OrganizationController::class => Factory\OrganizationControllerFactory::class,
            //'Client\Controller\Organization' => 'Client\Factory\OrganizationControllerFactory'
        ),
        'invokables' => array(
            'Client\Controller\Client' => 'Client\Controller\ClientController',
            'Client\Controller\Company' => 'Client\Controller\CompanyController',
            'Client\Controller\Contact' => 'Client\Controller\ContactController',
            'Client\Controller\Organization' => 'Client\Controller\OrganizationController',
            'Client\Controller\Index' => 'Client\Controller\IndexController',
        ),
        
    ),


    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'client' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/client[/:action][/:id][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/activeFilter/:activeFilter][/company/:company][/lock/:lock]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'company' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'activeFilter' => '[0-9]+',
                        'lock' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Client\Controller\Client',
                        'action'     => 'index',
                    ),
                ),
            ),
            'company' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/company[/:action][/:id][#:#][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/adrId/:adrId]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        '#' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'adrId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Client\Controller\Company',
                        'action'     => 'index',
                    ),
                ),
            ),
            'organization' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/organization[/:action][/:id][#:#][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/activeFilter/:activeFilter][/company/:company][/adrId/:adrId]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        '#' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'adrId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Client\Controller\Organization',
                        'action'     => 'index',
                    ),
                ),
            ),
            'contact' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/contact[/:action][/:id][#:#][/page/:page][/order_by/:order_by][/:order][/roleFilter/:roleFilter][/activeFilter/:activeFilter][/company/:company]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        '#' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                        'roleFilter' => '[0-9]+',
                        'adrId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Client\Controller\Contact',
                        'action'     => 'index',
                    ),
                ),
            ),

        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'renewal-reminder' => array(
                    'options' => array(
                        'route'    => 'clients renewal',
                        'defaults' => array(
                            'controller' => 'Client\Controller\Index',
                            'action'     => 'renewal'
                        )
                    )
                )
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'client' => __DIR__ . '/../view',
            'company' => __DIR__ . '/../view',
            'organization' => __DIR__ . '/../view',
            'contact' => __DIR__ . '/../view'
        ),
        'strategies' => array (
            'ViewJsonStrategy'
        )
    ),

);